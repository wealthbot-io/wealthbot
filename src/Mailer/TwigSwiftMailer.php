<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mailer;

use App\Entity\ClientAccount;
use App\Entity\Document;
use App\Entity\Workflow;
use App\Model\AccountOwnerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;

/**
 * @author
 */
class TwigSwiftMailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $twig;
    protected $parameters;
    protected $em;
    protected $from_email;
    protected $logger;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig\Environment $twig, $em, $from_email, $parameters, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = (array) $parameters;
        $this->em = $em;
        $this->from_email = $from_email;
        $this->logger = $logger;
    }

    public function sendSuggestedPortfolioEmailMessage(User $client)
    {
        $template = $this->parameters['suggested_portfolio'];
        $context = [
            'client' => $client,
            'portfolio' => $client->getProfile()->getSuggestedPortfolio(),
        ];

        $ria = $client->getProfile()->getRia();

        $this->sendMessage($template, $context, $this->from_email, $ria->getEmail());
    }

    public function sendCloseAccountsMessage(User $client, $systemAccounts, array $closeMessages)
    {
        $template = $this->parameters['close_accounts'];
        $context = [
            'client' => $client,
            'accounts' => $systemAccounts,
            'close_messages' => $closeMessages,
        ];

        $ria = $client->getProfile()->getRia();

        $this->sendMessage($template, $context, $this->from_email, $ria->getEmail());
    }

    public function sendWelcomeMessage(User $client, $password)
    {
        $template = $this->parameters['welcome'];
        $context = [
            'client' => $client,
            'password' => $password,
            'url' => $this->router->generate('rx_user_homepage', [], true),
        ];

        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }

    protected function sendMessage($templateName, $context, $fromEmail, $toEmail, array $attachments = [])
    {
        try {
            $template = $this->twig->load($templateName);
            $subject = $template->renderBlock('subject', $context);
            $textBody = $template->renderBlock('body_text', $context);
            $htmlBody = $template->renderBlock('body_html', $context);

            $message = new \Swift_Message($subject);
            $message
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
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, 'Error sending message');
        };
    }

    public function sendRiaActivatedEmailMessage($toEmail, User $ria)
    {
        $template = $this->parameters['ria_activated'];
        $context = [
            'company_information' => $ria->getRiaCompanyInformation(),
        ];

        $this->sendMessage($template, $context, $this->from_email, $toEmail);
    }

    public function sendInviteEmailMessage($invite)
    {
        $template = $this->parameters['invite'];
        $context = [
            'invite' => $invite,
        ];

        $this->sendMessage($template, $context, $this->from_email, $invite['contact_email']);
    }

    public function sendCreatedAdminUserMessage(User $user, $pass, $level)
    {
        $template = $this->parameters['created_user'];
        $context = [
            'user' => $user,
            'pass' => $pass,
            'level' => $level,
        ];

        $this->sendMessage($template, $context, $this->from_email, $user->getEmail());
    }



    public function sendConfirmationEmailMessage(User $user)
    {
        $template = $this->parameters['confirmation'];
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        $context = [
            'user' => $user,
            'confirmationUrl' => $url,
        ];

        $this->sendMessage($template, $context, $this->from_email, $user->getEmail());
    }


    /**
     * @param array $context
     *
     * @return bool
     */
    public function sendEmailBillMessage(array $context)
    {
        return $this->sendMessage(
            $this->parameters['bill_report'],
            $context,
            $context['ria']->getEmail(),
            $context['client']->getEmail()
        );
    }


    public function sendAdvCopyEmailMessage(User $client)
    {
        $ria = $client->getProfile()->getRia();
        $companyInformation = $ria->getRiaCompanyInformation();

        $template = $this->parameters['adv_copy'];
        $context = [
            'client' => $client->getUsername(),
            'company' => $companyInformation->getName(),
        ];

        $extension = pathinfo($companyInformation->getAdvCopy(), PATHINFO_EXTENSION);
        $attachments = ['ADV Copy.'.$extension => $companyInformation->getWebAdvCopy()];

        $this->sendMessage($template, $context, $this->from_email, $client->getEmail(), $attachments);
    }


    public function sendAdminsRiaActivatedEmail(User $ria)
    {
        $template = $this->parameters['admin_ria_activated'];
        $context = [
            'ria_name' => $ria->getRiaCompanyInformation()->getName(),
        ];
        $repository = $this->em->getRepository('App\Entity\User');
        $admins = $repository->getAllAdmins();
        $toEmails = [];
        /** @var User $admin */
        foreach ($admins as $admin) {
            $toEmails[] = $admin->getEmail();
        }
        $this->sendMessage($template, $context, $this->from_email, $toEmails);
    }
    //-----------------RIA EMAILS-------------------------
    public function sendRiaNotFinishedRegistrationEmail(User $ria)
    {
        $template = $this->parameters['ria_not_finished_registration'];
        $context = [
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];
        $this->sendMessage($template, $context, $this->from_email, $ria->getEmail());
    }
    public function sendRiaChangePasswordEmail(User $ria)
    {
        $template = $this->parameters['ria_change_password'];
        $context = [
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];
        $this->sendMessage($template, $context, $this->from_email, $ria->getEmail());
    }
    public function sendRiaUserResetPasswordEmail(User $ria, User $riaUser, $newPassword)
    {
        $template = $this->parameters['ria_user_reset_password'];
        $context = [
            'ria_name' => $ria->getFullName(),
            'new_password' => $newPassword,
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];
        $this->sendMessage($template, $context, $this->from_email, $riaUser->getEmail());
    }
    public function sendRiaUserCreateEmail(User $riaUser, $password)
    {
        $template = $this->parameters['ria_user_create'];
        $context = [
            'username' => $riaUser->getUsername(),
            'password' => $password,
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];
        $this->sendMessage($template, $context, $this->from_email, $riaUser->getEmail());
    }
    public function sendRiaClientSuggestedPortfolioEmail(User $client)
    {
        $riaAlertsConfiguration = $client->getRia()->getAlertsConfiguration();
        if ($riaAlertsConfiguration && !$riaAlertsConfiguration->getIsClientPortfolioSuggestion()) {
            return;
        }
        $template = $this->parameters['ria_client_suggested_portfolio'];
        $context = [
            'client' => $client,
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];
        $this->sendRiaEmails($client->getRia(), $template, $this->from_email, $context);
    }
    public function sendRiaClientClosedAccountsEmail(User $client, $accounts)
    {
        $riaAlertsConfiguration = $client->getRia()->getAlertsConfiguration();
        if ($riaAlertsConfiguration && !$riaAlertsConfiguration->getIsClientDrivenAccountClosures()) {
            return;
        }
        $template = $this->parameters['ria_client_closed_accounts'];
        $context = [
            'client' => $client,
            'accounts' => $accounts,
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];
        $this->sendRiaEmails($client->getRia(), $template, $this->from_email, $context);
    }
    public function sendRiaActivatedEmail(User $ria)
    {
        $template = $this->parameters['ria_activated'];
        $context = [
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];
        $this->sendRiaEmails($ria, $template, $this->from_email, $context);
    }
    private function sendRiaEmails(User $ria, $template, $fromEmail, $context)
    {
        $this->sendMessage($template, $context, $fromEmail, $ria->getEmail());
        $riaUsers = $this->em->getRepository('App\Entity\User')->getUsersByRiaId($ria->getId());
        foreach ($riaUsers as $riaUser) {
            $this->sendMessage($template, $context, $fromEmail, $riaUser->getEmail());
        }
    }
    //-----------------CLIENT EMAILS-------------------------
    //$context include required keys for layout: logo, ria
    public function sendClientNotFinishedRegistrationEmail(User $client)
    {
        $template = $this->parameters['client_not_finished_registration'];
        $ria = $client->getRia();
        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }
    public function sendClientNotApprovedPortfolioEmail(User $client)
    {
        $template = $this->parameters['client_not_approved_portfolio'];
        $ria = $client->getRia();
        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }
    public function sendClientNotCompleteAllApplicationsEmail(User $client)
    {
        $template = $this->parameters['client_not_completed_all_applications'];
        $ria = $client->getRia();
        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }
    public function sendClientRolloverInstruction401Email(ClientAccount $account, $rolloverMessage)
    {
        $template = $this->parameters['client_rollover_instruction_401'];
        $client = $account->getClient();
        $ria = $client->getRia();
        $context = [
            'account' => $account,
            'ria' => $ria,
            'rollover_message' => $rolloverMessage,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }
    public function sendClientUserCreateEmail(User $clientUser, $password)
    {
        $template = $this->parameters['client_user_create'];
        $ria = $clientUser->getMasterClient()->getRia();
        $context = [
            'new_client' => $clientUser,
            'new_password' => $password,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $this->from_email, $clientUser->getEmail());
    }
    public function sendClientResetSelfPasswordEmail(User $client, $newPassword)
    {
        $template = $this->parameters['client_reset_self_password'];
        $ria = $client->getRia();
        $context = [
            'new_password' => $newPassword,
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }
    public function sendClientResetPasswordEmail(User $client)
    {
        $template = $this->parameters['client_reset_password'];
        $ria = $client->getRia();
        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }
    public function sendClientUpdatedDocumentsEmail(User $client, $documentType)
    {
        $template = $this->parameters['client_updated_documents'];
        switch ($documentType) {
            case Document::TYPE_ADV:
                $documentStr = 'ADV';
                break;
            case Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT:
                $documentStr = 'Investment Management Agreement';
                break;
            default:
                throw new ParameterNotFoundException($documentType);
        }
        $ria = $client->getMasterClientId() ? $client->getMasterClient()->getRia() : $client->getRia();
        $context = [
            'client' => $client,
            'document_type' => $documentStr,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }
    public function sendClientInviteProspectEmail(User $ria, $clientEmail, $group = null)
    {
        $template = $this->parameters['client_invite_prospect'];
        $context = [
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
            'group' => $group,
        ];
        $this->sendMessage($template, $context, $this->from_email, $clientEmail);
    }
    public function sendClientInviteInternalProspectEmail(User $ria, User $client, $tmpPassword)
    {
        $template = $this->parameters['client_invite_internal_prospect'];
        $context = [
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
            'new_password' => $tmpPassword,
            'client' => $client,
        ];
        $this->sendMessage($template, $context, $this->from_email, $client->getEmail());
    }
    public function sendClientAdvCopyEmailMessage(User $client)
    {
        $ria = $client->getProfile()->getRia();
        $companyInformation = $ria->getRiaCompanyInformation();
        $template = $this->parameters['client_adv_copy'];
        $fromEmail = $this->from_email;
        $context = [
            'client' => $client,
            'ria' => $ria,
            'company' => $companyInformation,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $adv = $companyInformation->getAdvDocument();
        if ($adv) {
            $extension = pathinfo($adv->getFilename(), PATHINFO_EXTENSION);
            $attachments = ['ADV Copy.'.$extension => $adv->getAbsolutePath()];
        } else {
            $attachments = [];
        };
        $this->sendMessage($template, $context, $fromEmail, $client->getEmail(), $attachments);
    }
    public function sendClientPortfolioIsSubmittedEmail(User $client)
    {
        $ria = $client->getRia();
        $template = $this->parameters['client_portfolio_is_submitted'];
        $fromEmail = $this->from_email;
        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $fromEmail, $client->getEmail());
    }
    public function sendClientRiaUploadedDocument(User $client)
    {
        $ria = $client->getRia();
        $template = $this->parameters['client_ria_uploaded_document'];
        $fromEmail = $this->from_email;
        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $fromEmail, $client->getEmail());
    }
    public function sendDocusignJointAccountOwnerEmail(AccountOwnerInterface $jointOwner, User $ria)
    {
        $template = $this->parameters['docusign_joint_account_owner'];
        $fromEmail = $this->from_email;
        $context = [
            'joint_owner' => $jointOwner,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];
        $this->sendMessage($template, $context, $fromEmail, $jointOwner->getEmail());
    }
    public function sendCustodianWorkflowDocuments(User $ria, Workflow $workflow)
    {
        $documents = [];
        if ($workflow->canHaveDocuments()) {
            /** @var DocumentSignature $signature */
            foreach ($workflow->getDocumentSignatures() as $signature) {
                $document = $signature->getDocument();
                $documents[$document->getOriginalName()] = $this->router->generate('rx_download_document', [
                    'filename' => $document->getFilename(),
                    'originalName' => $document->getOriginalName(),
                ], true);
            }
        }
        if (count($documents)) {
            $template = $this->parameters['docusign_custodian_workflow_documents'];
            $fromEmail = $this->from_email;
            $custodian = $ria->getCustodian();
            $context = [
                'custodian' => $custodian,
                'ria' => $ria,
                'logo' => $this->getRiaLogo($ria->getId()),
            ];
            return $this->sendMessage($template, $context, $fromEmail, $custodian->getEmail(), $documents);
        }
        return 0;
    }

    /**
     * @param $ria_id
     * @return string
     */
    private function getRiaLogo($ria_id)
    {
        $url = $this->router->generate('rx_file_download', [
            'ria_id' => $ria_id
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        if (file_exists($url)) {
            return $url;
        };
        return '/img/logo.png';
    }
}
