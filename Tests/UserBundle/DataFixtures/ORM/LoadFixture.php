<?php 
namespace EveryCheck\ApiRestUserBundle\Tests\UserBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use EveryCheck\ApiRestUserBundle\Tests\UserBundle\DataFixtures\BuilderTrait\UserTrait;
use EveryCheck\ApiRestUserBundle\Tests\UserBundle\DataFixtures\BuilderTrait\RoleTrait;
use EveryCheck\ApiRestUserBundle\Tests\UserBundle\DataFixtures\BuilderTrait\TokenTrait;
use EveryCheck\ApiRestUserBundle\Tests\UserBundle\DataFixtures\BuilderTrait\UuidTrait;

use EveryCheck\ApiRestUserBundle\Entity\User;
use EveryCheck\ApiRestUserBundle\Entity\UserRole;
use EveryCheck\ApiRestUserBundle\Entity\AuthToken;

class LoadFixture implements FixtureInterface, ContainerAwareInterface
{
    use UserTrait;
    use TokenTrait;
    use UuidTrait;
    use RoleTrait;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $users = $this->createUsersWithToken(['someone']);
        $this->addRole($users['someone'],['somerole']);

        $this->manager->flush();
        $this->rewriteUuid([User::class,AuthToken::class,UserRole::class]);      
    }
}