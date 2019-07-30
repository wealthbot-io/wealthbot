<?php

namespace App\Entity;

/**
 * Class TransferCustodian
 * @package App\Entity
 */
class TransferCustodian
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

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
     * Set name.
     *
     * @param string $name
     *
     * @return TransferCustodian
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \App\Entity\TransferCustodianQuestion
     */
    private $transferCustodianQuestion;

    /**
     * Set transferCustodianQuestion.
     *
     * @param \App\Entity\TransferCustodianQuestion $transferCustodianQuestion
     *
     * @return TransferCustodian
     */
    public function setTransferCustodianQuestion(TransferCustodianQuestion $transferCustodianQuestion = null)
    {
        $this->transferCustodianQuestion = $transferCustodianQuestion;

        return $this;
    }

    /**
     * Get transferCustodianQuestion.
     *
     * @return \App\Entity\TransferCustodianQuestion
     */
    public function getTransferCustodianQuestion()
    {
        return $this->transferCustodianQuestion;
    }
}
