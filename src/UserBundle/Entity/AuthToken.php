<?php
namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/** 
 * AuthToken
 *
 * @ORM\Entity
 * @ORM\Table(name="s_auth_tokens")
 */
class AuthToken
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
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