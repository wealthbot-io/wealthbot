<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use App\Model\Workflow as BaseWorkflow;
use App\Entity\DocumentSignature;
use Doctrine\Common\Collections\Collection;

/**
 * Class Workflow
 * @package App\Entity
 */
class Workflow extends BaseWorkflow
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var int
     */
    protected $object_id;

    /**
     * @var array,
     */
    protected $object_ids;

    /**
     * @var string
     */
    protected $object_type;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $message_code;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $client_status;

    /**
     * @var bool
     */
    protected $is_archived;

    /**
     * @var \DateTime
     */
    protected $submitted;

    /**
     * @param \App\Entity\User
     */
    protected $client;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $documentSignatures;

    /**
     * @var string
     */
    protected $note;

    /**
     * @var string
     */
    protected $amount;

    public function __construct()
    {
        parent::__construct();

        $this->documentSignatures = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getId();
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return Workflow
     */
    public function setType($type)
    {
        parent::setType($type);

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return parent::getType();
    }

    /**
     * Set object_id.
     *
     * @param int $objectId
     *
     * @return Workflow
     */
    public function setObjectId($objectId)
    {
        parent::setObjectId($objectId);

        return $this;
    }

    /**
     * Get object_id.
     *
     * @return int
     */
    public function getObjectId()
    {
        return parent::getObjectId();
    }

    /**
     * Set object_ids.
     *
     * @param array $objectIds
     *
     * @return Workflow
     */
    public function setObjectIds(array $objectIds)
    {
        parent::setObjectIds($objectIds);

        return $this;
    }

    /**
     * Get object_ids.
     *
     * @return array
     */
    public function getObjectIds()
    {
        return parent::getObjectIds();
    }

    /**
     * Set object_type.
     *
     * @param string $objectType
     *
     * @return Workflow
     */
    public function setObjectType($objectType)
    {
        parent::setObjectType($objectType);

        return $this;
    }

    /**
     * Get object_type.
     *
     * @return string
     */
    public function getObjectType()
    {
        return parent::getObjectType();
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return Workflow
     */
    public function setMessage($message)
    {
        parent::setMessage($message);

        return $this;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return parent::getMessage();
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Workflow
     */
    public function setStatus($status)
    {
        parent::setStatus($status);

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return parent::getStatus();
    }

    /**
     * Set client status.
     *
     * @param int $clientStatus
     *
     * @return Workflow
     */
    public function setClientStatus($clientStatus)
    {
        parent::setClientStatus($clientStatus);

        return $this;
    }

    /**
     * Get client status.
     *
     * @return int
     */
    public function getClientStatus()
    {
        return parent::getClientStatus();
    }

    /**
     * Set is_archived.
     *
     * @param bool $isArchived
     *
     * @return Workflow
     */
    public function setIsArchived($isArchived)
    {
        parent::setIsArchived($isArchived);

        return $this;
    }

    /**
     * Get is_archived.
     *
     * @return bool
     */
    public function getIsArchived()
    {
        return parent::getIsArchived();
    }

    /**
     * Set submitted.
     *
     * @param \DateTime $submitted
     *
     * @return Workflow
     */
    public function setSubmitted($submitted)
    {
        parent::setSubmitted($submitted);

        return $this;
    }

    /**
     * Get submitted.
     *
     * @return \DateTime
     */
    public function getSubmitted()
    {
        return parent::getSubmitted();
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return Workflow
     */
    public function setClient(User $client = null)
    {
        parent::setClient($client);

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return parent::getClient();
    }

    /**
     * Set note.
     *
     * @param string $note
     *
     * @return Workflow
     */
    public function setNote($note)
    {
        parent::setNote($note);

        return $this;
    }

    /**
     * Get note.
     *
     * @return string
     */
    public function getNote()
    {
        return parent::getNote();
    }

    /**
     * Set message_code.
     *
     * @param string $messageCode
     *
     * @return Workflow
     */
    public function setMessageCode($messageCode)
    {
        parent::setMessageCode($messageCode);

        return $this;
    }

    /**
     * Get message_code.
     *
     * @return string
     */
    public function getMessageCode()
    {
        return parent::getMessageCode();
    }

    /**
     * Add documentSignatures.
     *
     * @param \App\Entity\DocumentSignature $documentSignatures
     *
     * @return Workflow
     */
    public function addDocumentSignature(DocumentSignature $documentSignatures)
    {
        $this->documentSignatures[] = $documentSignatures;

        return $this;
    }

    /**
     * Remove documentSignatures.
     *
     * @param \App\Entity\DocumentSignature $documentSignatures
     */
    public function removeDocumentSignature(DocumentSignature $documentSignatures)
    {
        $this->documentSignatures->removeElement($documentSignatures);
    }

    /**
     * Get documentSignatures.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocumentSignatures()
    {
        return $this->documentSignatures;
    }

    /**
     * Get count of documentSignatures.
     *
     * @return int
     */
    public function getDocumentSignaturesCount()
    {
        return $this->documentSignatures->count();
    }

    /**
     * Get filename of the first document signature.
     *
     * @return string|null
     */
    public function getFirstDocumentSignatureFilename()
    {
        if ($this->getDocumentSignaturesCount() > 0) {
            /** @var DocumentSignature $signature */
            $signature = $this->documentSignatures->first();

            return $signature->getDocument()->getFilename();
        }

        return;
    }

    /**
     * Is all document signatures are created.
     *
     * @return bool
     */
    public function isDocumentSignaturesCreated()
    {
        if (!$this->documentSignatures->count()) {
            return false;
        }

        foreach ($this->getDocumentSignatures() as $signature) {
            if (!$signature->isCreated()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Is all document signatures are completed.
     *
     * @return bool
     */
    public function isDocumentSignaturesCompleted()
    {
        if (!$this->documentSignatures->count()) {
            return false;
        }

        foreach ($this->getDocumentSignatures() as $signature) {
            if (!$signature->isCompleted()) {
                return false;
            }
        }

        return true;
    }

//    /**
//     * Update client status by document signatures
//     */
//    public function updateClientStatusByDocumentSignatures()
//    {
//        if ($this->isDocumentSignaturesCreated()) {
//            $this->setClientStatus(self::CLIENT_STATUS_ENVELOPE_CREATED);
//        } elseif ($this->isDocumentSignaturesCompleted()) {
//            $this->setClientStatus(self::CLIENT_STATUS_ENVELOPE_COMPLETED);
//        } else {
//            $this->setClientStatus(self::CLIENT_STATUS_ENVELOPE_OPENED);
//        }
//    }
//
//    /**
//     * Update client status by client portfolio object
//     *
//     * @param ClientPortfolio $clientPortfolio
//     */
//    public function updateClientStatusByClientPortfolio(ClientPortfolio $clientPortfolio)
//    {
//        if ($clientPortfolio->isProposed()) {
//            $this->setClientStatus(self::CLIENT_STATUS_PORTFOLIO_PROPOSED);
//        } elseif ($clientPortfolio->isClientAccepted()) {
//            $this->setClientStatus(self::CLIENT_STATUS_PORTFOLIO_CLIENT_ACCEPTED);
//            $this->setIsArchived(true);
//        }
//    }

    /**
     * Get all documents of document signatures.
     *
     * @return array
     */
    public function getSignaturesDocuments()
    {
        $documents = [];

        /** @var DocumentSignature $signature */
        foreach ($this->getDocumentSignatures() as $signature) {
            $documents[] = $signature->getDocument();
        }

        return $documents;
    }

    /**
     * Set amount.
     *
     * @param string $amount
     *
     * @return Workflow
     */
    public function setAmount($amount)
    {
        parent::setAmount($amount);

        return $this;
    }

    /**
     * Get amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return parent::getAmount();
    }
}
