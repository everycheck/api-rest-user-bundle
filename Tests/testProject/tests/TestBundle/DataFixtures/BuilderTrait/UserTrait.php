<?php 
namespace EveryCheck\UserApiRestBundle\Tests\testProject\tests\TestBundle\DataFixtures\BuilderTrait;

use EveryCheck\UserApiRestBundle\Entity\User;

trait UserTrait
{
    public function createUser(string $username = "someone") : User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username.'@everycheck.fr');
        $user->setPlainPassword($username);

        if(  empty($user->getPlainPassword()) === false  )
        {
            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encoded);            
        }
        else
        {
            $user->setPassword('');
        }

        $this->manager->persist($user);

        return $user;
    }

    public function createUsersWithToken(array $names) : array
    {
        $users = [];
        foreach ($names as $name)
        {
            $users[$name] = $this->createUser($name);
            $this->createToken($users[$name]);
        }
        return $users;
    }

}