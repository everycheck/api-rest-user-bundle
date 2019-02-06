<?php
namespace EveryCheck\ApiRestUserBundle\Service;

use EveryCheck\ApiRestUserBundle\Entity\User;

class UserPasswordGenerator
{
	public function __construct($passwordEncoder , $generatePassword = false)
	{
		$this->generatePassword = $generatePassword;
		$this->passwordEncoder = $passwordEncoder;

		//$this->generator = (new \RandomLib\Factory)->getHighStrengthGenerator();
	}

	public function setUpPassword(User $user, $password = null)
	{
		if( $password !== null )
		{
 	       $user->setPlainPassword($password);
		}
		else if($this->generatePassword)
		{
 	       $user->setPlainPassword($this->generator->generateString(16));
 	    }
 	    else
 	    {
 	       $user->setPlainPassword($user->getEmail());
 	    }
        $encoded = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encoded);
	}
}