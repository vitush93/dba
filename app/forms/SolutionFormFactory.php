<?php

namespace App\Forms;


use App\Model\FileUploadHandler;
use Libs\BootstrapForm;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Object;

class SolutionFormFactory extends Object
{
    /** @var Context */
    private $database;

    /**
     * @param Context $context
     */
    function __construct(Context $context)
    {
        $this->database= $context;
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