<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

abstract class BaseVoter implements VoterInterface
{
    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [
            'OWNER',
            'OPERATOR',
        ]);
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param object         $object     The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    abstract public function vote(TokenInterface $token, $object, array $attributes);
}
