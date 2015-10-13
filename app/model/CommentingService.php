<?php

namespace App\Model;


use Nette\Application\Responses\JsonResponse;
use Nette\Database\Context;
use Nette\Neon\Exception;
use Nette\Object;
use Nette\Security\User;
use Nette\Utils\DateTime;

class CommentingService extends Object
{
    /** @var ProjectManager */
    private $projectManager;

    /** @var Context */
    private $database;

    /**
     * @param ProjectManager $projectManager
     * @param Context $context
     */
    function __construct(ProjectManager $projectManager, Context $context)
    {
        $this->projectManager = $projectManager;
        $this->database = $context;
    }

    /**
     * Creates a new comment thread.
     *
     * @param $user_id
     * @param $project_id
     * @param string $text comment text
     * @return JsonResponse
     * @throws CommentingException
     */
    function comment($user_id, $project_id, $text)
    {
        $inserted = $this->database->table('comments')->insert(array(
            'users_id' => $user_id,
            'text' => $text,
            'projects_id' => $project_id,
            'bump' => new DateTime()
        ));

        if ($inserted) {
            $parsedown = new \Parsedown();
            $user = $inserted->ref('users');
            $name = $user->name ? $user->name : $user->username;

            return new JsonResponse(array(
                'text' => $parsedown->parse($inserted->text),
                'posted' => $inserted->posted->format('j.n.Y H:i'),
                'author' => $name,
                'id' => $inserted->id
            ));
        } else {
            throw new CommentingException;
        }
    }

    /**
     * @param $user_id
     * @param $project_id
     * @param string $text comment text
     * @param int $comment parent comment id
     * @return JsonResponse
     * @throws CommentingException
     */
    function reply($user_id, $project_id, $text, $comment)
    {
        $checkComment = $this->database->table('comments')->where(array(
                'projects_id' => $project_id,
                'id' => $comment
            ))->count() > 0;

        if (!$checkComment) throw new CommentingException;

        $inserted = $this->database->table('comments')->insert(array(
            'users_id' => $user_id,
            'text' => $text,
            'projects_id' => $project_id,
            'comments_id' => $comment
        ));

        $this->database->table('comments')->where(array(
            'projects_id' => $project_id,
            'id' => $comment
        ))->update(array(
            'bump' => new DateTime()
        ));

        if ($inserted) {
            $user = $inserted->ref('users');
            $name = $user->name ? $user->name : $user->username;

            return new JsonResponse(array(
                'text' => $inserted->text,
                'posted' => $inserted->posted->format('j.n.Y H:i'),
                'author' => $name
            ));
        } else {
            throw new CommentingException;
        }
    }
}

class CommentingException extends Exception
{
}