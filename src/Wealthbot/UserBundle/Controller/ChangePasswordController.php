<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wealthbot\RiaBundle\Mailer\MailerInterface;

/**
 * Controller managing the password change.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ChangePasswordController extends BaseController
{
    /**
     * Change user password.
     */
    public function changePasswordAction()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $form = $this->container->get('fos_user.change_password.form');
        $formHandler = $this->container->get('fos_user.change_password.form.handler');

        $process = $formHandler->process($user);
        if ($process) {
            $this->setFlash('fos_user_success', 'change_password.flash.success');

            return new RedirectResponse($this->getRedirectionUrl($user));
        }

        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:ChangePassword:changePassword.html.'.$this->container->getParameter('fos_user.template.engine'),
            ['form' => $form->createView()]
        );
    }

    public function resetPasswordAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var $mailer MailerInterface */
        $mailer = $this->container->get('wealthbot_ria.mailer');
        $user = $em->getRepository('WealthbotUserBundle:User')->find($request->get('user_id'));

        if (!$user) {
            throw $this->createNotFoundException('User does not exist.');
        }

        $user->setPlainPassword($user->generateTemporaryPassword());
        $mailer->sendConfirmationEmailMessage($user);

        $this->container->get('session')->getFlashBag()->add('success', 'Password for user "'.$user->getProfile()->getLastName().' '.$user->getProfile()->getFirstName().'" has been reseted successfully.');

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }

    /**
     * Generate the redirection url when the resetting is completed.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('rx_after_login');
    }

    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->add($action, $value);
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @param string    $message  A message
     * @param Exception $previous The previous exception
     *
     * @return NotFoundHttpException
     */
    private function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }
}
