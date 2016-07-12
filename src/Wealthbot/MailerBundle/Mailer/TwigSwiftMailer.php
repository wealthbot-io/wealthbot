<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\MailerBundle\Mailer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wealthbot\AdminBundle\Entity\Custodian;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Model\AccountOwnerInterface;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\UserBundle\Entity\Document;
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
    protected $em;

    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        EntityManager $em,
        array $parameters
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->em = $em;
        $this->parameters = $parameters;
    }

    //-----------------ADMIN EMAILS-----------------------

    public function sendAdminsRiaActivatedEmail(User $ria)
    {
        $template = $this->parameters['template']['admin_ria_activated'];

        $context = [
            'ria_name' => $ria->getRiaCompanyInformation()->getName(),
        ];

        $repository = $this->em->getRepository('WealthbotUserBundle:User');
        $admins = $repository->getAllAdmins();

        $toEmails = [];
        /** @var User $admin */
        foreach ($admins as $admin) {
            $toEmails[] = $admin->getEmail();
        }

        $this->sendMessage($template, $this->parameters['from_email']['admin_ria_activated'], $toEmails, $context);
    }

    //-----------------RIA EMAILS-------------------------

    public function sendRiaNotFinishedRegistrationEmail(User $ria)
    {
        $template = $this->parameters['template']['ria_not_finished_registration'];

        $context = [
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];

        $this->sendMessage($template, $this->parameters['from_email']['ria_not_finished_registration'], $ria->getEmail(), $context);
    }

    public function sendRiaChangePasswordEmail(User $ria)
    {
        $template = $this->parameters['template']['ria_change_password'];

        $context = [
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];

        $this->sendMessage($template, $this->parameters['from_email']['ria_change_password'], $ria->getEmail(), $context);
    }

    public function sendRiaUserResetPasswordEmail(User $ria, User $riaUser, $newPassword)
    {
        $template = $this->parameters['template']['ria_user_reset_password'];

        $context = [
            'ria_name' => $ria->getFullName(),
            'new_password' => $newPassword,
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];

        $this->sendMessage($template, $this->parameters['from_email']['ria_user_reset_password'], $riaUser->getEmail(), $context);
    }

    public function sendRiaUserCreateEmail(User $riaUser, $password)
    {
        $template = $this->parameters['template']['ria_user_create'];

        $context = [
            'username' => $riaUser->getUsername(),
            'password' => $password,
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];

        $this->sendMessage($template, $this->parameters['from_email']['ria_user_create'], $riaUser->getEmail(), $context);
    }

    public function sendRiaClientSuggestedPortfolioEmail(User $client)
    {
        $riaAlertsConfiguration = $client->getRia()->getAlertsConfiguration();

        if ($riaAlertsConfiguration && !$riaAlertsConfiguration->getIsClientPortfolioSuggestion()) {
            return;
        }

        $template = $this->parameters['template']['ria_client_suggested_portfolio'];

        $context = [
            'client' => $client,
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];

        $this->sendRiaEmails($client->getRia(), $template, $this->parameters['from_email']['ria_client_suggested_portfolio'], $context);
    }

    public function sendRiaClientClosedAccountsEmail(User $client, $accounts)
    {
        $riaAlertsConfiguration = $client->getRia()->getAlertsConfiguration();

        if ($riaAlertsConfiguration && !$riaAlertsConfiguration->getIsClientDrivenAccountClosures()) {
            return;
        }

        $template = $this->parameters['template']['ria_client_closed_accounts'];

        $context = [
            'client' => $client,
            'accounts' => $accounts,
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];

        $this->sendRiaEmails($client->getRia(), $template, $this->parameters['from_email']['ria_client_closed_accounts'], $context);
    }

    public function sendRiaActivatedEmail(User $ria)
    {
        $template = $this->parameters['template']['ria_activated'];

        $context = [
            'logo' => $this->router->getContext()->getHost().'/img/logo.png',
        ];

        $this->sendRiaEmails($ria, $template, $this->parameters['from_email']['ria_activated'], $context);
    }

    private function sendRiaEmails(User $ria, $template, $fromEmail, $context)
    {
        $this->sendMessage($template, $fromEmail, $ria->getEmail(), $context);

        $riaUsers = $this->em->getRepository('WealthbotUserBundle:User')->getUsersByRiaId($ria->getId());

        foreach ($riaUsers as $riaUser) {
            $this->sendMessage($template, $fromEmail, $riaUser->getEmail(), $context);
        }
    }

    //-----------------CLIENT EMAILS-------------------------
    //$context include required keys for layout: logo, ria

    public function sendClientNotFinishedRegistrationEmail(User $client)
    {
        $template = $this->parameters['template']['client_not_finished_registration'];

        $ria = $client->getRia();

        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_not_finished_registration'], $client->getEmail(), $context);
    }

    public function sendClientNotApprovedPortfolioEmail(User $client)
    {
        $template = $this->parameters['template']['client_not_approved_portfolio'];

        $ria = $client->getRia();

        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_not_approved_portfolio'], $client->getEmail(), $context);
    }

    public function sendClientNotCompleteAllApplicationsEmail(User $client)
    {
        $template = $this->parameters['template']['client_not_completed_all_applications'];

        $ria = $client->getRia();

        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_not_completed_all_applications'], $client->getEmail(), $context);
    }

    public function sendClientRolloverInstruction401Email(ClientAccount $account, $rolloverMessage)
    {
        $template = $this->parameters['template']['client_rollover_instruction_401'];

        $client = $account->getClient();
        $ria = $client->getRia();

        $context = [
            'account' => $account,
            'ria' => $ria,
            'rollover_message' => $rolloverMessage,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_rollover_instruction_401'], $client->getEmail(), $context);
    }

    public function sendClientUserCreateEmail(User $clientUser, $password)
    {
        $template = $this->parameters['template']['client_user_create'];

        $ria = $clientUser->getMasterClient()->getRia();

        $context = [
            'new_client' => $clientUser,
            'new_password' => $password,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_user_create'], $clientUser->getEmail(), $context);
    }

    public function sendClientResetSelfPasswordEmail(User $client, $newPassword)
    {
        $template = $this->parameters['template']['client_reset_self_password'];

        $ria = $client->getRia();

        $context = [
            'new_password' => $newPassword,
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_reset_self_password'], $client->getEmail(), $context);
    }

    public function sendClientResetPasswordEmail(User $client)
    {
        $template = $this->parameters['template']['client_reset_password'];

        $ria = $client->getRia();

        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_reset_password'], $client->getEmail(), $context);
    }

    public function sendClientUpdatedDocumentsEmail(User $client, $documentType)
    {
        $template = $this->parameters['template']['client_updated_documents'];

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

        $this->sendMessage($template, $this->parameters['from_email']['client_updated_documents'], $client->getEmail(), $context);
    }

    public function sendClientInviteProspectEmail(User $ria, $clientEmail, $group = null)
    {
        $template = $this->parameters['template']['client_invite_prospect'];

        $context = [
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
            'group' => $group,
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_invite_prospect'], $clientEmail, $context);
    }

    public function sendClientInviteInternalProspectEmail(User $ria, User $client, $tmpPassword)
    {
        $template = $this->parameters['template']['client_invite_internal_prospect'];

        $context = [
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
            'new_password' => $tmpPassword,
            'client' => $client,
        ];

        $this->sendMessage($template, $this->parameters['from_email']['client_invite_internal_prospect'], $client->getEmail(), $context);
    }

    public function sendClientAdvCopyEmailMessage(User $client)
    {
        $ria = $client->getProfile()->getRia();
        $companyInformation = $ria->getRiaCompanyInformation();

        $template = $this->parameters['template']['client_adv_copy'];
        $fromEmail = $this->parameters['from_email']['client_adv_copy'];
        $context = [
            'client' => $client,
            'ria' => $ria,
            'company' => $companyInformation,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $adv = $companyInformation->getAdvDocument();

        if($adv){
            $extension = pathinfo($adv->getFilename(), PATHINFO_EXTENSION);
            $attachments = ['ADV Copy.'.$extension => $adv->getAbsolutePath()];
        } else {
            $attachments = [];
        };

        $this->sendMessage($template, $fromEmail, $client->getEmail(), $context, $attachments);
    }

    public function sendClientPortfolioIsSubmittedEmail(User $client)
    {
        $ria = $client->getRia();

        $template = $this->parameters['template']['client_portfolio_is_submitted'];
        $fromEmail = $this->parameters['from_email']['client_portfolio_is_submitted'];

        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $fromEmail, $client->getEmail(), $context);
    }

    public function sendClientRiaUploadedDocument(User $client)
    {
        $ria = $client->getRia();

        $template = $this->parameters['template']['client_ria_uploaded_document'];
        $fromEmail = $this->parameters['from_email']['client_ria_uploaded_document'];

        $context = [
            'client' => $client,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $fromEmail, $client->getEmail(), $context);
    }

    public function sendDocusignJointAccountOwnerEmail(AccountOwnerInterface $jointOwner, User $ria)
    {
        $template = $this->parameters['template']['docusign_joint_account_owner'];
        $fromEmail = $this->parameters['from_email']['docusign_joint_account_owner'];

        $context = [
            'joint_owner' => $jointOwner,
            'ria' => $ria,
            'logo' => $this->getRiaLogo($ria->getId()),
        ];

        $this->sendMessage($template, $fromEmail, $jointOwner->getEmail(), $context);
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
            $template = $this->parameters['template']['docusign_custodian_workflow_documents'];
            $fromEmail = $this->parameters['from_email']['docusign_custodian_workflow_documents'];
            $custodian = $ria->getCustodian();

            $context = [
                'custodian' => $custodian,
                'ria' => $ria,
                'logo' => $this->getRiaLogo($ria->getId()),
            ];

            return $this->sendMessage($template, $fromEmail, $custodian->getEmail(), $context, $documents);
        }

        return 0;
    }

    /**
     * Send email message.
     *
     * @param string       $templateName
     * @param array|string $fromEmails
     * @param array|string $toEmails
     * @param array        $context
     * @param array        $attachments
     *
     * @return int
     */
    private function sendMessage($templateName, $fromEmails, $toEmails, $context = [], $attachments = [])
    {
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmails)
            ->setTo($toEmails);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        if (is_array($attachments) && !empty($attachments)) {
            foreach ($attachments as $filename => $path) {
                
                if(file_exists($filename)){
                    if ($this->fileExists($path)) {
                        $attachment = \Swift_Attachment::fromPath($path);
                        if (is_string($filename)) {
                            $attachment->setFilename($filename);
                        }
                        $message->attach($attachment);
                    }
                }
                
            }
        }

        return $this->mailer->send($message);
    }
    
    private function fileExists($path)
    {
        if (file_exists($path) || ($fp = curl_init($path ) !== false)) {
            return true;
        }
        return false;
    }

    private function getRiaLogo($riaId)
    {
        return $this->router->generate('rx_file_download', ['ria_id' => $riaId], true);
    }
}
