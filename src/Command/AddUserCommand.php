<?php
namespace EveryCheck\UserApiRestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use EveryCheck\UserApiRestBundle\Entity\User;

class AddUserCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('rest:user:add')
            ->setDescription('List all fixture available for testing')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Login of the new user'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_OPTIONAL,
                'If not set `username`@exaple.org will be used'
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_OPTIONAL,
                'If not set email will be used'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $username =  $input->getArgument('username');   
        $email  = $input->getOption('email');  
        if(empty($email))
        {
            $email = $username.'@example.org';
        }
        $password  = $input->getOption('password');  
        if(empty($password))
        {
            $password = $username;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setLastPasswordUpdate(new \DateTime("now"));
        $this->getContainer()->get('password_generator')->setUpPassword($user,$password);

        $em->persist($user);
        $em->flush();

        $output->writeLn('<info>User "'.$username.'" has been created.</info>');
    }

}