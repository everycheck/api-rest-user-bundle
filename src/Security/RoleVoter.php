<?php
namespace EveryCheck\UserApiRestBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use EveryCheck\UserApiRestBundle\Entity\User;

class RoleVoter extends Voter
{

    protected function supports($attribute, $subject)
    {
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
            $roles = array_merge($roles, $role->getSplittedRoles());
        }

        return in_array($attribute, $roles);

    }
}