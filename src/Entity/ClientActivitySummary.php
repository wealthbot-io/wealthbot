<?php

namespace App\Entity;

use App\Model\ActivityInterface;
use App\Entity\User;

/**
 * Class ClientActivitySummary
 * @package App\Entity
 */
class ClientActivitySummary implements ActivityInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client_id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @param \App\Entity\User
     */
    private $client;

    /**
     * @var bool
     */
    private $is_show_ria;

    public function __construct()
    {
        $this->setIsShowRia(true);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set client_id.
     *
     * @param int $clientId
     *
     * @return ClientActivitySummary
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return ClientActivitySummary
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return ClientActivitySummary
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return ClientActivitySummary
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set is_show_ria.
     *
     * @param bool $isShowRia
     *
     * @return ClientActivitySummary
     */
    public function setIsShowRia($isShowRia)
    {
        $this->is_show_ria = $isShowRia;

        return $this;
    }

    /**
     * Get is_show_ria.
     *
     * @return bool
     */
    public function getIsShowRia()
    {
        return $this->is_show_ria;
    }

    /**
     * @var int
     */
    private $document_id;

    /**
     * @param \App\Entity\Document
     */
    private $Document;

    /**
     * Set document_id.
     *
     * @param int $documentId
     *
     * @return ClientActivitySummary
     */
    public function setDocumentId($documentId)
    {
        $this->document_id = $documentId;

        return $this;
    }

    /**
     * Get document_id.
     *
     * @return int
     */
    public function getDocumentId()
    {
        return $this->document_id;
    }

    /**
     * Set Document.
     *
     * @param \App\Entity\Document $document
     *
     * @return ClientActivitySummary
     */
    public function setDocument(Document $document = null)
    {
        $this->Document = $document;

        return $this;
    }

    /**
     * Get Document.
     *
     * @return \App\Entity\Document
     */
    public function getDocument()
    {
        return $this->Document;
    }

    /**
     * Get activity message.
     *
     * @return string
     */
    public function getActivityMessage()
    {
        return 'Document Uploaded';
    }

    /**
     * Get activity client.
     *
     * @return User
     */
    public function getActivityClient()
    {
        return $this->client;
    }
}
