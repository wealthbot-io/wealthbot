<?php

namespace App\Validator\Constraints;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use App\Model\Acl;

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
