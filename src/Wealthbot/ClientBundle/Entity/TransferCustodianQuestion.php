<?php

namespace Wealthbot\ClientBundle\Entity;

/**
 * TransferCustodianQuestion.
 */
class TransferCustodianQuestion
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $transfer_custodian_id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var bool
     */
    private $docusign_eligible_answer;

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
     * Set transfer_custodian_id.
     *
     * @param int $transferCustodianId
     *
     * @return TransferCustodianQuestion
     */
    public function setTransferCustodianId($transferCustodianId)
    {
        $this->transfer_custodian_id = $transferCustodianId;

        return $this;
    }

    /**
     * Get transfer_custodian_id.
     *
     * @return int
     */
    public function getTransferCustodianId()
    {
        return $this->transfer_custodian_id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return TransferCustodianQuestion
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * @var \Wealthbot\ClientBundle\Entity\TransferCustodian
     */
    private $transferCustodian;

    /**
     * Set transferCustodian.
     *
     * @param \Wealthbot\ClientBundle\Entity\TransferCustodian $transferCustodian
     *
     * @return TransferCustodianQuestion
     */
    public function setTransferCustodian(\Wealthbot\ClientBundle\Entity\TransferCustodian $transferCustodian = null)
    {
        $this->transferCustodian = $transferCustodian;

        return $this;
    }

    /**
     * Get transferCustodian.
     *
     * @return \Wealthbot\ClientBundle\Entity\TransferCustodian
     */
    public function getTransferCustodian()
    {
        return $this->transferCustodian;
    }

    /**
     * Set docusign_eligible_answer.
     *
     * @param bool $docusignEligibleAnswer
     *
     * @return TransferCustodianQuestion
     */
    public function setDocusignEligibleAnswer($docusignEligibleAnswer)
    {
        $this->docusign_eligible_answer = $docusignEligibleAnswer;

        return $this;
    }

    /**
     * Get docusign_eligible_answer.
     *
     * @return bool
     */
    public function getDocusignEligibleAnswer()
    {
        return $this->docusign_eligible_answer;
    }
}
