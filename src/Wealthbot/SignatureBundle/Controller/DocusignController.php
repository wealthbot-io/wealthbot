<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 29.10.13
 * Time: 16:19.
 */

namespace Wealthbot\SignatureBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\ClientBundle\ClientEvents;
use Wealthbot\ClientBundle\Entity\ClientAdditionalContact;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Event\WorkflowEvent;
use Wealthbot\ClientBundle\Model\AccountOwnerInterface;
use Wealthbot\ClientBundle\Model\ClientAccount;
use Wealthbot\SignatureBundle\Adapter\AccountOwnerRecipientAdapter;
use Wealthbot\SignatureBundle\Entity\DocumentOwnerSignature;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\SignatureBundle\Model\Envelope;
use Wealthbot\SignatureBundle\Service\ElectronicSignatureService;
use Wealthbot\UserBundle\Entity\Document;

class DocusignController extends Controller
{
    /** @var \Wealthbot\SignatureBundle\Docusign\DocusignSessionPersistence */
    private $api;

    /** @var \Wealthbot\SignatureBundle\Manager\DocumentSignatureManager */
    private $signatureManager;

    /** @var  ElectronicSignatureService */
    private $electronicSignature;

    /** @var EntityManager */
    private $em;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->api = $this->get('wealthbot_docusign.api_client');
        $this->signatureManager = $this->get('wealthbot_docusign.document_signature.manager');
        $this->electronicSignature = $this->get('wealthbot_docusign.electronic_signature_service');
        $this->em = $this->get('doctrine.orm.entity_manager');
    }

    public function signAction($signature_id)
    {
        $signature = $this->signatureManager->findActiveDocumentSignature($signature_id);
        if (!$signature) {
            throw $this->createNotFoundException();
        }

        $source = $this->signatureManager->getSourceObject($signature);
        $account = $source->getClientAccount();
        $primaryApplicant = $account->getPrimaryApplicant();

        try {
            if (null === $signature->getDocusignEnvelopeId()) {
                $this->electronicSignature->sendEnvelopeForDraftSignature($signature);
            }

            if ($primaryApplicant->getObjectToSave() instanceof ClientAdditionalContact) {
                $existOwnerSignature = $this->signatureManager->findOneOwnerSignatureByDocumentSignatureIdAndContactId(
                    $signature->getId(),
                    $primaryApplicant->getId()
                );
            } else {
                $existOwnerSignature = $this->signatureManager->findOneOwnerSignatureByDocumentSignatureIdAndClientId(
                    $signature->getId(),
                    $primaryApplicant->getId()
                );
            }

            $error = $this->getAccountSigningErrorMessage($signature, $existOwnerSignature);
            if (null !== $error) {
                return $this->render('WealthbotSignatureBundle:Default:application_sign_error.html.twig', [
                    'message' => '<strong>Error:</strong> '.$error,
                ]);
            }

            $envelopeId = $signature->getDocusignEnvelopeId();

            if ($primaryApplicant instanceof AccountOwnerInterface) {
                $recipient = new AccountOwnerRecipientAdapter($primaryApplicant);
                $returnUrl = $this->generateUrl(
                    'wealthbot_docusign_application_sign_callback',
                    ['envelope_id' => $envelopeId],
                    true
                );

                $embeddedUrl = $this->api->getEmbeddedSigningUrl($envelopeId, $recipient, $returnUrl);
                if ($embeddedUrl) {
                    return $this->render(
                        'WealthbotSignatureBundle:Default:application_sign_iframe.html.twig',
                        ['url' => $embeddedUrl]
                    );
                }
            }
        } catch (\Exception $e) {
            return $this->render('WealthbotSignatureBundle:Default:application_sign_error.html.twig', [
                'message' => $e->getMessage(),
            ]);
        }

        return $this->render('WealthbotSignatureBundle:Default:application_sign_error.html.twig', [
            'message' => 'An error has occurred. Please try again later.',
        ]);
    }

    public function applicationSignAction(Request $request)
    {
        /** @var ClientAccount $account */
        $account = $this->em->getRepository('WealthbotClientBundle:ClientAccount')->find($request->get('account_id'));
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $signature = $this->signatureManager->findActiveDocumentSignatureBySourceIdAndType($account->getId(), DocumentSignature::TYPE_OPEN_OR_TRANSFER_ACCOUNT);
        if (!$signature) {
            throw $this->createNotFoundException();
        }

        if (null === $signature->getDocusignEnvelopeId()) {
            $this->electronicSignature->sendEnvelopeForApplication($account);
            $this->em->refresh($signature);
        }

        $envelopeId = $signature->getDocusignEnvelopeId();
        $primaryApplicant = $account->getPrimaryApplicant();

        if ($primaryApplicant instanceof AccountOwnerInterface) {
            $recipient = new AccountOwnerRecipientAdapter($primaryApplicant);
            $returnUrl = $this->generateUrl(
                'wealthbot_docusign_application_sign_callback',
                ['envelope_id' => $envelopeId, 'application_id' => $account->getId()],
                true
            );

            $embeddedUrl = $this->api->getEmbeddedSigningUrl($envelopeId, $recipient, $returnUrl);
            if ($embeddedUrl) {
                return $this->render(
                    'WealthbotSignatureBundle:Default:application_sign_iframe.html.twig',
                    ['url' => $embeddedUrl]
                );
            }
        }

        return $this->render('WealthbotSignatureBundle:Default:application_sign_error.html.twig', [
            'message' => 'An error has occurred. Please try again later.',
        ]);
    }

    public function applicationSignCallbackAction(Request $request)
    {
        $applicationId = $request->get('application_id');
        $envelopeId = $request->get('envelope_id');
        $signatures = $this->signatureManager->findDocumentSignaturesByEnvelopeId($envelopeId);
        if (!count($signatures)) {
            throw $this->createNotFoundException('Signature does not exist.');
        }

        $event = $request->get('event');
        switch ($event) {
            case 'exception':
                $error = 'An error has occurred. Please try again later.';
                break;
            case 'id_check_faild':
                $error = 'An error has occurred: recipient failed an ID check.';
                break;
            case 'session_timeout':
                $error = 'An error has occurred: session times out.';
                break;
            default:
                $error = null;
                break;
        }

        $isCompleted = false;
        $status = $this->electronicSignature->updateDocumentSignaturesStatusByEnvelopeId($envelopeId, $signatures);
        switch ($status) {
            case Envelope::STATUS_SIGNED:
            case Envelope::STATUS_COMPLETED:
                $message = 'You have successfully signed your document.<br/>Please close this tab and return to the original tab.';
                $isCompleted = true;
                $this->getDocusignDocumentByEnvelopeIdAndSignatures($envelopeId, $signatures);
                break;

            case Envelope::STATUS_DECLINED:
                $message = 'Signing declined.';
                break;

            case Envelope::STATUS_SENT:
            case Envelope::STATUS_PROCESSING:
                $message = 'Signing in process.';
                if ($applicationId) {
                    $account = $this->em->getRepository('WealthbotClientBundle:ClientAccount')->find($applicationId);
                    if ($account && $account->isJointType()) {
                        $primaryApplicant = $account->getPrimaryApplicant();
                        $secondaryApplicant = $account->getSecondaryApplicant();

                        if ($this->signatureManager->isOwnerSignApplication($primaryApplicant, $account) &&
                            !$this->signatureManager->isOwnerSignApplication($secondaryApplicant, $account)
                        ) {
                            $ria = $account->getClient()->getRia();

                            // Send email to secondary applicant
                            $mailer = $this->get('wealthbot.mailer');
                            $mailer->sendDocusignJointAccountOwnerEmail($secondaryApplicant, $ria);
                        }
                    }
                }
                break;

            case Envelope::STATUS_VOIDED:
                $message = null;
                $error = 'Envelope has been voted.';
                break;

            case Envelope::STATUS_DELETED:
                $message = null;
                $error = 'Envelope has been voted.';
                break;

            case Envelope::STATUS_TIMED_OUT:
                $message = null;
                $error = 'Timed out. Please try again later.';
                break;

            default:
                $message = 'Signing in process.';
                break;
        }

        if (null !== $error) {
            return $this->render(
                'WealthbotSignatureBundle:Default:application_sign_error.html.twig',
                ['message' => $error]
            );
        }

        $params = [
            'envelope_id' => $envelopeId,
            'application_id' => $applicationId ? $applicationId : '',
            'signature_id' => (count($signatures) === 1) ? $signatures[0]->getId() : '',
            'is_completed' => $isCompleted,
            'message' => $message,
        ];

        return $this->render('WealthbotSignatureBundle:Default:application_sign.html.twig', $params);
    }

    /**
     * Get envelope documents and save them for signatures.
     *
     * @param string              $envelopeId
     * @param DocumentSignature[] $signatures must be sorted in the order of creation
     *
     * @throws \RuntimeException
     */
    private function getDocusignDocumentByEnvelopeIdAndSignatures($envelopeId, array $signatures)
    {
        $documents = $this->api->getEnvelopeDocuments($envelopeId);

        if ($documents && count($documents->envelopeDocuments)) {
            foreach ($signatures as $index => $signature) {
                if ($signature->getDocument() && $signature->getDocument()->getFilename()) {
                    continue;
                }

                if (!isset($documents->envelopeDocuments[$index])) {
                    throw new \RuntimeException(sprintf(
                        'Document for signature with index: %s does not exist.',
                        $index
                    ));
                }

                $documentInfo = $documents->envelopeDocuments[$index];
                $documentBytes = $this->api->getEnvelopeDocument($envelopeId, $documentInfo->documentId);

                $source = $this->signatureManager->getSourceObject($signature);
                $clientAccount = $source->getClientAccount();
                $client = $clientAccount->getClient();

                $filename = $client->getFirstName().'_'.$client->getMiddleName().'_'.$client->getLastName()
                    .'_Account'.$clientAccount->getId().'_'.$documentInfo->name;
                $path = $this->container->getParameter('uploads_dir').'/tmp';

                if (!is_dir($path)) {
                    mkdir($path);
                }

                $path .= '/'.$filename;

                $fp = fopen($path, 'w+');
                fwrite($fp, $documentBytes);
                fclose($fp);

                $file = new File($path);

                $document = $signature->getDocument();
                $document->setOwner($client->getRia());
                $document->setFile($file);
                $document->setOriginalName($documentInfo->name);
                $document->setType(Document::TYPE_APPLICATION);
                $document->upload();

                if (!$client->getUserDocuments()->contains($document)) {
                    $client->addUserDocument($document);
                }

                $this->em->persist($document);
            }

            $this->em->persist($client);
            $this->em->flush();

            //$this->createWorkflow($signature, $document);
        }
    }

    private function getAccountSigningErrorMessage(DocumentSignature $signature, DocumentOwnerSignature $ownerSignature)
    {
        $status = $signature->getStatus();
        switch ($status) {
            case Envelope::STATUS_SIGNED:
                $error = 'Signing completed.';
                break;
            case Envelope::STATUS_COMPLETED:
                $error = 'Signing completed.';
                break;
            case Envelope::STATUS_DECLINED:
                $error = 'Signing declined.';
                break;
            case Envelope::STATUS_VOIDED:
                $error = 'Envelope has been voted.';
                break;
            case Envelope::STATUS_DELETED:
                $error = 'Envelope has been deleted.';
                break;
            case Envelope::STATUS_TIMED_OUT:
                $error = 'Timed out. Please try again later.';
                break;
            default:
                $error = null;
                break;
        }

        if (null !== $error) {
            return $error;
        }

        $ownerStatus = $ownerSignature->getStatus();
        switch ($ownerStatus) {
            case DocumentOwnerSignature::STATUS_SIGNED:
                $error = 'You already complete signing.';
                break;
            case DocumentOwnerSignature::STATUS_COMPLETED:
                $error = 'You already complete signing.';
                break;
            case DocumentOwnerSignature::STATUS_DECLINED:
                $error = 'Signing was declined.';
                break;
            default:
                $error = null;
                break;
        }

        return $error;
    }

    /**
     * Create workflow for document signature.
     *
     * @param DocumentSignature $signature
     * @param Document          $document
     */
    private function createWorkflow(DocumentSignature $signature, Document $document)
    {
        $account = $signature->getClientAccount();
        $client = $account->getClient();
        $type = $signature->getType();

        // TODO: workflow changes
        if (DocumentSignature::TYPE_OPEN_OR_TRANSFER_ACCOUNT === $type) {
            $event = new WorkflowEvent($client, $account, Workflow::TYPE_PAPERWORK, null);
            $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);
        }
    }

    private function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
