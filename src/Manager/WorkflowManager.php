<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.07.13
 * Time: 16:31
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Entity\BankInformation;
use App\Entity\ClientPortfolio;
use App\Entity\Distribution;
use App\Entity\SystemAccount;
use App\Entity\Workflow;
use App\Model\BaseContribution;
use App\Model\ClientAccount;
use App\Model\PaymentWorkflowableInterface;
use App\Model\WorkflowableInterface;
use App\Entity\DocumentSignature;
use App\Manager\DocumentSignatureManager;
use App\Model\SignableInterface;
use App\Entity\Document;
use App\Entity\User;

class WorkflowManager
{
    /** @var \Doctrine\Common\Persistence\ObjectManager */
    private $om;

    /** @var string */
    private $class;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $repository;

    /** @var \App\Manager\DocumentSignatureManager */
    private $signatureManager;

    /** @var ClientAccountValuesManager */
    private $accountValuesManager;

    public function __construct(ObjectManager $om, $class, DocumentSignatureManager $signatureManager, ClientAccountValuesManager $accountValuesManager)
    {
        $this->om = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();

        $this->signatureManager = $signatureManager;
        $this->accountValuesManager = $accountValuesManager;
    }

    /**
     * Find workflow.
     *
     * @param int $id
     *
     * @return object
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Find workflow object by criteria.
     *
     * @param array $criteria
     *
     * @return Workflow
     */
    public function findOneBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @param User $client
     *
     * @return mixed
     */
    public function findNotCompletedInitRebalanceWorkflow(User $client)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->repository->createQueryBuilder('w');

        $qb->where('w.client = :client')
            ->andWhere('w.message_code = :message_code')
            ->andWhere('w.status != :status')
            ->setMaxResults(1)
            ->setParameters([
                'client' => $client,
                'message_code' => Workflow::MESSAGE_CODE_PAPERWORK_INITIAL_REBALANCE,
                'status' => Workflow::STATUS_COMPLETED,
            ]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Find workflow objects by criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return mixed
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find one workflow by client, object_type and type.
     *
     * @param User          $client
     * @param int           $type
     * @param object|string $objectType
     *
     * @return Workflow
     */
    public function findOneByClientAndTypeAndObjectType(User $client, $type, $objectType)
    {
        if (is_object($objectType)) {
            $objectTypeClass = $this->om->getClassMetadata(get_class($objectType))->getName();
        } else {
            $objectTypeClass = $objectType;
        }

        return $this->findOneBy(
            [
                'client' => $client,
                'type' => $type,
                'object_type' => $objectTypeClass,
            ]
        );
    }

    /**
     * Find one by client and object and type.
     *
     * @param User                  $client
     * @param WorkflowableInterface $object
     * @param $type
     *
     * @return Workflow|null
     */
    public function findOneByClientAndObjectAndType(User $client, WorkflowableInterface $object, $type)
    {
        $criteria = [
            'client' => $client,
            'type' => $type,
            'message_code' => $object->getWorkflowMessageCode(),
        ];

        if (method_exists($object, 'getId') && (null !== $object->getId())) {
            $criteria['object_id'] = $object->getId();
        }

        return $this->findOneBy($criteria);
    }

    /**
     * @param User                  $client
     * @param WorkflowableInterface $object
     *
     * @return Workflow|null
     */
    public function findOneByClientAndObject(User $client, WorkflowableInterface $object)
    {
        $messageCode = $object->getWorkflowMessageCode();

        if (array_key_exists($messageCode, Workflow::getPaperworkMessageChoices())) {
            $type = Workflow::TYPE_PAPERWORK;
        } else {
            $type = Workflow::TYPE_ALERT;
        }

        return $this->findOneByClientAndObjectAndType($client, $object, $type);
    }

    /**
     * Find account application workflow.
     *
     * @param ClientAccount $account
     *
     * @return Workflow|null
     */
    public function findAccountApplicationWorkflow(ClientAccount $account)
    {
        return $this->repository->findAccountApplicationWorkflow($account);
    }

    /**
     * Returns query for all workflow by id of ria user.
     *
     * @param int       $riaId
     * @param bool|null $isArchived
     *
     * @return \Doctrine\ORM\Query
     */
    public function findByRiaIdQuery($riaId, $isArchived = null)
    {
        return $this->repository->findByRiaIdQuery($riaId, $isArchived);
    }

    /**
     * Find all workflow by id of ria user.
     *
     * @param int       $riaId
     * @param bool|null $isArchived
     *
     * @return array
     */
    public function findByRiaId($riaId, $isArchived = null)
    {
        return $this->repository->findByRiaId($riaId, $isArchived);
    }

    /**
     * Find one workflow by id and id of ria user.
     *
     * @param int       $id
     * @param int       $riaId
     * @param bool|null $isArchived
     *
     * @return Workflow|null
     */
    public function findOneByIdAndRiaId($id, $riaId, $isArchived = null)
    {
        return $this->repository->findOneByIdAndRiaId($id, $riaId, $isArchived);
    }

    /**
     * Get workflowable object by workflow
     * Returns object that primary key is contained in object_id column.
     *
     * @param Workflow $workflow
     *
     * @return WorkflowableInterface|null
     */
    public function getObject(Workflow $workflow)
    {
        if (!$workflow->getObjectId()) {
            return;
        }

        $repository = $this->om->getRepository($workflow->getObjectType());

        return $repository->find($workflow->getObjectId());
    }

    /**
     * Get workflowable objects by workflow
     * Returns objects that primary keys are contained in object_ids column.
     *
     * @param Workflow $workflow
     *
     * @return WorkflowableInterface[]
     */
    public function getObjects(Workflow $workflow)
    {
        $result = [];

        $objectIds = $workflow->getObjectIds();
        if (is_array($objectIds) && !empty($objectIds)) {
            /** @var EntityRepository $repository */
            $repository = $this->om->getRepository($workflow->getObjectType());

            $qb = $repository->createQueryBuilder('wo');
            $qb->where($qb->expr()->in('wo.id', $objectIds));

            $result = $qb->getQuery()->getResult();
        }

        return $result;
    }

    /**
     * Get workflow activity.
     *
     * @param Workflow $workflow
     *
     * @return string
     */
    public function getActivity(Workflow $workflow)
    {
        $activity = $workflow->getMessage();
        if ($workflow->isAccountPaperwork() ||
            Workflow::MESSAGE_CODE_PAPERWORK_INITIAL_REBALANCE === $workflow->getMessageCode()
        ) {
            $object = $this->getObject($workflow);

            if ($object instanceof ClientAccount) {
                $activity .= ' - '.$object->getTypeName();
            } elseif ($object instanceof SystemAccount) {
                $activity .= ' - '.$object->getClientAccount()->getTypeName();
            }
        }

        return $activity;
    }

    /**
     * Update status of workflow.
     *
     * @param Workflow $workflow
     * @param $status
     *
     * @throws \InvalidArgumentException
     */
    public function updateStatus(Workflow $workflow, $status)
    {
        try {
            $workflow->setStatus($status);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value of status argument: %s', $status),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update status of workflow and save in db.
     *
     * @param Workflow $workflow
     * @param $status
     */
    public function updateStatusAndSave(Workflow $workflow, $status)
    {
        $this->updateStatus($workflow, $status);
        $this->save($workflow);
    }

    /**
     * Archive workflow.
     *
     * @param Workflow $workflow
     * @param bool     $archive
     */
    public function archive(Workflow $workflow, $archive = true)
    {
        $workflow->setIsArchived($archive);

        $this->om->persist($workflow);
        $this->om->flush();
    }

    /**
     * Archive workflow and save in db.
     *
     * @param Workflow $workflow
     * @param bool     $archive
     */
    public function archiveAndSave(Workflow $workflow, $archive = true)
    {
        $this->archive($workflow, $archive);
        $this->save($workflow);
    }

    /**
     * Update client status by system client account.
     *
     * @param Workflow      $workflow
     * @param SystemAccount $account
     */
    public function updateClientStatusBySystemAccount(Workflow $workflow, SystemAccount $account)
    {
        $value = $this->accountValuesManager->getTotalValue($account);

        if ($account->isActive()) {
            $workflow->setClientStatus(Workflow::CLIENT_STATUS_ACCOUNT_OPENED);
        } elseif ($workflow->isClientStatusAccountOpened() && $value > 1) {
            $workflow->setClientStatus(Workflow::CLIENT_STATUS_ACCOUNT_FUNDED);
        }
    }

    /**
     * Update client status by document signatures.
     *
     * @param Workflow $workflow
     */
    public function updateClientStatusByDocumentSignatures(Workflow $workflow)
    {
        if ($workflow->isDocumentSignaturesCreated()) {
            $workflow->setClientStatus(Workflow::CLIENT_STATUS_ENVELOPE_CREATED);
        } elseif ($workflow->isDocumentSignaturesCompleted()) {
            $workflow->setClientStatus(Workflow::CLIENT_STATUS_ENVELOPE_COMPLETED);
        } else {
            $workflow->setClientStatus(Workflow::CLIENT_STATUS_ENVELOPE_OPENED);
        }
    }

    /**
     * Update client status by client portfolio object.
     *
     * @param Workflow        $workflow
     * @param ClientPortfolio $clientPortfolio
     */
    public function updateClientStatusByClientPortfolio(Workflow $workflow, ClientPortfolio $clientPortfolio)
    {
        if ($clientPortfolio->isProposed()) {
            $workflow->setClientStatus(Workflow::CLIENT_STATUS_PORTFOLIO_PROPOSED);
        } elseif ($clientPortfolio->isClientAccepted()) {
            $workflow->setClientStatus(Workflow::CLIENT_STATUS_PORTFOLIO_CLIENT_ACCEPTED);
            $workflow->setIsArchived(true);
        }
    }

    /**
     * Delete workflow.
     *
     * @param Workflow $workflow
     */
    public function delete(Workflow $workflow)
    {
        $this->om->remove($workflow);
        $this->om->flush();
    }

    /**
     * Save workflow.
     *
     * @param Workflow $workflow
     */
    public function save(Workflow $workflow)
    {
        $this->om->persist($workflow);
        $this->om->flush();
    }

    /**
     * Get workflow documents to download.
     *
     * @param Workflow $workflow
     * @param bool     $filenameWithIndex
     *
     * @return array
     */
    public function getDocumentsToDownload(Workflow $workflow, $filenameWithIndex = true)
    {
        $signatures = $workflow->getDocumentSignatures();

        $documents = [];
        $applicationDocuments = [];

        /** @var DocumentSignature $signature */
        foreach ($signatures as $signature) {
            $applicationDocuments[$signature->getOrder()][] = $signature->getDocument();
        }

        $otherDocuments = $this->getAdditionalDocuments($workflow);

        if (!$filenameWithIndex) {
            foreach ($applicationDocuments as $orderedDocuments) {
                $documents = array_merge($documents, $orderedDocuments);
            }

            $documents = array_merge($documents, $otherDocuments);
        } else {
            $index = 1;
            foreach ($applicationDocuments as $orderedDocuments) {
                /** @var Document $document */
                foreach ($orderedDocuments as $document) {
                    $document->setOriginalName($index.'.'.$document->getOriginalName());
                    $documents[] = $document;
                    ++$index;
                }
            }

            /** @var Document $document */
            foreach ($otherDocuments as $document) {
                $document->setOriginalName($index.'.'.$document->getOriginalName());
                $documents[] = $document;
                ++$index;
            }
        }

        return $documents;
    }

    /**
     * Get additional workflow documents.
     *
     * @param Workflow $workflow
     *
     * @return array
     */
    public function getAdditionalDocuments(Workflow $workflow)
    {
        $documents = [];

        $code = $workflow->getMessageCode();
        switch ($code) {
            case Workflow::MESSAGE_CODE_PAPERWORK_UPDATE_DISTRIBUTIONS:
            case Workflow::MESSAGE_CODE_PAPERWORK_UPDATE_CONTRIBUTIONS:
                /** @var Distribution|BaseContribution $object */
                $object = $this->getObject($workflow);
                $bankInfo = $object->getBankInformation();
                break;
            case Workflow::MESSAGE_CODE_PAPERWORK_UPDATE_BANKING_INFORMATION:
                /* @var BankInformation $object */
                $bankInfo = $this->getObject($workflow);
                break;
            default:
                $bankInfo = null;
                break;
        }

        if ($bankInfo) {
            $bankPdfDocument = $bankInfo->getPdfDocument();
            if ($bankPdfDocument) {
                $documents[] = $bankPdfDocument;
            }
        }

        /** @var DocumentSignature $signature */
        foreach ($workflow->getDocumentSignatures() as $signature) {
            $signatureAdditionalDocuments = $this->signatureManager->getAdditionalDocuments($signature);
            $documents = array_merge($documents, $signatureAdditionalDocuments);
        }

        return $documents;
    }

    /**
     * Create new workflow.
     *
     * @param User                                  $client
     * @param WorkflowableInterface                 $object
     * @param int                                   $type
     * @param DocumentSignature|DocumentSignature[] $signatures
     * @param array                                 $objectIds
     *
     * @return Workflow|null
     *
     * @throws \InvalidArgumentException
     */
    public function createWorkflow(
        User $client,
        WorkflowableInterface $object,
        $type,
        $signatures = null,
        array $objectIds = null
    ) {
        $class = $this->om->getClassMetadata(get_class($object))->getName();

        $workflow = new Workflow();
        $workflow->setClient($client);
        $workflow->setType($type);
        $workflow->setObjectType($class);
        $workflow->setMessageCode($object->getWorkflowMessageCode());

        if (method_exists($object, 'getId') && (null !== $object->getId())) {
            $workflow->setObjectId($object->getId());
        }

        if (Workflow::TYPE_PAPERWORK === $type) {
            $documentSignatures = [];
            if ((null === $signatures) && ($object instanceof SignableInterface)) {
                $signature = $this->signatureManager->findDocumentSignatureBySource($object);
                if ($signature) {
                    $documentSignatures[] = $signature;
                }
            } else {
                if (is_object($signatures)) {
                    if (!($signatures instanceof DocumentSignature)) {
                        throw new \InvalidArgumentException('Parameter signatures must be array or instance of DocumentSignature.');
                    }

                    $documentSignatures[] = $signatures;
                } elseif (is_array($signatures)) {
                    $documentSignatures = $signatures;
                }
            }

            if (count($documentSignatures)) {
                foreach ($documentSignatures as $documentSignature) {
                    $workflow->addDocumentSignature($documentSignature);
                }

                $this->updateClientStatusByDocumentSignatures($workflow);
            }

            if ($object instanceof ClientPortfolio) {
                $this->updateClientStatusByClientPortfolio($workflow, $object);
            }
        } elseif ($object instanceof SystemAccount) {
            $this->updateClientStatusBySystemAccount($workflow, $object);
        }

        if (null !== $objectIds) {
            $workflow->setObjectIds($objectIds);
        }

        if ($object instanceof PaymentWorkflowableInterface) {
            $workflow->setAmount($object->getWorkflowAmount());
        }

        $this->om->persist($workflow);
        $this->om->flush();

        return $workflow;
    }
}
