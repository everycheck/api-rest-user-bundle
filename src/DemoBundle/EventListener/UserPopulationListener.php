<?php

namespace DemoBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use EveryCheck\Acl\Event\RequestPopulationEvent;

class UserPopulationListener
{

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    public function onPostedResquest(RequestPopulationEvent $event)
    {
        if($event->getEntity() instanceof UserBundle\Entity\User)
        {
            $event->addUser($this->tokenStorage->getToken()->getUser());
            $event->addUser($event->getEntity());
        }
    }

}