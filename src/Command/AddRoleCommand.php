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
                'role name'
            )
            ->addArgument(
                'credential',
                InputArgument::REQUIRED,
                'credential associated to this role as int = C (1) + R (2) + U(4) + D(8)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $em          = $this->getContainer()->get('doctrine.orm.entity_manager');
        $username    =  $input->getArgument('username');   
        $roleName    = $input->getArgument('role');  
        $credential  = intval($input->getArgument('credential'));  

        $user = $em->getRepository(User::class)->findOneByUsername($username);
        if(empty($user)) 
        {
            $output->writeLn('<error>User "'.$username.'" does not exist.</error>');
            exit();
        }

        $role = $em->getRepository(UserRole::class)->findOneBy(['user'=>$user,'name'=>$roleName]);
        if(empty($role))
        {
            $role = new UserRole();
            $role->setName($roleName);
            $role->setUser($user);            
        }

        $role->setCreator(($credential & 1) == 1);
        $role->setReader (($credential & 2) == 2);
        $role->setUpdator(($credential & 4) == 4);
        $role->setDeletor(($credential & 8) == 8);

        $em->persist($role);
        $em->flush();

        $output->writeLn('<info>Role "'.$roleName.'" has been added to "'.$username.' with credential $credential".</info>');
    }

}