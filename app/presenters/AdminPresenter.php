<?php

namespace App\Presenters;


use App\Forms\SolutionFormFactory;
use App\Model\CommentingException;
use App\Model\CommentingService;
use App\Model\ProjectManager;
use App\Model\UserManager;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Tracy\Debugger;

class AdminPresenter extends BasePresenter
{
    /** @var Context @inject */
    public $database;

    /** @var ProjectManager @inject */
    public $projectManager;

    /** @var SolutionFormFactory @inject */
    public $solutionFormFactory;

    /** @var CommentingService @inject */
    public $commentingService;

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isInRole(UserManager::ROLE_ADMIN)) throw new BadRequestException;
    }

    function actionProject($id)
    {
        $project = $this->database->table('projects')->get($id);
        if (!$project) throw new BadRequestException;

        $this->template->project = $project;
        $this->template->solutions = $this->projectManager->solutions($id);
        $this->template->comments = $project->related('comments')
            ->where('comments_id', null)
            ->order('posted DESC');
    }

    function handleComment($text)
    {
        try {
            $response = $this->commentingService->comment(
                $this->user->id,
                $this->getParameter('id'),
                $text);

            $this->sendResponse($response);
        } catch (CommentingException $e) {
            Debugger::log($e);
        }
    }

    function handleReply($text, $comment)
    {
        try {
            $response = $this->commentingService->reply(
                $this->user->id,
                $this->getParameter('id'),
                $text,
                $comment);

            $this->sendResponse($response);
        } catch (CommentingException $e) {
            Debugger::log($e);
        }
    }

    function renderProject()
    {
        $this->template->setFile(__DIR__ . '/templates/Homepage/default.latte');
        $this->template->admin = true;

        $parsedown = new \Parsedown();
        $this->template->desc = $parsedown->parse($this->template->project->description);
        $this->template->parsedown = $parsedown;
    }

    function renderDefault()
    {
        $this->template->projects = $this->database->table('projects')
            ->where('accepted', 0)
            ->order('created DESC')
            ->fetchAll();

        $this->template->comments = $this->database->table('comments')
            ->where('seen', false)
            ->where('users_id != ?', $this->user->id)
            ->order('posted DESC')
            ->fetchAll();

        $this->template->solutions = $this->database->table('solutions')
            ->where('seen', false)
            ->order('uploaded DESC')
            ->fetchAll();
    }

    function renderProjects()
    {
        $this->template->projects = $this->database->table('projects');
    }

    /**
     * @return Form
     */
    function createComponentSolutionForm()
    {
        $form = $this->solutionFormFactory->create();

        return $form;
    }

}