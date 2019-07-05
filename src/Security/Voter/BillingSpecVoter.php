<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use App\Entity\BillingSpec;

class BillingSpecVoter extends BaseVoter
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
        if (!$object instanceof BillingSpec) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if ($user === $object->getOwner()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
