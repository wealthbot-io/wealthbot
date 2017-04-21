<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\AdminBundle\Mailer;

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

    public function sendRiaActivatedEmailMessage($toEmail, User $ria)
    {
        $template = $this->parameters['template']['ria_activated'];
        $context = [
            'company_information' => $ria->getRiaCompanyInformation(),
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['ria_activated'], $toEmail);
    }

    public function sendInviteEmailMessage($invite)
    {
        $template = $this->parameters['template']['invite'];
        $context = [
            'invite' => $invite,
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['invite'], $invite['contact_email']);
    }

    public function sendCreatedAdminUserMessage(User $user, $pass, $level)
    {
        $template = $this->parameters['template']['created_user'];
        $context = [
            'user' => $user,
            'pass' => $pass,
            'level' => $level,
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['created_user'], $user->getEmail());
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
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }
}
