<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 30.07.13
 * Time: 11:22
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\MailerBundle\Mailer\TwigSwiftMailer;
use Wealthbot\UserBundle\Entity\User;

class SlaveClientFormHandler
{
    private $form;
    private $request;
    private $em;
    private $mailer;

    public function __construct(FormInterface $form, Request $request, EntityManager $em, TwigSwiftMailer $mailer)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function process(User $masterClient)
    {
        if ($this->request->isMethod('post')) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($masterClient);

                return true;
            }
        }

        return false;
    }

    private function onSuccess(User $masterClient)
    {
        /** @var User $slaveClient */
        $slaveClient = $this->form->getData();
        $access = $this->form->get('access')->getData();

        $password = $slaveClient->generateTemporaryPassword();

        if ($access === 'full') {
            $roles = ['ROLE_SLAVE_CLIENT', 'ROLE_CLIENT_FULL'];
        } else {
            $roles = ['ROLE_SLAVE_CLIENT'];
        }

        $slaveClient->setPlainPassword($password);
        $slaveClient->setRoles($roles);
        $slaveClient->setMasterClient($masterClient);
        $slaveClient->setEnabled(true);

        $this->em->persist($slaveClient);
        $this->em->flush();

        $this->mailer->sendClientUserCreateEmail($slaveClient, $password);
    }
}
