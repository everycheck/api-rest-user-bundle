<?php 
namespace Tests\UserBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Tests\UserBundle\DataFixtures\BuilderTrait\UserTrait;
use Tests\UserBundle\DataFixtures\BuilderTrait\TokenTrait;
use Tests\UserBundle\DataFixtures\BuilderTrait\UuidTrait;

use UserBundle\Entity\User;
use UserBundle\Entity\AuthToken;

class LoadFixture implements FixtureInterface, ContainerAwareInterface
{
    use UserTrait;
    use TokenTrait;
    use UuidTrait;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->createUsersWithToken(['someone']);

        $this->manager->flush();
        $this->rewriteUuid([User::class,AuthToken::class]);      
    }
}