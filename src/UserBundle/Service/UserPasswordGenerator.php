<?php
namespace UserBundle\Service;

use UserBundle\Entity\User;

class UserPasswordGenerator
{
	private $generatePassword = false;
	private $passwordEncoder = null;

	public function __construct($passwordEncoder , $generatePassword = false)
	{
		$this->generatePassword = $generatePassword;
		$this->passwordEncoder = $passwordEncoder;
	}

	public function setUpPassword(User $user, $password = null)
	{
		if( $password !== null )
		{
 	       $user->setPlainPassword($password);
		}
		else if($this->generatePassword)
		{
 	       $user->setPlainPassword($this->randomPassword());
 	    }
 	    else
 	    {
 	       $user->setPlainPassword($user->getEmail());
 	    }
        $encoded = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encoded);
	}

	protected function randomPassword($len = 8)
	{

		if( ($len%2) !== 0)
		{ // Length paramenter must be a multiple of 2
			$len=8;
		}

		$length=$len-2; // Makes room for the two-digit number on the end
		$conso=array('b','c','d','f','g','h','j','k','l','m','n','p','r','s','t','v','w','x','y','z');
		$vocal=array('a','e','i','o','u');
		$password='';
		srand ((double)microtime()*1000000);
		$max = $length/2;
		for($i=1; $i<=$max; $i++){
			$password.=$conso[rand(0,19)];
			$password.=$vocal[rand(0,4)];
		}
		$password.=rand(10,99);
		$newpass = $password;
		return $newpass;
	}
}