<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\ClientBundle\Mailer;

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

    public function sendSuggestedPortfolioEmailMessage(User $client)
    {
        $template = $this->parameters['template']['suggested_portfolio'];
        $context = [
            'client' => $client,
            'portfolio' => $client->getProfile()->getSuggestedPortfolio(),
        ];

        $ria = $client->getProfile()->getRia();

        $this->sendMessage($template, $context, $this->parameters['from_email']['suggested_portfolio'], $ria->getEmail());
    }

    public function sendCloseAccountsMessage(User $client, $systemAccounts, array $closeMessages)
    {
        $template = $this->parameters['template']['close_accounts'];
        $context = [
            'client' => $client,
            'accounts' => $systemAccounts,
            'close_messages' => $closeMessages,
        ];

        $ria = $client->getProfile()->getRia();

        $this->sendMessage($template, $context, $this->parameters['from_email']['close_accounts'], $ria->getEmail());
    }

    public function sendWelcomeMessage(User $client, $password)
    {
        $template = $this->parameters['template']['welcome'];
        $context = [
            'client' => $client,
            'password' => $password,
            'url' => $this->router->generate('rx_user_homepage', [], true),
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['welcome'], $client->getEmail());
    }

    protected function sendMessage($templateName, $context, $fromEmail, $toEmail, array $attachments = [])
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

        if (is_array($attachments) && !empty($attachments)) {
            foreach ($attachments as $filename => $path) {
                $attachment = \Swift_Attachment::fromPath($path);
                if (is_string($filename)) {
                    $attachment->setFilename($filename);
                }

                $message->attach($attachment);
            }
        }

        $this->mailer->send($message);
    }
}
