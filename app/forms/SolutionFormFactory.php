<?php

namespace App\Forms;


use App\Model\FileUploadHandler;
use App\Model\ProjectManager;
use Libs\BootstrapForm;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Object;
use Nette\Security\User;

class SolutionFormFactory extends Object
{
    /** @var Context */
    private $database;

    private $projectManager;

    private $user;

    /**
     * @param User $user
     * @param ProjectManager $projectManager
     * @param Context $context
     */
    function __construct(User $user, ProjectManager $projectManager, Context $context)
    {
        $this->database = $context;
        $this->user = $user;
        $this->projectManager = $projectManager;
    }

    /**
     * @param Form $form
     * @param $values
     */
    function solutionFormSucceeded(Form $form, $values)
    {
        if ($values->file->isOk()) {
            $values->projects_id = $this->projectManager->accepted($this->user->id)->id;
            $values->file = FileUploadHandler::upload($values->file);
            $this->database->table('solutions')->insert($values);

            $form->getPresenter()->flashMessage('Solution has been successfully uploaded.', 'success');
        } else {
            $form->getPresenter()->flashMessage('Something went wrong while uploading a file :( .', 'danger');
        }
    }

    /**
     * @return Form
     */
    function create()
    {
        $form = new Form();

        $form->addUpload('file', 'Select file')->setRequired();
        $form->addTextArea('note', 'Your note');
        $form->addSubmit('process', 'Upload');

        $form->onSuccess[] = $this->solutionFormSucceeded;

        return BootstrapForm::makeBootstrap($form);
    }
}