<?php

namespace EveryCheck\ApiRestUserBundle\Entity;

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
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @JMS\Exclude
     */
    protected $user;


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
        return 'ROLE_' . strtoupper($this->getName());
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }   
}

