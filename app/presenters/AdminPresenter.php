<?php

namespace App\Presenters;


use App\Model\UserManager;
use Nette\Application\BadRequestException;
use Nette\Database\Context;

class AdminPresenter extends BasePresenter
{
    /** @var Context @inject */
    public $database;

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isInRole(UserManager::ROLE_ADMIN)) throw new BadRequestException;
    }
}