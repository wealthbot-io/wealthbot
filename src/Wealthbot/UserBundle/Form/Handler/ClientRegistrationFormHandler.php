<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.09.13
 * Time: 17:05
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler;
use FOS\UserBundle\Model\UserInterface;

class ClientRegistrationFormHandler extends RegistrationFormHandler
{
    protected function onSuccess(UserInterface $user, $confirmation)
    {
        if ($confirmation) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->mailer->sendConfirmationEmailMessage($user);
        } else {
            $user->setEnabled(true);
        }

        $user->setRoles(['ROLE_CLIENT']);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());
    }
}
