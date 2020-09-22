<?php

namespace App\Controller\User;

use FOS\UserBundle\Controller\SecurityController as BaseSecurity;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;

class SecurityController extends BaseSecurity
{
    public function login(Request $request)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session Session */
        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

        $csrfToken = $this->container->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        $riaLogo = null;

        $subDomainManager = $this->container->get('wealthbot_user.subdomain_manager');
        $riaCompanyInformation = $subDomainManager->getRiaCompanyInformation();

        if ($riaCompanyInformation) {
            $riaLogo = $this->container->get('router')->generate('rx_file_download', ['ria_id' => $riaCompanyInformation->getRia()->getId()], true);
        }

        $redirectUrl = $request->get('redirect_url', null);
        if (null !== $redirectUrl) {
            $redirectUrl = urldecode($redirectUrl);
            $session->set('redirect_url', $redirectUrl);
        }

        return $this->renderLogin([
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
            'ria_logo' => $riaLogo,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $form = $this->container->get('form.factory')->createBuilder(FormType::class, null, [])
            ->add('email', EmailType::class, ['label' => 'Enter in your email bellow and we\'ll send you a link to reset your password.'])
            ->getForm();

        $subDomainManager = $this->container->get('wealthbot_user.subdomain_manager');
        $riaCompanyInformation = $subDomainManager->getRiaCompanyInformation();
        $riaLogo = $riaCompanyInformation ? $this->container->get('router')->generate('rx_file_download', ['ria_id' => $riaCompanyInformation->getRia()->getId()], true) : '/img/logo.png';

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $em->getRepository('App\Entity\User')->findOneBy(['email' => $data['email']]);

                if ($user) {
                    $newPassword = $user->generateTemporaryPassword();
                    $user->setPlainPassword($newPassword);
                    $user->setIsPasswordReset(true);

                    $em->persist($user);
                    $em->flush();

                    if ($user->hasRole('ROLE_CLIENT')) {
                        $mailer = $this->container->get('wealthbot.mailer');
                        $mailer->sendClientResetSelfPasswordEmail($user, $newPassword);
                    } else {
                        $mailer = $this->container->get('wealthbot.mailer');
                        $mailer->sendRiaUserResetPasswordEmail($user, $user, $newPassword);
                    }

                    return new Response($this->container->get('twig')->render(
                        '/User/Security/reset_password.html.twig',
                        [
                            'form' => $form->createView(),
                            'success' => true,
                            'ria_logo' => $riaLogo,
                        ]
                    ));
                }
            }
        }

        return new Response($this->container->get('twig')->render(
            '/User/Security/reset_password.html.twig',
            [
                'form' => $form->createView(),
                'ria_logo' => !empty($riaLogo) ? $riaLogo : null,
            ]
        ));
    }

    public function check()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logout()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return Response
     */
    protected function renderLogin(array $data)
    {
        return $this->render('/User/Security/login.html.twig', $data);
    }
}
