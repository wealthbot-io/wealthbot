<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\RiaBundle\Mailer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wealthbot\UserBundle\Entity\User;

/**
 * @author
 */
class TwigSwiftMailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $twig;
    protected $parameters;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $twig, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmailMessage(User $user)
    {
        $template = $this->parameters['template']['confirmation'];
        $url = 'some url'; //$this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        $context = [
            'user' => $user,
            'confirmationUrl' => $url,
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

    public function sendInviteEmailMessage($invite)
    {
        $template = $this->parameters['template']['invite'];
        $context = [
            'invite' => $invite,
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email']['invite'], $invite['contact_email']);
    }

    /**
     * @param array $context
     *
     * @return bool
     */
    public function sendEmailBillMessage(array $context)
    {
        return $this->sendMessage(
            $this->parameters['template']['bill_report'],
            $context,
            $context['ria']->getEmail(),
            $context['client']->getEmail()
        );
    }

    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
        ;

        if (!empty($htmlBody)) {
            $message
                ->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain')
            ;
        } else {
            $message->setBody($textBody);
        }

        return $this->mailer->send($message);
    }
}
