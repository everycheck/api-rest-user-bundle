<?php 
namespace EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\DataFixtures\BuilderTrait;

use EveryCheck\UserApiRestBundle\UserBundle\Entity\User;
use EveryCheck\UserApiRestBundle\UserBundle\Entity\UserRole;

trait RoleTrait
{
    public function addRole(User $user,array $names)
    {
        foreach ($names as $name)
        {
            $role = new UserRole();
            $role->setUser($user);
            $role->setName($name);
            $this->manager->persist($role);
        }
    }

}