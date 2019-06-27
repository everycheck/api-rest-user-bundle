<?php
namespace EveryCheck\UserApiRestBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class PasswordEvent extends Event
{
    const PASSWORD_RESET_NAME         = 'user_api_rest.password_event.patch';
    const PASSWORD_RESET_REQUEST_NAME = 'user_api_rest.password_event.request';

    private $token = null;

    public function __construct($token)
    {
        $this->token = $token;
    }
  
    public function getToken()
    {
        return $this->token;
    }
}