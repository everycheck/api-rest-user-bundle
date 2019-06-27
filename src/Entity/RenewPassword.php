<?php
namespace EveryCheck\UserApiRestBundle\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

class RenewPassword
{
    protected $login;
    protected $password;
    
    /**
     * @RollerworksPassword\Blacklist(provider='blacklist_provider')
     * @RollerworksPassword\PasswordStrength(minLength=6, minStrength=3)
     */
    protected $newPassword;


    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getNewPassword()
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;
    }
}