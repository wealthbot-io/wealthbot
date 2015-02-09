<?php

namespace Wealthbot\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransferCustodianQuestionAnswer
 */
class TransferCustodianQuestionAnswer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $transfer_custodian_question_id;

    /**
     * @var integer
     */
    private $transfer_information_id;

    /**
     * @var boolean
     */
    private $value;

    /**
     * @var \Wealthbot\ClientBundle\Entity\TransferCustodianQuestion
     */
    private $question;

    /**
     * @var \Wealthbot\ClientBundle\Entity\TransferInformation
     */
    private $transferInformation;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set transfer_custodian_question_id
     *
     * @param integer $transferCustodianQuestionId
     * @return TransferCustodianQuestionAnswer
     */
    public function setTransferCustodianQuestionId($transferCustodianQuestionId)
    {
        $this->transfer_custodian_question_id = $transferCustodianQuestionId;
    
        return $this;
    }

    /**
     * Get transfer_custodian_question_id
     *
     * @return integer 
     */
    public function getTransferCustodianQuestionId()
    {
        return $this->transfer_custodian_question_id;
    }

    /**
     * Set transfer_information_id
     *
     * @param integer $transferInformationId
     * @return TransferCustodianQuestionAnswer
     */
    public function setTransferInformationId($transferInformationId)
    {
        $this->transfer_information_id = $transferInformationId;
    
        return $this;
    }

    /**
     * Get transfer_information_id
     *
     * @return integer 
     */
    public function getTransferInformationId()
    {
        return $this->transfer_information_id;
    }

    /**
     * Set value
     *
     * @param boolean $value
     * @return TransferCustodianQuestionAnswer
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return boolean 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set question
     *
     * @param \Wealthbot\ClientBundle\Entity\TransferCustodianQuestion $question
     * @return TransferCustodianQuestionAnswer
     */
    public function setQuestion(\Wealthbot\ClientBundle\Entity\TransferCustodianQuestion $question = null)
    {
        $this->question = $question;
    
        return $this;
    }

    /**
     * Get question
     *
     * @return \Wealthbot\ClientBundle\Entity\TransferCustodianQuestion 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set transferInformation
     *
     * @param \Wealthbot\ClientBundle\Entity\TransferInformation $transferInformation
     * @return TransferCustodianQuestionAnswer
     */
    public function setTransferInformation(\Wealthbot\ClientBundle\Entity\TransferInformation $transferInformation = null)
    {
        $this->transferInformation = $transferInformation;
    
        return $this;
    }

    /**
     * Get transferInformation
     *
     * @return \Wealthbot\ClientBundle\Entity\TransferInformation 
     */
    public function getTransferInformation()
    {
        return $this->transferInformation;
    }
}
