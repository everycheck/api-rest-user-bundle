<?php
namespace EveryCheck\UserApiRestBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use EveryCheck\UserApiRestBundle\Entity\User;

class UserEvent extends Event
{
	const DELETE_NAME = 'user_api_rest.user_event.delete';
    const POST_NAME   = 'user_api_rest.user_event.post';
    const PATCH_NAME  = 'user_api_rest.user_event.patch';

    private $user = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
  
    public function getUser()
    {
        return $this->user;
    }
}