<?php

namespace App\Controller\User;

use App\Form\Type\RiaRegistrationType;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use App\Entity\Group;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RiaController extends Controller
{
    public function registration(Request $request)
    {
        $form = $this->createForm(RiaRegistrationType::class);
        $formHandler = $this->container->get('wealthbot_user.registration.ria.form.handler');
        $em = $this->get('doctrine.orm.entity_manager');

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $process = $formHandler->process($form);
                if ($process) {
                    $user = $form->getData();

                    $groupAll = $em->getRepository('App\Entity\Group')->findOneBy(['name' => Group::GROUP_NAME_ALL]);
                    $user->addGroup($groupAll);

                    $em->persist($user);
                    $em->flush();
                    $response = $this->redirect($this->generateUrl('rx_ria_company_profile'));
                    $this->authenticateUser($user, $request);

                    return $response;
                }
            };
        }

        return $this->render('/User/Ria/registration.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Authenticate a user with Symfony Security.
     *
     * @param \FOS\UserBundle\Model\UserInterface        $user
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function authenticateUser(UserInterface $user, Request $request)
    {
        try {
            $token = new UsernamePasswordToken($user, null, $this->container->getParameter('fos_user.firewall_name'), $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            // Fire the login event manually
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
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
