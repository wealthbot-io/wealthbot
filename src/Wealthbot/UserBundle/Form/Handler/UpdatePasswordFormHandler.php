<?php

namespace Wealthbot\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\ChangePasswordFormHandler;
use FOS\UserBundle\Model\UserInterface;

class UpdatePasswordFormHandler extends ChangePasswordFormHandler
{
    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->form->getData()->getPlainPassword();
    }

    public function process(UserInterface $user)
    {
        $this->form->setData($user);

        if ('POST' === $this->request->getMethod()) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }
}
