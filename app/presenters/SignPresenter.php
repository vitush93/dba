<?php

namespace App\Presenters;

use Libs\BootstrapForm;
use Nette;
use App\Forms\SignFormFactory;
use Nette\Application\UI\Form;


class SignPresenter extends BasePresenter
{
    /** @var SignFormFactory @inject */
    public $factory;


    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = $this->factory->create();
        $form->onSuccess[] = function (Form $form) {
            $form->getPresenter()->flashMessage('You have been logged in!', 'success');
            $form->getPresenter()->redirect('Homepage:');
        };

        return BootstrapForm::makeBootstrap($form);
    }


    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.', 'info');
        $this->redirect('in');
    }

}
