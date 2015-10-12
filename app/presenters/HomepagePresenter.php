<?php

namespace App\Presenters;

use App\Model;
use Libs\BootstrapForm;
use Nette\Application\UI\Form;


class HomepagePresenter extends BasePresenter
{
    /** @var Model\ProjectManager @inject */
    public $projectManager;

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

        $this->template->project = $this->projectManager->accepted($this->user->id);
        $this->template->projects = $projects;
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
