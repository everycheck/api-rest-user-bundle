<?php

namespace EveryCheck\UserApiRestBundle\Tests\testProject\src\TestBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use EveryCheck\SimpleAclBundle\Event\RequestPopulationEvent;
use EveryCheck\UserApiRestBundle\Entity\User;

class UserPopulationListener
{

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    public function onPostedResquest(RequestPopulationEvent $event)
    {
        if($event->getEntity() instanceof User)
        {
            $event->addUser($this->tokenStorage->getToken()->getUser());
            $event->addUser($event->getEntity());
        }
    }

}