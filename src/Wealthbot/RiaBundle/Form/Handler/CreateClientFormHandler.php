<?php

namespace Wealthbot\RiaBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Entity\Profile;

class CreateClientFormHandler
{
    protected $request;
    protected $client;
    protected $form;

    public function __construct(Form $form, Request $request, EntityManager $em)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
    }

    public function process(User $ria)
    {
        if ('POST' == $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {

                $client = $this->form->getData();
                $client->setRoles(array('ROLE_CLIENT'));

                $profile = $client->getProfile();
                $profile->setRegistrationStep(0);
                $profile->setRia($ria);
                $profile->setClientSource(Profile::CLIENT_SOURCE_IN_HOUSE);

                $this->em->persist($client);
                $this->em->flush();

                return true;
            }
        }
        return false;
    }
}