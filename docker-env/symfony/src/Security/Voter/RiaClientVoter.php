<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use App\Entity\User;

class RiaClientVoter extends BaseVoter
{
    /**
     * @param TokenInterface $token
     * @param object         $object
     * @param array          $attributes
     *
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object instanceof User) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        /* @var $ria User */
        $ria = $token->getUser();

        if ($ria === $object->getRia()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
