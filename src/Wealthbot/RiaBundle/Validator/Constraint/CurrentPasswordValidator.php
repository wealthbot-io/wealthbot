<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\RiaBundle\Validator\Constraint;

use Wealthbot\ClientBundle\Model\Acl;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class CurrentPasswordValidator extends ConstraintValidator
{
    private $acl;
    private $encoderFactory;

    public function __construct(Acl $acl, EncoderFactoryInterface $encoderFactory)
    {
        $this->acl = $acl;
        $this->encoderFactory = $encoderFactory;
    }

    public function validate($password, Constraint $constraint)
    {
        if ($this->acl->isRiaClientView()) {
            $user = $this->acl->getClientForRiaClientView($this->acl->getUser());
        } else {
            $user = $this->acl->getUser();
        }

        if (!$user instanceof UserInterface) {
            throw new ConstraintDefinitionException('The User must extend UserInterface');
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            $this->context->addViolation($constraint->message);
        }
    }
}
