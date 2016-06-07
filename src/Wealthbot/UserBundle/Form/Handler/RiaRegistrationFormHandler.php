<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\AdminBundle\Manager\FeeManager;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;

class RiaRegistrationFormHandler extends RegistrationFormHandler
{
    protected $feeManager;

    public function __construct(FormInterface $form,
                                Request $request,
                                UserManagerInterface $userManager,
                                MailerInterface $mailer,
                                TokenGeneratorInterface $tokenGenerator,
                                FeeManager $feeManager)
    {
        parent::__construct($form, $request, $userManager, $mailer, $tokenGenerator);

        $this->feeManager = $feeManager;
    }

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

        $user->setRoles(['ROLE_RIA']);

        $riaCompanyInformation = new RiaCompanyInformation();
        $riaCompanyInformation->setName($user->getProfile()->getCompany());
        $riaCompanyInformation->setRia($user);
        $user->setRiaCompanyInformation($riaCompanyInformation);

        $this->userManager->updateUser($user);

        if ($riaCompanyInformation->getRelationshipType() === RiaCompanyInformation::RELATIONSHIP_TYPE_LICENSE_FEE) {
            $this->feeManager->resetRiaFee($user);
        }
    }
}
