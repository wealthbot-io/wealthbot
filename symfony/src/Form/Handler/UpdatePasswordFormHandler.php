<?php

namespace App\Form\Handler;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/* TODO:<Symfony3> */
class UpdatePasswordFormHandler implements EventSubscriberInterface
{
    private $form;
    private $request;

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->form->getData()->getPlainPassword();
    }

    public function onSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        $this->form->setData($user);

        if ('POST' === $this->request->getCurrentRequest()->getMethod()) {
            $this->form->handleRequest($this->request->getCurrentRequest());

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::CHANGE_PASSWORD_COMPLETED => 'onSuccess',
        );
    }
}
