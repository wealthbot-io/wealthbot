<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;
use App\Entity\Workflow;
use App\Model\WorkflowableInterface;
use App\Entity\DocumentSignature;
use App\Entity\User;

class WorkflowEvent extends Event
{
    /** @var \App\Entity\User */
    private $client;

    /** @var \App\Model\WorkflowableInterface */
    private $object;

    /** @var int */
    private $type;

    /** @var DocumentSignature|DocumentSignature[] */
    private $signatures;

    /** @var array */
    private $objectIds;

    /** @var Workflow */
    private $data;

    public function __construct(User $client, WorkflowableInterface $object, $type, $signatures = null, array $objectIds = null)
    {
        $this->client = $client;
        $this->object = $object;
        $this->type = $type;
        $this->signatures = $signatures;
        $this->objectIds = $objectIds;
    }

    /**
     * Get client.
     *
     * @return User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get workflowable object.
     *
     * @return WorkflowableInterface
     */
    public function getObject()
    {
        return $this->object;
    }

    public function getType()
    {
        return $this->type;
    }


    /**
     * Get Signatures
     * @return DocumentSignature|DocumentSignature[]|null
     */
    public function getSignatures()
    {
        return $this->signatures;
    }

    public function getObjectIds()
    {
        return $this->objectIds;
    }

    /**
     * Set data.
     *
     * @param Workflow $data
     */
    public function setData(Workflow $data)
    {
        $this->data = $data;
    }

    /**
     * Get data.
     *
     * @return Workflow
     */
    public function getData()
    {
        return $this->data;
    }
}
