<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Object;

class ProjectManager extends Object
{
    const TABLE_NAME = 'projects';

    const
        STATUS_AWAITING = 0,
        STATUS_ACCEPTED = 1,
        STATUS_DECLINED = 2,
        STATUS_NOT_COMPLETED = 0,
        STATUS_COMPLETED = 1;

    /** @var Context */
    private $database;

    /**
     * @param Context $context
     */
    function __construct(Context $context)
    {
        $this->database = $context;
    }

    /**
     * @param $name
     * @param $desc
     * @param $user_id
     * @return bool|int|\Nette\Database\Table\IRow
     */
    function add($name, $desc, $user_id)
    {
        return $this->database->table(self::TABLE_NAME)->insert(array(
            'name' => $name,
            'description' => $desc,
            'users_id' => $user_id
        ));
    }

    /**
     * @param $user_id
     * @return bool|mixed|\Nette\Database\Table\IRow
     */
    function accepted($user_id)
    {
        return $this->database->table(self::TABLE_NAME)->where(array(
            'users_id' => $user_id,
            'accepted' => self::STATUS_ACCEPTED
        ))->fetch();
    }

    /**
     * @param $user_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    function byUser($user_id)
    {
        return $this->database->table(self::TABLE_NAME)->where('users_id', $user_id)->fetchAll();
    }

    /**
     * @param $project_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    function solutions($project_id)
    {
        return $this->database->table('solutions')->where('projects_id', $project_id)->order('uploaded DESC')->fetchAll();
    }
}