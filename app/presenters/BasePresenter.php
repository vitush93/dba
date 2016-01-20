<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context @inject */
    public $database;

    protected function startup()
    {
        parent::startup();

        $this->database->query("SET search_path TO dba"); // TODO temporary schema search path fix

        if ($this->presenter->name != 'Sign' && !$this->user->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        if ($this->user->getLogoutReason() == Nette\Security\IUserStorage::INACTIVITY) {
            $this->flashMessage('You have been logged out (20 minutes).', 'info');
        }
    }
}
