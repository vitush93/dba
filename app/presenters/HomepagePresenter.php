<?php

namespace App\Presenters;

use App\Model;
use Libs\BootstrapForm;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Context;


class HomepagePresenter extends BasePresenter
{
    /** @var Model\ProjectManager @inject */
    public $projectManager;

    /** @var Context @inject */
    public $database;

    function actionDefault()
    {
        if ($this->user->isInRole(Model\UserManager::ROLE_ADMIN)) {
            $this->redirect('admin');
        }
    }

    function actionAdmin()
    {
        if (!$this->user->isInRole(Model\UserManager::ROLE_ADMIN)) throw new BadRequestException;
    }

    function renderAdmin()
    {

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
            $this->template->desc = $parsedown->parse($project->description);

            $this->template->solutions = $this->projectManager->solutions($project->id);
            $this->template->comments = $this->database->table('comments')->where(array(
                'comments_id' => null,
                'users_id' => $this->user->id
            ))->order('posted DESC');
        }
    }

    function handleComment($text)
    {
        $inserted = $this->database->table('comments')->insert(array(
            'users_id' => $this->user->id,
            'text' => $text,
            'projects_id' => $this->projectManager->accepted($this->user->id)->id
        ));

        if ($inserted) {
            $parsedown = new \Parsedown();
            $user = $inserted->ref('users');
            $name = $user->name ? $user->name : $user->username;

            $this->sendJson(array(
                'text' => $parsedown->parse($inserted->text),
                'posted' => $inserted->posted->format('j.n.Y H:i'),
                'author' => $name,
                'id' => $inserted->id
            ));
        }
    }

    function handleReply($text, $comment)
    {
        $checkComment = $this->database->table('comments')->where(array(
                'users_id' => $this->user->id,
                'id' => $comment
            ))->count() > 0;

        if ($checkComment) {
            $inserted = $this->database->table('comments')->insert(array(
                'users_id' => $this->user->id,
                'text' => $text,
                'projects_id' => $this->projectManager->accepted($this->user->id)->id,
                'comments_id' => $comment
            ));

            if ($inserted) {
                $user = $inserted->ref('users');
                $name = $user->name ? $user->name : $user->username;

                $this->sendJson(array(
                    'text' => $inserted->text,
                    'posted' => $inserted->posted->format('j.n.Y H:i'),
                    'author' => $name
                ));
            }
        }
    }

    /**
     * @param Form $form
     * @param $values
     */
    function solutionFormSucceeded(Form $form, $values)
    {
        if ($values->file->isOk()) {
            $values->projects_id = $this->projectManager->accepted($this->user->id)->id;
            $values->file = Model\FileUploadHandler::upload($values->file);
            $this->database->table('solutions')->insert($values);

            $this->flashMessage('Solution has been successfully uploaded.', 'success');
        } else {
            $this->flashMessage('Something went wrong while uploading a file :( .', 'danger');
        }
    }

    /**
     * @param Form $form
     * @param $values
     */
    function projectFormSucceeded(Form $form, $values)
    {
        $this->projectManager->add($values->name, $values->description, $this->user->id);
        $this->flashMessage("Project $values->name has been successfully created!", 'success');
    }

    /**
     * @return Form
     */
    function createComponentSolutionForm()
    {
        $form = new Form();

        $form->addUpload('file', 'Select file')->setRequired();
        $form->addTextArea('note', 'Your note');
        $form->addSubmit('process', 'Upload');

        $form->onSuccess[] = $this->solutionFormSucceeded;

        return BootstrapForm::makeBootstrap($form);
    }

    /**
     * @return Form
     */
    function createComponentProjectForm()
    {
        $form = new Form();

        $form->addText('name', 'Project name')->setRequired();
        $form->addTextArea('description', 'Project description')
            ->setAttribute('rows', 10)
            ->setOption('description', 'You can use Markdown syntax')
            ->setRequired();
        $form->addSubmit('process', 'Create');

        $form->onSuccess[] = $this->projectFormSucceeded;

        return BootstrapForm::makeBootstrap($form);
    }
}
