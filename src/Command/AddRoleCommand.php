<?php
namespace EveryCheck\UserApiRestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use EveryCheck\UserApiRestBundle\Entity\User;
use EveryCheck\UserApiRestBundle\Entity\UserRole;

class AddRoleCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('rest:role:add')
            ->setDescription('Add role to existing user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username wich must received a new role'
            )
            ->addArgument(
                'role',
                InputArgument::REQUIRED,
                'If not set `username`@exaple.org will be used'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $username =  $input->getArgument('username');   
        $roleName  = $input->getArgument('role');  

        $user = $em->getRepository(User::class)->findOneByUsername($username);
        if(empty($user)) 
        {
            $output->writeLn('<error>User "'.$username.'" does not exist.</error>');
            exit();
        }

        $role = new UserRole();
        $role->setName($roleName);
        $role->setUser($user);

        $em->persist($role);
        $em->flush();

        $output->writeLn('<info>Role "'.$roleName.'" has been added to "'.$username.'".</info>');
    }

}