<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.10.13
 * Time: 18:39
 * To change this template use File | Settings | File Templates.
 */

namespace App\Service;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use App\Entity\Distribution;
use App\Entity\SystemAccount;
use App\Model\ClientAccount;
use App\Adapter\AccountOwnerRecipientAdapter;
use App\Docusign\AbstractDocusign;
use App\Entity\DocumentSignature;
use App\Manager\AccountDocusignManager;
use App\Manager\DocumentSignatureManager;
use App\Model\AccountTabsConfigurationFactory;
use App\Model\Envelope;
use App\Model\RecipientInterface;
use App\Model\SignableInterface;

class ElectronicSignatureService
{
    /** @var \App\Docusign\AbstractDocusign */
    private $docusignApi;

    /** @var \App\Manager\DocumentSignatureManager */
    private $signatureManager;

    /** @var \App\Manager\AccountDocusignManager */
    private $accountDocusignManager;

    /** @var array */
    private $templates;

    public function __construct(
        AbstractDocusign $docusignApi,
        DocumentSignatureManager $signatureManager,
        AccountDocusignManager $accountDocusignManager,
        array $templates
    ) {
        $this->docusignApi = $docusignApi;
        $this->signatureManager = $signatureManager;
        $this->accountDocusignManager = $accountDocusignManager;
        $this->templates = $templates;
    }

    /**
     * Send envelope from template for signable object.
     *
     * @param SignableInterface $object
     *
     * @return DocumentSignature
     *
     * @throws \RuntimeException
     */
    public function sendEnvelopeForSignableObject(SignableInterface $object)
    {
        $response = $this->sendEnvelope($object);
        $signature = $this->signatureManager->createSignature($object, $response->envelopeId);

        return $signature;
    }

    /**
     * Send envelope for draft signature and update signature.
     *
     * @param DocumentSignature $signature
     *
     * @return DocumentSignature
     */
    public function sendEnvelopeForDraftSignature(DocumentSignature $signature)
    {
        $object = $this->signatureManager->getSourceObject($signature);
        $response = $this->sendEnvelope($object);

        $signature->setDocusignEnvelopeId($response->envelopeId);
        $signature->setStatus($response->status);

        $this->signatureManager->saveDocumentSignature($signature);

        return $signature;
    }

    /**
     * Send envelope for all signatures for application.
     *
     * @param ClientAccount $account
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    public function sendEnvelopeForApplication(ClientAccount $account)
    {
        $signatures = $this->signatureManager->findSignaturesByAccountConsolidatorId($account->getId());

        $usedTemplates = [];
        $compositeTemplates = [];

        foreach ($signatures as $signature) {
            $source = $this->signatureManager->getSourceObject($signature);
            $templateId = $this->getTemplateForSignableObject($source);

            if (in_array($templateId, $usedTemplates)) {
                continue;
            }

            $usedTemplates[] = $templateId;
            $recipients = $this->getRecipients($source);
            $recipientParams = [];

            foreach ($recipients as $key => $recipient) {
                $recipientParams[] = [
                    'email' => $recipient->getEmail(),
                    'name' => $recipient->getName(),
                    'clientUserId' => $recipient->getClientUserId(),
                    'recipientId' => $key + 1,
                    'roleName' => $recipient->getRoleName(),
                    'type' => $recipient->getType(),
                    'tabs' => $recipient->getTabs()->toArray(),
                ];
            }

            $serverTemplates = [['sequence' => 1, 'templateId' => $templateId]];
            $inlineTemplates = [
                [
                    'sequence' => 2,
                    'recipients' => ['signers' => $recipientParams],
                ],
            ];

            $compositeTemplates[] = ['serverTemplates' => $serverTemplates, 'inlineTemplates' => $inlineTemplates];
        }

        if (count($compositeTemplates)) {
            $options = [
                'compositeTemplates' => $compositeTemplates,
                'enableWetSign' => false, // Disable Sign on Paper button
            ];

            $envelope = new Envelope();
            $envelope->setEmailBlurb('Sign all documents');
            $envelope->setEmailSubject('Sign all documents');
            //$envelope->setRecipients($recipients);
            $envelope->setStatus(Envelope::STATUS_SENT);

            try {
                $response = $this->docusignApi->sendEnvelope($envelope, $options);
            } catch (\Exception $e) {
                throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

            foreach ($signatures as $signature) {
                $signature->setDocusignEnvelopeId($response->envelopeId);
                $signature->setStatus($response->status);

                $this->signatureManager->saveDocumentSignature($signature);
            }

            return true;
        }

        return false;
    }

    /**
     * Update document and document owner signatures.
     *
     * @param string              $envelopeId
     * @param DocumentSignature[] $signatures
     *
     * @return DocumentSignature
     */
    public function updateDocumentSignaturesStatusByEnvelopeId($envelopeId, array $signatures)
    {
        $status = $this->docusignApi->getEnvelopeStatus($envelopeId);
        $recipientsStatuses = $this->docusignApi->getEnvelopeRecipientsStatuses($envelopeId);

        foreach ($signatures as $signature) {
            $signatureEnvelopeId = $signature->getDocusignEnvelopeId();
            if (!$signatureEnvelopeId || $signatureEnvelopeId !== $envelopeId) {
                continue;
            }

            $signature->setStatus($status);
            foreach ($recipientsStatuses as $recipientEmail => $recipientStatus) {
                $ownerSignature = $this->signatureManager->findOneOwnerSignatureByDocumentSignatureIdAndOwnerEmail(
                    $signature->getId(),
                    $recipientEmail
                );

                if ($ownerSignature) {
                    $ownerSignature->setStatus($recipientStatus);
                    $this->signatureManager->persist($ownerSignature);
                }
            }

            $this->signatureManager->persist($signature);
        }

        $this->signatureManager->flush();

        return $status;
    }

    /**
     * Update account and account owners signatures.
     *
     * @param ClientAccount $account
     * @param string        $type
     *
     * @return \App\Entity\DocumentSignature
     */
    public function updateAccountSignatureStatusByAccountAndType(ClientAccount $account, $type)
    {
        $signature = $this->signatureManager->findActiveDocumentSignatureBySourceIdAndType(
            $account->getId(),
            $type
        );
        if ($signature) {
            $this->updateDocumentSignaturesStatusByEnvelopeId($signature->getDocusignEnvelopeId(), [$signature]);
        }

        return $signature;
    }

    /**
     * Send envelope.
     *
     * @param SignableInterface $object
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    private function sendEnvelope(SignableInterface $object)
    {
        $recipients = $this->getRecipients($object);

        $envelope = new Envelope();
        $envelope->setEmailSubject('TestS');
        $envelope->setEmailBlurb('TestB');
        $envelope->setRecipients($recipients);
        $envelope->setStatus(Envelope::STATUS_SENT);

        try {
            $templateId = $this->getTemplateForSignableObject($object);
            $options = [
                'enableWetSign' => false, // Disable Sign on Paper button
            ];

            $response = $this->docusignApi->sendEnvelopeFromTemplate($envelope, $templateId, $options);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * Get recipients.
     *
     * @param SignableInterface $object
     *
     * @return RecipientInterface[]
     */
    private function getRecipients(SignableInterface $object)
    {
        $tabsConfigurationFactory = new AccountTabsConfigurationFactory($object);
        $tabsConfiguration = $tabsConfigurationFactory->create();
        $tabs = $tabsConfiguration->generate();

        $account = $object->getClientAccount();
        $recipients = [];

        $primaryOwner = $account->getPrimaryApplicant();
        if ($primaryOwner) {
            $primaryRecipient = new AccountOwnerRecipientAdapter($primaryOwner);
            $primaryRecipient->setTabs($tabs);

            $recipients[] = $primaryRecipient;
        }

        if ($account->isJointType()) {
            $secondaryOwner = $account->getSecondaryApplicant();
            if ($secondaryOwner) {
                $secondaryRecipient = new AccountOwnerRecipientAdapter($secondaryOwner, false);
                $secondaryRecipient->setTabs($tabs);

                $recipients[] = $secondaryRecipient;
            }
        }

        return $recipients;
    }

    /**
     * Get docusign template for signable object.
     *
     * @param SignableInterface $object
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    private function getTemplateForSignableObject(SignableInterface $object)
    {
        $type = $object->getDocumentSignatureType();
        $accountType = $object->getClientAccount()->getSystemType();

        switch ($type) {
            case DocumentSignature::TYPE_OPEN_OR_TRANSFER_ACCOUNT:
                if (SystemAccount::TYPE_PERSONAL_INVESTMENT === $accountType ||
                    SystemAccount::TYPE_JOINT_INVESTMENT === $accountType
                ) {
                    $key = 'personal_account';
                } elseif (SystemAccount::TYPE_ROTH_IRA === $accountType ||
                    SystemAccount::TYPE_TRADITIONAL_IRA === $accountType
                ) {
                    $key = 'ira_account';
                } else {
                    throw new \InvalidArgumentException(sprintf('Invalid account type: %s', $accountType));
                }

                break;

            case DocumentSignature::TYPE_TRANSFER_INFORMATION:
                $key = 'account_transfer_form';
                break;

            case DocumentSignature::TYPE_AUTO_INVEST_CONTRIBUTION:
            case DocumentSignature::TYPE_ONE_TIME_CONTRIBUTION:
                $key = 'electronic_funds_transfer_form';
                break;

            case DocumentSignature::TYPE_CHANGE_BENEFICIARY:
                $key = 'beneficiary_designation_form';
                break;

            case DocumentSignature::TYPE_AUTO_DISTRIBUTION:
                if (SystemAccount::TYPE_PERSONAL_INVESTMENT === $accountType ||
                    SystemAccount::TYPE_JOINT_INVESTMENT === $accountType
                ) {
                    $key = 'electronic_funds_transfer_form';
                } elseif (SystemAccount::TYPE_ROTH_IRA === $accountType ||
                    SystemAccount::TYPE_TRADITIONAL_IRA === $accountType
                ) {
                    $key = 'ira_distribution_form';
                } else {
                    throw new \InvalidArgumentException(sprintf('Invalid account type: %s', $accountType));
                }

                break;

            case DocumentSignature::TYPE_ONE_TIME_DISTRIBUTION:
                if (SystemAccount::TYPE_PERSONAL_INVESTMENT === $accountType ||
                    SystemAccount::TYPE_JOINT_INVESTMENT === $accountType
                ) {
                    if (!($object instanceof Distribution) || !$object->isOneTime()) {
                        throw new \InvalidArgumentException(sprintf('Object must be one-time distribution.'));
                    }

                    if (Distribution::TRANSFER_METHOD_RECEIVE_CHECK === $object->getTransferMethod()) {
                        $key = 'check_request';
                    } elseif (Distribution::TRANSFER_METHOD_WIRE_TRANSFER === $object->getTransferMethod()) {
                        $key = 'wire_instructions';
                    } else {
                        $key = 'electronic_funds_transfer_form';
                    }
                } elseif (SystemAccount::TYPE_ROTH_IRA === $accountType ||
                    SystemAccount::TYPE_TRADITIONAL_IRA === $accountType
                ) {
                    $key = 'ira_distribution_form';
                } else {
                    throw new \InvalidArgumentException(sprintf('Invalid account type: %s', $accountType));
                }

                break;

            default:
                throw new \InvalidArgumentException(sprintf('Invalid document signature type: %s', $type));
                break;
        }

        if (!$this->hasTemplate($key)) {
            throw new InvalidConfigurationException('Template with key: %s does not exist. Check configuration.', $key);
        }

        return $this->getTemplate($key);
    }

    /**
     * Get template wit key $key.
     *
     * @param string $key
     *
     * @return string
     */
    private function getTemplate($key)
    {
        return $this->templates[$key];
    }

    /**
     * Return true if template with key $key is exist
     * and false otherwise.
     *
     * @param string $key
     *
     * @return bool
     */
    private function hasTemplate($key)
    {
        return isset($this->templates[$key]);
    }
}
