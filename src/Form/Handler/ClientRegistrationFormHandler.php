<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.09.13
 * Time: 17:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use FOS\UserBundle\Model\UserManager;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientRegistrationFormHandler
{
    private $container;
    protected $form;
    protected $tokenGenerator;
    protected $mailer;
    protected $userManager;

    public function __construct(ContainerInterface $container, $form, UserManager $userManager, $mailer, $tokenGenerator)
    {
        $this->container = $container;
        $this->form = $form;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
        $this->userManager = $userManager;
    }

    public function process()
    {
        $user = $this->form->getData();
        $confirmation = false;
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

        return $user;
    }
}
