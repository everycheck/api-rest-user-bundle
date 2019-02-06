<?php 
namespace EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\DataFixtures\BuilderTrait;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\AuthToken;

trait TokenTrait
{
    public function createToken(User $user) : AuthToken
    {
        $authToken = new AuthToken();
        $authToken->setValue('token_'. $user->getUsername() );
        $authToken->setUser($user);
        $authToken->setCreatedAt(new \DateTime('now'));
        $this->manager->persist($authToken);

        return $authToken;
    }

}