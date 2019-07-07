<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 10.06.13
 * Time: 19:15
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Form\Handler\AbstractFormHandler;
use App\Mailer\TwigSwiftMailer;
use App\Entity\Profile;
use App\Entity\User;

class InviteProspectFormHandler extends AbstractFormHandler
{
    public function success()
    {
        $emailService = $this->getOption('email_service');
        $ria = $this->getOption('ria');

        if (!($ria instanceof User)) {
            throw new \InvalidArgumentException(sprintf('Option ria must be instance of %s', get_class(new User())));
        }

        if (!($emailService instanceof TwigSwiftMailer)) {
            throw new \InvalidArgumentException(sprintf('Option email_service must be instance of Mailer\TwigSwiftMailer'));
        }

        $type = $this->form->get('type')->getData();

        /** @var User $prospect */
        $prospect = $this->form->getData();

        if ('internal' === $type) {
            /** @var Profile $profile */
            $profile = $prospect->getProfile();

            $profile->setStatusProspect();
            $profile->setRia($ria);

            $prospect->setEnabled(true);

            $password = $prospect->generateTemporaryPassword();
            $prospect->setPlainPassword($password);
            $prospect->addRole('ROLE_CLIENT');

            $this->em->persist($prospect);
            $this->em->flush();

            $emailService->sendClientInviteInternalProspectEmail($ria, $prospect, $password);
        } else {
            $groupName = null;
            $group = $prospect->getGroups()->first();
            if ($group) {
                $groupName = $group->getName();
            }

            $emailService->sendClientInviteProspectEmail($ria, $prospect->getEmail(), $groupName);
        }
    }
}
