<?php 
namespace EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\DataFixtures\BuilderTrait\UserTrait;
use EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\DataFixtures\BuilderTrait\RoleTrait;
use EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\DataFixtures\BuilderTrait\TokenTrait;
use EveryCheck\UserApiRestBundle\Tests\UserApiRestBundle\DataFixtures\BuilderTrait\UuidTrait;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\UserRole;
use EveryCheck\UserApiRestBundle\Entity\AuthToken;

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