<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Handler;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Manager\FeeManager;
use App\Entity\RiaCompanyInformation;

class RiaRegistrationFormHandler
{
    protected $feeManager;
    protected $form;
    protected $request;
    protected $userManager;
    protected $mailer;
    protected $tokenGenerator;

    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        UserManagerInterface $userManager,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator,
        \App\Manager\FeeManager $feeManager
    ) {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->feeManager = $feeManager;
    }

    public function process($form)
    {
        $user = $form->getData();
        $confirmation = false; //@todo: fix //

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

        if (RiaCompanyInformation::RELATIONSHIP_TYPE_LICENSE_FEE === $riaCompanyInformation->getRelationshipType()) {
            $this->feeManager->resetRiaFee($user);
        }

        return true;
    }
}
