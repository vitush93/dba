<?php

namespace App\Presenters;

use App\Forms\SolutionFormFactory;
use App\Model;
use Libs\BootstrapForm;
use Libs\Utils;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Tracy\Debugger;


class HomepagePresenter extends BasePresenter
{
    /** @var Model\ProjectManager @inject */
    public $projectManager;

    /** @var Context @inject */
    public $database;

    /** @var SolutionFormFactory @inject */
    public $solutionFormFactory;

    /** @var Model\CommentingService @inject */
    public $commentingService;

    function actionDefault()
    {
        if ($this->user->isInRole(Model\UserManager::ROLE_ADMIN) && !$this->isAjax()) {
            $this->redirect('Admin:default');
        }
    }

    function renderDefault()
    {
        $projects = $this->projectManager->byUser($this->user->id);
        $awaiting = 0;
        $accepted = 0;
        $declined = 0;
        foreach ($projects as $p) {
            if ($p->accepted == Model\ProjectManager::STATUS_AWAITING) $awaiting++;
            if ($p->accepted == Model\ProjectManager::STATUS_ACCEPTED) $accepted++;
            if ($p->accepted == Model\ProjectManager::STATUS_DECLINED) $declined++;
        }

        $this->template->accepted = $accepted;
        $this->template->declined = $declined;
        $this->template->awaiting = $awaiting;

        $project = $this->projectManager->accepted($this->user->id);;
        $this->template->project = $project;
        $this->template->projects = $projects;

        if ($accepted > 0) {
            $parsedown = new \Parsedown();
            $desc = htmlentities($project->description, ENT_QUOTES, 'UTF-8');
            $this->template->desc = $parsedown->parse($desc);

            $this->template->solutions = $this->projectManager->solutions($project->id);
            $this->template->comments = $this->database->table('comments')->where(array(
                'comments_id' => null,
                'projects_id' => $project->id
            ))->order('bump DESC');
            $this->template->parsedown = $parsedown;
        }
    }

    function handleComment($text)
    {
        try {
            $project_id = $this->projectManager->accepted($this->user->id)->id;
            $response = $this->commentingService->comment($this->user->id, $project_id, $text);

            $this->sendResponse($response);
        } catch (Model\CommentingException $e) {
            Debugger::log($e);
        }
    }

    function handleReply($text, $comment)
    {
        try {
            $project_id = $this->projectManager->accepted($this->user->id)->id;
            $response = $this->commentingService->reply($this->user->id, $project_id, $text, $comment);

            $this->sendResponse($response);
        } catch (Model\CommentingException $e) {
            Debugger::log($e);
        }
    }

    /**
     * @param Form $form
     * @param $values
     */
    function projectFormSucceeded(Form $form, $values)
    {
        $new_project = $this->projectManager->add($values->name, $values->description, $this->user->id);

        foreach (Utils::safeExplodeByComma($values->tags) as $tag) {
            try {
                $project_tag = $this->database->table('tags')->insert(array(
                    'tag' => $tag
                ));

                $tag_id = $project_tag->id;
            } catch (UniqueConstraintViolationException $e) {
                $tag_id = $this->database->table('tags')->where('tag', $tag)->fetch()->id;
            }

            $this->database->table('projects_tags')->insert(array(
                'projects_id' => $new_project->id,
                'tags_id' => $tag_id
            ));
        }

        $this->flashMessage("Project $values->name has been successfully created!", 'success');
    }

    /**
     * @return Form
     */
    function createComponentSolutionForm()
    {
        $form = $this->solutionFormFactory->create();

        return $form;
    }

    /**
     * @return Form
     */
    function createComponentProjectForm()
    {
        $form = new Form();

        $form->addText('name', 'Project name')
            ->setRequired();
        $form->addText('tags', 'Tags')
            ->setOption('description', 'Comma-delimited project tags e.g. PostgreSQL, MS SQL etc.');
        $form->addTextArea('description', 'Project description')
            ->setAttribute('rows', 10)
            ->setOption('description', 'You can use Markdown syntax.')
            ->setRequired();
        $form->addSubmit('process', 'Create');

        $form->onSuccess[] = $this->projectFormSucceeded;

        return BootstrapForm::makeBootstrap($form);
    }
}
