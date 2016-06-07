<?php

namespace Wealthbot\UserBundle\Controller;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Wealthbot\UserBundle\Entity\Group;

class RiaController extends Controller
{
    public function registrationAction()
    {
        $form = $this->container->get('wealthbot_user.registration.ria.form');
        $formHandler = $this->container->get('wealthbot_user.registration.ria.form.handler');
        $em = $this->get('doctrine.orm.entity_manager');

        $process = $formHandler->process();
        if ($process) {
            $user = $form->getData();

            $groupAll = $em->getRepository('WealthbotUserBundle:Group')->findOneBy(['name' => Group::GROUP_NAME_ALL]);
            $user->addGroup($groupAll);

            $em->persist($user);
            $em->flush();
            $response = $this->redirect($this->generateUrl('rx_ria_company_profile'));
            $this->authenticateUser($user, $response);

            return $response;
        }

        return $this->render('WealthbotUserBundle:Ria:registration.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Authenticate a user with Symfony Security.
     *
     * @param \FOS\UserBundle\Model\UserInterface        $user
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function authenticateUser(UserInterface $user, Response $response)
    {
        try {
            $this->container->get('fos_user.security.login_manager')->loginUser(
                $this->container->getParameter('fos_user.firewall_name'),
                $user,
                $response);
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }

    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->add($action, $value);
    }
}
