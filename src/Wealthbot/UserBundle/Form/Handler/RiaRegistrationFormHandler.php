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

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Wealthbot\AdminBundle\Manager\FeeManager;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;


class RiaRegistrationFormHandler implements EventSubscriberInterface
{

    protected $feeManager;


    private $container;

    public function __construct(FormInterface $form,
                                RequestStack $requestStack,
                                UserManagerInterface $userManager,
                                MailerInterface $mailer,
                                TokenGeneratorInterface $tokenGenerator,
                                FeeManager $feeManager,
ContainerInterface $container)
    {
        parent::__construct($form, $requestStack->getCurrentRequest(), $userManager, $mailer, $tokenGenerator);

        $this->feeManager = $feeManager;
        $this->container = $container;
    }

    protected function onSuccess(FormEvent $event)
    {

        $user = $event->getForm()->getData();
        $confirmation = $this->container->getParameter('fos_user.registration.confirmation.enabled');


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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onSuccess',
        );
    }
}
