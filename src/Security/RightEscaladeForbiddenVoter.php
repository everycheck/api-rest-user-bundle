<?php
namespace EveryCheck\UserApiRestBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use EveryCheck\UserApiRestBundle\Entity\UserRole;
use EveryCheck\UserApiRestBundle\Entity\User;

class RightEscaladeForbiddenVoter extends Voter
{

    protected function supports($attribute, $subject)
    {        

        if($attribute !== 'role') return false;
        if(!is_array($subject)) return false;
        if(!array_key_exists('name', $subject)) return false;
        if( !array_key_exists('creator', $subject) && 
            !array_key_exists('reader' , $subject) && 
            !array_key_exists('updator', $subject) && 
            !array_key_exists('deletor', $subject)  
          ) return false;
        
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }
        
        $roles = [];

        foreach ($user->getRoles() as $role)
        {   
            if($role->getName() == $subject['name'])
            {
                if(array_key_exists('creator', $subject) && !$role->getCreator()) return false;
                if(array_key_exists('reader' , $subject) && !$role->getReader() ) return false;
                if(array_key_exists('updator', $subject) && !$role->getUpdator()) return false;
                if(array_key_exists('deletor', $subject) && !$role->getDeletor()) return false;
                return true;
            }
        }

        return false;

    }
}