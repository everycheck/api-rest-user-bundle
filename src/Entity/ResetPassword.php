<?php

namespace EveryCheck\UserApiRestBundle\Entity;

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

