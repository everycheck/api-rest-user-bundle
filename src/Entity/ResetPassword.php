<?php

namespace EveryCheck\UserApiRestBundle\Entity;

use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

/**
 * ResetPassword
 *
 */
class ResetPassword
{
    /**
     * @var string
     *  
     */
    private $token;

    /**
     * @var string
     * @RollerworksPassword\Blacklist()
     * @RollerworksPassword\PasswordStrength(minLength=6, minStrength=3)
     */
    private $password;

    public function setToken($token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}

