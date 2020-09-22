<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use App\Entity\ClientAccount;
use App\Entity\User;

class ClientAccountVoter extends BaseVoter
{
    const PERMISSION_CHANGE_CONSOLIDATOR = 'CHANGE_CONSOLIDATOR';
    const PERMISSION_APPROVE_BILL = 'APPROVE_BILL';
    const PERMISSION_EDIT = 'EDIT';

    /**
     * @param TokenInterface $token
     * @param ClientAccount  $object
     * @param array          $attributes
     *
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object instanceof ClientAccount) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($user->isAdmin()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if (in_array($attributes[0], [static::PERMISSION_CHANGE_CONSOLIDATOR, static::PERMISSION_APPROVE_BILL, static::PERMISSION_EDIT])) {
            if ($user === $object->getClient()->getRia()) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
