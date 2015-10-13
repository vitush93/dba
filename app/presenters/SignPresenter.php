<?php

namespace App\Presenters;

use App\Model\DuplicateNameException;
use App\Model\UserManager;
use Libs\BootstrapForm;
use Nette;
use App\Forms\SignFormFactory;
use Nette\Application\UI\Form;


class SignPresenter extends BasePresenter
{
    /** @var SignFormFactory @inject */
    public $factory;

    /** @var UserManager @inject */
    public $userManager;

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

    /**
     * @return Form
     */
    protected function createComponentRegisterForm()
    {
        $form = new Form();

        $form->addText('username', 'Username')
            ->addRule(Form::MIN_LENGTH, 'Username must be at least 4 characters long.', 4)
            ->addRule(Form::PATTERN, 'Only alphanumeric characters are allowed in username.', "^[a-zA-Z0-9]*$")
            ->setRequired();
        $form->addText('email', 'E-mail')->setRequired();
        $form->addPassword('password', 'Password')
            ->addRule(Form::MIN_LENGTH, 'Password must be at least 6 characters long.', 6)
            ->setRequired()
            ->setOption('description', 'Password must be at least 6 characters long.');
        $form->addPassword('password2', 'Check password')
            ->setRequired()
            ->addRule(Form::EQUAL, 'Password does not match.', $form['password'])
            ->setOmitted()
            ->setOption('description', 'Enter you password again.');
        $form->addText('name', 'Your name');
        $form->addSubmit('process', 'Create');

        $form->onSuccess[] = $this->registerFormSucceeded;

        return BootstrapForm::makeBootstrap($form);
    }

    /**
     * @param Form $form
     * @param $values
     */
    function registerFormSucceeded(Form $form, $values)
    {
        try {
            $this->userManager->add(
                $values->username,
                $values->password,
                $values->email,
                UserManager::ROLE_USER,
                $values->name);

            $this->flashMessage('Your account has been successfully created. You can now login.', 'success');
            $this->redirect('in');
        } catch (DuplicateNameException $e) {
            $this->flashMessage('User with this username or e-mail address already exists.', 'warning');
        }
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.', 'info');
        $this->redirect('in');
    }

}
