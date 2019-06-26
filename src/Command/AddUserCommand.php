<?php
namespace EveryCheck\UserApiRestBundle\Service;

use EveryCheck\UserApiRestBundle\Entity\User;

class UserPasswordGenerator
{
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
            $user->setPlainPassword($this->generateString(16));
         }
         else
         {
            $user->setPlainPassword($user->getEmail());
         }
        $encoded = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encoded);
    }

    protected function generateString($length)
    {
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1)
        {
            throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i)
        {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

}