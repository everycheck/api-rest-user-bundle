<?php 
namespace EveryCheck\UserApiRestBundle\Tests\testProject\tests\TestBundle\DataFixtures\BuilderTrait;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\UserRole;

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