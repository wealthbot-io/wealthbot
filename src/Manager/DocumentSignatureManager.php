<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.10.13
 * Time: 16:29
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\AccountGroup;
use App\Entity\BankInformation;
use App\Entity\ClientAccountOwner;
use App\Entity\ClientAdditionalContact;
use App\Entity\TransferInformation;
use App\Model\AccountOwnerInterface;
use App\Model\ClientAccount;
use App\Entity\DocumentOwnerSignature;
use App\Entity\DocumentSignature;
use App\Model\Envelope;
use App\Model\SignableInterface;
use App\Entity\Document;
use App\Entity\User;

class DocumentSignatureManager
{
    /** @var \Doctrine\Common\Persistence\ObjectManager */
    private $om;

    /** @var string */
    private $accountSignatureClass;

    /** @var string */
    private $ownerSignatureClass;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $documentSignatureRepository;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $ownerSignatureRepository;

    public function __construct(ObjectManager $om, $accountSignatureClass, $accountOwnerSignatureClass)
    {
        $this->om = $om;
        $this->documentSignatureRepository = $om->getRepository($accountSignatureClass);
        $this->ownerSignatureRepository = $om->getRepository($accountOwnerSignatureClass);

        $accountSignatureMetadata = $om->getClassMetadata($accountSignatureClass);
        $this->accountSignatureClass = $accountSignatureMetadata->getName();

        $ownerSignatureMetadata = $om->getClassMetadata($accountOwnerSignatureClass);
        $this->ownerSignatureClass = $ownerSignatureMetadata->getName();
    }

    /**
     * Find document signature.
     *
     * @param int $id
     *
     * @return DocumentSignature
     */
    public function findDocumentSignature($id)
    {
        return $this->documentSignatureRepository->find($id);
    }

    /**
     * Find document signature object by criteria.
     *
     * @param array $criteria
     *
     * @return DocumentSignature
     */
    public function findOneDocumentSignatureBy(array $criteria)
    {
        return $this->documentSignatureRepository->findOneBy($criteria);
    }

    /**
     * Find active document signature.
     *
     * @param $id
     *
     * @return DocumentSignature
     */
    public function findActiveDocumentSignature($id)
    {
        return $this->findOneDocumentSignatureBy(['id' => $id, 'active' => true]);
    }

    /**
     * Find document signature objects by criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return mixed
     */
    public function findDocumentSignaturesBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->documentSignatureRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find document signature by source id and type.
     *
     * @param int    $sourceId
     * @param string $type
     * @param bool   $isActive
     *
     * @return DocumentSignature
     */
    public function findOneDocumentSignatureBySourceIdAndType($sourceId, $type, $isActive = true)
    {
        return $this->findOneDocumentSignatureBy(
            [
                'source_id' => $sourceId,
                'type' => $type,
                'active' => $isActive,
            ]
        );
    }

    /**
     * Find document signature by source.
     *
     * @param SignableInterface $source
     *
     * @return DocumentSignature
     */
    public function findDocumentSignatureBySource(SignableInterface $source)
    {
        return $this->findOneDocumentSignatureBySourceIdAndType(
            $source->getSourceObjectId(),
            $source->getDocumentSignatureType()
        );
    }

    /**
     * Find active document signature by source id and type.
     *
     * @param int    $accountId
     * @param string $type
     *
     * @return DocumentSignature
     */
    public function findActiveDocumentSignatureBySourceIdAndType($accountId, $type)
    {
        return $this->findOneDocumentSignatureBySourceIdAndType($accountId, $type, true);
    }

    /**
     * Find document signatures by docusign envelope id.
     * Order by id.
     *
     * @param string $envelopeId
     * @param bool   $isActive
     *
     * @return DocumentSignature[]
     */
    public function findDocumentSignaturesByEnvelopeId($envelopeId, $isActive = true)
    {
        return $this->documentSignatureRepository->findBy(
            ['docusign_envelope_id' => $envelopeId, 'active' => (bool) $isActive],
            ['id' => 'ASC']
        );
    }

    /**
     * Get signatures by client and types.
     *
     * @param User  $client
     * @param array $types
     *
     * @return array
     */
    public function findDocumentSignaturesByClientAndTypes(User $client, array $types = [])
    {
        return $this->documentSignatureRepository->getSignaturesByClientAndTypes($client, $types);
    }

    /**
     * Find signatures by client account consolidator id.
     * Ordered by id.
     *
     * @param int $consolidatorId
     *
     * @return DocumentSignature[]
     */
    public function findSignaturesByAccountConsolidatorId($consolidatorId)
    {
        return $this->documentSignatureRepository->findSignaturesByAccountConsolidatorId($consolidatorId);
    }

    /**
     * Find one change_beneficiary signature with status created by client account.
     *
     * @param ClientAccount $account
     *
     * @return DocumentSignature|null
     */
    public function findChangeBeneficiaryCreatedByClientAccount(ClientAccount $account)
    {
        return $this->documentSignatureRepository->findChangeBeneficiaryCreatedByClientAccountId($account->getId());
    }

    /**
     * Find change_beneficiary signatures by client account.
     *
     * @param ClientAccount $account
     *
     * @return DocumentSignature[]
     */
    public function findChangeBeneficiaryByClientAccount(ClientAccount $account)
    {
        return $this->documentSignatureRepository->findChangeBeneficiaryByClientAccountId($account->getId());
    }

    /**
     * Get application signatures.
     *
     * @param ClientAccount $account
     *
     * @return \App\Entity\DocumentSignature[]
     */
    public function getApplicationSignatures(ClientAccount $account)
    {
        return $this->findSignaturesByAccountConsolidatorId($account->getId());
    }

    /**
     * Is all client account in application signed.
     *
     * @param ClientAccount|int $account
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function isApplicationSigned($account)
    {
        if ($account instanceof ClientAccount) {
            $accountId = $account->getId();
        } elseif (is_numeric($account)) {
            $accountId = $account;
        } else {
            throw new \InvalidArgumentException('Argument must be integer or instance of ClientAccount.');
        }

        $signatures = $this->findSignaturesByAccountConsolidatorId($accountId);

        foreach ($signatures as $signature) {
            if (!$signature->isCompleted()) {
                return false;
            }
        }

        return true;

//        foreach (DocumentSignature::getTypeChoices() as $type) {
//            /** @var SignableObjectRepositoryInterface $repository */
//            $repository = $this->documentSignatureRepository->getRepositoryByType($type);
//
//            if (!$repository->isApplicationSigned($accountId)) {
//                return false;
//            }
//        }
//
//        return true;
    }

    /**
     * Is account owner sign all document signatures for application.
     *
     * @param AccountOwnerInterface $accountOwner
     * @param ClientAccount         $account
     *
     * @return bool
     */
    public function isOwnerSignApplication(AccountOwnerInterface $accountOwner, ClientAccount $account)
    {
        $signatures = $this->findSignaturesByAccountConsolidatorId($account->getId());

        foreach ($signatures as $signature) {
            if (!$this->isOwnerSignatureCompleted($accountOwner, $signature)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find document owner signature.
     *
     * @param int $id
     *
     * @return DocumentOwnerSignature
     */
    public function findOwnerSignature($id)
    {
        return $this->ownerSignatureRepository->find($id);
    }

    /**
     * Find document owner signature object by criteria.
     *
     * @param array $criteria
     *
     * @return DocumentOwnerSignature
     */
    public function findOneOwnerSignatureBy(array $criteria)
    {
        return $this->ownerSignatureRepository->findOneBy($criteria);
    }

    /**
     * Find document owner signature objects by criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return mixed
     */
    public function findOwnerSignaturesBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->ownerSignatureRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find document owner signature objects by account signature id.
     *
     * @param int $documentSignatureId
     *
     * @return mixed
     */
    public function findOwnerSignatureByDocumentSignatureId($documentSignatureId)
    {
        return $this->findOneOwnerSignatureBy(['document_signature_id' => $documentSignatureId]);
    }

    /**
     * Find document owner signature object by document signature id and client id.
     *
     * @param int $documentSignatureId
     * @param int $clientId
     *
     * @return DocumentOwnerSignature
     */
    public function findOneOwnerSignatureByDocumentSignatureIdAndClientId($documentSignatureId, $clientId)
    {
        return $this->findOneOwnerSignatureBy(
            ['document_signature_id' => $documentSignatureId, 'owner_client_user_id' => $clientId]
        );
    }

    /**
     * Find document owner signature object by document signature id and contact id.
     *
     * @param int $documentSignatureId
     * @param int $contactId
     *
     * @return DocumentOwnerSignature
     */
    public function findOneOwnerSignatureByDocumentSignatureIdAndContactId($documentSignatureId, $contactId)
    {
        return $this->findOneOwnerSignatureBy(
            ['document_signature_id' => $documentSignatureId, 'owner_contact_id' => $contactId]
        );
    }

    /**
     * Find document owner signature object by document signature id and contact id.
     *
     * @param int $documentSignatureId
     * @param int $contactId
     *
     * @return DocumentOwnerSignature
     */
    public function findOneOwnerSignatureByAccountSignatureIdAndContactId($documentSignatureId, $contactId)
    {
        return $this->findOneOwnerSignatureBy(
            ['document_signature_id' => $documentSignatureId, 'owner_contact_id' => $contactId]
        );
    }

    /**
     * Find document owner signature object by document signature id and owner email.
     *
     * @param int    $documentSignatureId
     * @param string $email
     *
     * @return DocumentOwnerSignature
     */
    public function findOneOwnerSignatureByDocumentSignatureIdAndOwnerEmail($documentSignatureId, $email)
    {
        return $this->ownerSignatureRepository->findOneByDocumentSignatureIdAndOwnerEmail($documentSignatureId, $email);
    }

    /**
     * Create new document signature.
     *
     * @param int         $sourceId
     * @param string      $type
     * @param string|null $envelopeId
     * @param string      $status
     *
     * @return DocumentSignature
     */
    public function createDocumentSignature($sourceId, $type, $envelopeId = null, $status = Envelope::STATUS_CREATED)
    {
        $document = new Document();
        $document->setType(Document::TYPE_APPLICATION);

        $signature = new DocumentSignature();
        $signature->setSourceId($sourceId);
        $signature->setDocument($document);
        $signature->setDocusignEnvelopeId($envelopeId);
        $signature->setType($type);
        $signature->setStatus($status);
        $signature->setActive(true);

        return $signature;
    }

    /**
     * Change document signature status.
     * Returns true if status has been changed and false otherwise.
     *
     * @param DocumentSignature $signature
     * @param int               $status
     *
     * @return bool
     */
    public function changeDocumentSignatureStatus(DocumentSignature $signature, $status)
    {
        if ($status !== $signature->getStatus()) {
            $signature->setStatus($status);

            return true;
        }

        return false;
    }

    /**
     * Change document signature status
     * and save if status has been changed.
     *
     * @param DocumentSignature $signature
     * @param int               $status
     */
    public function updateDocumentSignatureIfStatusChanged(DocumentSignature $signature, $status)
    {
        $isChanged = $this->changeDocumentSignatureStatus($signature, $status);
        if ($isChanged) {
            $this->saveDocumentSignature($signature);
        }
    }

    /**
     * Save document signature.
     * If signature status is not equal to TYPE_ONE_TIME_CONTRIBUTION or TYPE_ONE_TIME_DISTRIBUTION
     * Then set active flag to false for all previous signatures with same source_id and type.
     *
     * @param DocumentSignature $signature
     */
    public function saveDocumentSignature(DocumentSignature $signature)
    {
        if ($signature->isActive()) {
            $type = $signature->getType();

            if (DocumentSignature::TYPE_ONE_TIME_CONTRIBUTION !== $type &&
                DocumentSignature::TYPE_ONE_TIME_DISTRIBUTION !== $type
            ) {
                $this->resetDocumentSignaturesActiveFlag($signature, false);
            }
        }

        $this->persist($signature);
        $this->flush();
    }

    /**
     * Create new account owner signature object.
     *
     * @param DocumentSignature     $documentSignature
     * @param AccountOwnerInterface $owner
     * @param string                $status
     *
     * @return DocumentOwnerSignature
     */
    public function createOwnerSignature(DocumentSignature $documentSignature, AccountOwnerInterface $owner, $status = DocumentOwnerSignature::STATUS_CREATED)
    {
        $signature = new DocumentOwnerSignature();
        $signature->setDocumentSignature($documentSignature);
        $signature->setStatus($status);

        $ownerObject = $owner->getObjectToSave();
        if ($ownerObject instanceof User && $ownerObject->hasRole('ROLE_CLIENT')) {
            $signature->setClientOwner($ownerObject);
        } elseif ($ownerObject instanceof ClientAdditionalContact) {
            $signature->setContactOwner($ownerObject);
        }

        return $signature;
    }

    /**
     * Create and save signature for client account and envelope_id.
     *
     * @param SignableInterface $object
     * @param string|null       $envelopeId
     *
     * @return DocumentSignature
     */
    public function createSignature(SignableInterface $object, $envelopeId = null)
    {
        $account = $object->getClientAccount();
        $sourceId = $object->getSourceObjectId();
        $type = $object->getDocumentSignatureType();

        $signature = $this->createDocumentSignature($sourceId, $type, $envelopeId);
        if ($account->getClient()) {
            $signature->getDocument()->setOwner($account->getClient()->getRia());
        }

        $primaryApplicant = $account->getPrimaryApplicant();
        $pOwnerSig = $this->createOwnerSignature($signature, $primaryApplicant);
        $signature->addOwnerSignature($pOwnerSig);

        if ($account->isJointType() && $account->getSecondaryApplicant()) {
            $secondaryApplicant = $account->getSecondaryApplicant();
            $sOwnerSig = $this->createOwnerSignature($signature, $secondaryApplicant);
            $signature->addOwnerSignature($sOwnerSig);
        }

        $this->saveDocumentSignature($signature);

        return $signature;
    }

    /**
     * Create signatures for bank information updating action.
     * Returns array of signatures for client who own bank information.
     *
     * @param BankInformation $bankInformation
     *
     * @return DocumentSignature[]
     */
    public function createBankInformationSignature(BankInformation $bankInformation)
    {
        $contributionRepo = $this->om->getRepository('App\Entity\AccountContribution');
        $distributionRepo = $this->om->getRepository('App\Entity\Distribution');

        $contributions = $contributionRepo->findBy(['bankInformation' => $bankInformation]);
        $distributions = $distributionRepo->findBy(['bankInformation' => $bankInformation]);

        $signatures = [];

        foreach ($contributions as $contribution) {
            $signature = $this->createSignature($contribution);
            if ($bankInformation->getClient() === $contribution->getClientAccount()->getClient()) {
                $signatures[] = $signature;
            }
        }

        foreach ($distributions as $distribution) {
            $signature = $this->createSignature($distribution);
            if ($bankInformation->getClient() === $distribution->getClientAccount()->getClient()) {
                $signatures[] = $signature;
            }
        }

        return $signatures;
    }

    /**
     * Reset document signatures active flag to false
     * for all previous signatures with same source_id and type.
     *
     * @param DocumentSignature $signature
     * @param bool              $flush
     */
    public function resetDocumentSignaturesActiveFlag(DocumentSignature $signature, $flush = true)
    {
        $signatures = $this->findDocumentSignaturesBy(
            ['source_id' => $signature->getSourceId(), 'type' => $signature->getType()]
        );
        foreach ($signatures as $item) {
            if ($item !== $signature) {
                $item->setActive(false);
                $this->persist($item);
            }
        }

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Is document signature completed.
     *
     * @param DocumentSignature|int $signature
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function isSignatureCompleted($signature)
    {
        if ($signature instanceof DocumentSignature) {
            $documentSignature = $signature;
        } elseif (is_numeric($signature)) {
            $documentSignature = $this->documentSignatureRepository->find($signature);
        } else {
            throw new \InvalidArgumentException('Argument must be integer or instance of DocumentSignature.');
        }

        return $documentSignature ? $documentSignature->isCompleted() : false;
    }

    /**
     * Is client account owner has complete signature.
     *
     * @param AccountOwnerInterface $accountOwner
     * @param DocumentSignature     $signature
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    public function isOwnerSignatureCompleted(AccountOwnerInterface $accountOwner, DocumentSignature $signature)
    {
        $criteria = ['documentSignature' => $signature];

        $owner = $accountOwner->getObjectToSave();
        if (ClientAccountOwner::OWNER_TYPE_SELF === $accountOwner->getType()) {
            $criteria['clientOwner'] = $owner;
        } else {
            $criteria['contactOwner'] = $owner;
        }

        $ownerSignature = $this->ownerSignatureRepository->findOneBy($criteria);
        if (!$ownerSignature) {
            throw new \RuntimeException('Owner document signature does not exist.');
        }

        return $ownerSignature->isCompleted();
    }

    public function isDocumentSignatureForObjectExist(SignableInterface $object)
    {
        $signature = $this->findOneDocumentSignatureBySourceIdAndType(
            $object->getSourceObjectId(),
            $object->getDocumentSignatureType()
        );

        return $signature ? true : false;
    }

    /**
     * Get signable object of signature.
     *
     * @param DocumentSignature $signature
     *
     * @return SignableInterface
     */
    public function getSourceObject(DocumentSignature $signature)
    {
        $repository = $this->documentSignatureRepository->getRepositoryByType($signature->getType());

        return $repository->find($signature->getSourceId());
    }

    /**
     * Get additional signature documents.
     *
     * @param DocumentSignature $signature
     *
     * @return Document[]
     */
    public function getAdditionalDocuments(DocumentSignature $signature)
    {
        $documents = [];
        if (DocumentSignature::TYPE_TRANSFER_INFORMATION === $signature->getType()) {
            /** @var TransferInformation $transferInformation */
            $transferInformation = $this->getSourceObject($signature);

            $statementDocument = $transferInformation->getStatementDocument();
            if ($statementDocument) {
                $documents[] = $statementDocument;
            }
        }

        return $documents;
    }

    /**
     * Persist object to object manager.
     *
     * @param object $object
     */
    public function persist($object)
    {
        $this->om->persist($object);
    }

    /**
     * Flushes all changes.
     */
    public function flush()
    {
        $this->om->flush();
    }

    /**
     * Get document signature activity.
     *
     * @param DocumentSignature $signature
     *
     * @return string
     */
    public function getActivity(DocumentSignature $signature)
    {
        $type = $signature->getType();
        $source = $this->getSourceObject($signature);

        $account = $source->getClientAccount();
        $accountGroup = $account->getGroupName();

        switch ($type) {
            case DocumentSignature::TYPE_OPEN_OR_TRANSFER_ACCOUNT:
                if (AccountGroup::GROUP_DEPOSIT_MONEY === $accountGroup) {
                    $activity = 'Open an account';
                } elseif (AccountGroup::GROUP_FINANCIAL_INSTITUTION === $accountGroup) {
                    $activity = 'Transfer an account';
                } elseif (AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT === $accountGroup) {
                    $activity = 'Rollover a 401(k)';
                } else {
                    $activity = 'Undefined';
                }
                break;
            case DocumentSignature::TYPE_TRANSFER_INFORMATION:
                $activity = 'Transfer Account';
                break;
            case DocumentSignature::TYPE_AUTO_INVEST_CONTRIBUTION:
                $activity = 'Auto-invest instructions';
                break;
            case DocumentSignature::TYPE_AUTO_DISTRIBUTION:
                $activity = 'Auto-distribution instructions';
                break;
            case DocumentSignature::TYPE_ONE_TIME_CONTRIBUTION:
                $activity = 'One Time Contribution';
                break;
            case DocumentSignature::TYPE_ONE_TIME_DISTRIBUTION:
                $activity = 'One Time Distribution';
                break;
            case DocumentSignature::TYPE_CHANGE_BENEFICIARY:
                $activity = 'Change Beneficiary';
                break;
            default:
                $activity = 'Undefined';
                break;
        }

        return $activity;
    }
}
