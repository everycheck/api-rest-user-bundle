<?php 
namespace Tests\UserBundle\DataFixtures\BuilderTrait;

use UserBundle\Entity\User;
use UserBundle\Entity\UserRole;

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