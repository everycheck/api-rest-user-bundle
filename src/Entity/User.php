<?php
namespace EveryCheck\UserApiRestBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="s_user")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")  
     * @JMS\Exclude 
     */
    private $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     * @JMS\Accessor(getter="getUuidAsString") 
     */
    private $uuid;

    /**
     * @ORM\Column(name="username", type="string", length=255, unique=true)  
     */
    private $username;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)  
     */
    private $email;

    /**
     * @JMS\Exclude
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @JMS\Exclude
     */
    private $plainPassword;

    /**
     * Old password send by user when updating password, never stored
     * @var string
     *
     * @JMS\Exclude
     */
    private $oldPassword;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Exclude
     */
    private $lastPasswordUpdate;

    /**
     * @ORM\Column(name="active", type="boolean", nullable=false, options={"default" : 1})
     */
    private $active = true;

    /**
     * @ORM\OneToMany(targetEntity="UserRole", mappedBy="user")
     */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }


    public function getId()
    {
        return $this->id;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getUuidAsString()
    {
        return $this->uuid->toString();
    }
    
    /**
     * @ORM\PrePersist
     */
    public function setupUuid()
    {
        $this->setUuid(\Ramsey\Uuid\Uuid::uuid4());
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    public function setLastPasswordUpdate(\DateTime $lastPasswordUpdate): self
    {
        $this->lastPasswordUpdate = $lastPasswordUpdate;
        return $this;
    }

    public function getLastPasswordUpdate(): ?\DateTime
    {
        return $this->lastPasswordUpdate;
    }

    public function getSalt()
    {
        return null;
    }

    public function getRoles()
    {
        return $this->roles->getValues();
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

     public function isActive()
     {
         return $this->active;
     }
 
     public function setActive($active)
     {
         $this->active = $active;
         return $this;
     }
}

