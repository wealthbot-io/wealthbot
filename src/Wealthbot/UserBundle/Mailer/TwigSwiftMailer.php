<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\UserBundle\Mailer;

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

    public function sendAdvCopyEmailMessage(User $client)
    {
        $ria = $client->getProfile()->getRia();
        $companyInformation = $ria->getRiaCompanyInformation();

        $template = $this->parameters['template']['adv_copy'];
        $context = [
            'client' => $client->getUsername(),
            'company' => $companyInformation->getName(),
        ];

        $extension = pathinfo($companyInformation->getAdvCopy(), PATHINFO_EXTENSION);
        $attachments = ['ADV Copy.'.$extension => $companyInformation->getWebAdvCopy()];

        $this->sendMessage($template, $context, $this->parameters['from_email']['adv_copy'], $client->getEmail(), $attachments);
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

                //TODO need to assure to use correct absolute path!
                if (file_exists($path)) {
                    $attachment = \Swift_Attachment::fromPath($path);
                    if (is_string($filename)) {
                        $attachment->setFilename($filename);
                    }
                    $message->attach($attachment);
                }
            }
        }

        $this->mailer->send($message);
    }
}
