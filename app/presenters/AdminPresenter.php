<?php

namespace App\Presenters;


use App\Forms\SolutionFormFactory;
use App\Model\CommentingException;
use App\Model\CommentingService;
use App\Model\ProjectManager;
use App\Model\UserManager;
use Libs\BootstrapForm;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Security\Passwords;
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
            ->order('bump DESC');
    }

    function renderStudents()
    {
        $this->template->students = $this->database->table('users')->where('role', UserManager::ROLE_USER);
    }

    function handleAccept($id)
    {
        $this->database->table('projects')->where('id', $id)->update(array(
            'accepted' => ProjectManager::STATUS_ACCEPTED
        ));

        $this->flashMessage('Project has been marked as accepted.', 'info');
        $this->redirect('this');
    }

    function handleDecline($id)
    {
        $this->database->table('projects')->where('id', $id)->update(array(
            'accepted' => ProjectManager::STATUS_DECLINED
        ));

        $this->flashMessage('Project has been marked as declined.', 'info');
        $this->redirect('this');
    }

    function handleSeenSolution($id)
    {
        $this->database->table('solutions')->where('id', $id)->update(array(
            'seen' => true
        ));

        $this->flashMessage('Solution has been resolved.', 'info');
        $this->redirect('this');
    }

    function handleSeenComment($id)
    {
        $this->database->table('comments')->where('id', $id)->update(array(
            'seen' => true
        ));

        $this->flashMessage('Comment has been resolved.', 'info');
        $this->redirect('this');
    }

    function handleSeenAll()
    {
        $project_id = $this->getParameter('id');

        $this->database->table('comments')->where('projects_id', $project_id)->update(array(
            'seen' => true
        ));

        $this->database->table('solutions')->where('projects_id', $project_id)->update(array(
            'seen' => true
        ));

        $this->flashMessage('All comments and solutions in project has been resolved.', 'info');
        $this->redirect('default');
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
        $desc = htmlentities($this->template->project->description, ENT_QUOTES, 'UTF-8');
        $this->template->desc = $parsedown->parse($desc);
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

        $this->template->parsedown = new \Parsedown();
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

    function actionPwd($id)
    {
        $student = $this->database->table('users')->get($id);
        if (!$student) throw new BadRequestException;

        $this->template->student = $student;
    }

    function pwdFormSucceeded(Form $form, $values)
    {
        $this->database->table('users')->where('id', $this->getParameter('id'))->update(array(
            UserManager::COLUMN_PASSWORD_HASH => Passwords::hash($values->password)
        ));

        $this->flashMessage('Password has been changed for the student.', 'info');
        $this->redirect('students');
    }

    function createComponentPwdForm()
    {
        $form = new Form();

        $form->addPassword('password', 'Password')
            ->addRule(Form::MIN_LENGTH, 'Password must be at least 6 characters long.', 6)
            ->setRequired()
            ->setOption('description', 'Password must be at least 6 characters long.');
        $form->addPassword('password2', 'Check password')
            ->setRequired()
            ->addRule(Form::EQUAL, 'Password does not match.', $form['password'])
            ->setOmitted()
            ->setOption('description', 'Enter you password again.');
        $form->addSubmit('process', 'Change password');

        $form->onSuccess[] = $this->pwdFormSucceeded;

        return BootstrapForm::makeBootstrap($form);
    }

}