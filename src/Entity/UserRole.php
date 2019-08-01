<?php

namespace EveryCheck\UserApiRestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * UserRole
 *
 * @ORM\Table(name="s_user_role",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="role_unique",columns={"user_id", "name"})
 *    }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity()
 */
class UserRole implements RoleInterface 
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
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(name="creator", type="boolean" )
     */
    private $creator = false;

    /**
     * @ORM\Column(name="reader", type="boolean" )
     */
    private $reader = false;

    /**
     * @ORM\Column(name="updator", type="boolean" )
     */
    private $updator = false;

    /**
     * @ORM\Column(name="deletor", type="boolean" )
     */
    private $deletor = false;

    /**
     * @ORM\Column(name="administrator", type="datetime", nullable=true)
     */
    private $administrator;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @JMS\Exclude
     */
    private $user;

    /**
     * Get id
     *
     * @return int
     */
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getRole()
    {
        $role = 'ROLE_' . strtoupper($this->getName());

        $allowed  = $this->getCreator()       ? 'C' : '';
        $allowed .= $this->getReader()        ? 'R' : '';
        $allowed .= $this->getUpdator()       ? 'U' : '';
        $allowed .= $this->getDeletor()       ? 'D' : '';
        $allowed .= $this->canAdministrate()  ? 'A' : '';

        if(!empty($allow)) $role .= '_' .$allow;

        return $role;
    }

    public function getSplittedRoles() : array
    {
        $prefix = 'ROLE_' . strtoupper($this->getName());
        $roles = [];

        if($this->getCreator())      $roles[] = $prefix.'_CREATE';
        if($this->getReader())       $roles[] = $prefix.'_READ';
        if($this->getUpdator())      $roles[] = $prefix.'_UPDATE';
        if($this->getDeletor())      $roles[] = $prefix.'_DELETE';
        if($this->canAdministrate()) $roles[] = $prefix.'_ADMINISTRATE';

        return $roles;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }   

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator(?bool $creator)
    {
        $this->creator = $creator;
    }   

    public function getReader()
    {
        return $this->reader;
    }

    public function setReader(?bool $reader)
    {
        $this->reader = $reader;
    }   

    public function getUpdator()
    {
        return $this->updator;
    }

    public function setUpdator(?bool $updator)
    {
        $this->updator = $updator;
    }   

    public function getDeletor()
    {
        return $this->deletor;
    }

    public function setDeletor(?bool $deletor)
    {
        $this->deletor = $deletor;
    }   

    public function canAdministrate()
    {
        if(empty($this->administrator)) return false;
        if(!($this->administrator instanceof \DateTime)) return false;

        return $this->administrator > new \DateTime();
    }

    public function getAdministrator()
    {
        return $this->administrator;
    }

    public function setAdministrator(?\DateTime $administrator)
    {
        $this->administrator = $administrator;
    }   

}

