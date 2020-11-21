<?php

namespace App\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Profile;
use App\Entity\User;

class CreateClientFormHandler
{
    protected $request;
    protected $client;
    protected $form;
    protected $em;

    public function __construct(Form $form, Request $request, EntityManager $em)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
    }

    public function process(User $ria)
    {
        if ('POST' === $this->request->getMethod()) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $client = $this->form->getData();
                $client->setRoles(['ROLE_CLIENT']);

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
