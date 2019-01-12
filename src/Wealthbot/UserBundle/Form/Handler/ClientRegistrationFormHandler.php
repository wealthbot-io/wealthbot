<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.09.13
 * Time: 17:05
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\UserBundle\Form\Handler;


use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;



class ClientRegistrationFormHandler implements EventSubscriberInterface
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
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

        $user->setRoles(['ROLE_CLIENT']);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());
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
