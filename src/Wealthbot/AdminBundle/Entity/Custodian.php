<?php

namespace Wealthbot\AdminBundle\Entity;

/**
 * Custodian.
 */
class Custodian
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
     * @var string
     */
    private $email;

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
     * @return Custodian
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
     * Set email.
     *
     * @param string $email
     *
     * @return Custodian
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $custodianDocuments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->custodianDocuments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add custodianDocuments.
     *
     * @param \Wealthbot\UserBundle\Entity\Document $custodianDocuments
     *
     * @return Custodian
     */
    public function addCustodianDocument(\Wealthbot\UserBundle\Entity\Document $custodianDocuments)
    {
        $this->custodianDocuments[] = $custodianDocuments;

        return $this;
    }

    /**
     * Remove custodianDocuments.
     *
     * @param \Wealthbot\UserBundle\Entity\Document $custodianDocuments
     */
    public function removeCustodianDocument(\Wealthbot\UserBundle\Entity\Document $custodianDocuments)
    {
        $this->custodianDocuments->removeElement($custodianDocuments);
    }

    /**
     * Get custodianDocuments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustodianDocuments()
    {
        return $this->custodianDocuments;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $advisorCodes;

    /**
     * Add advisorCodes.
     *
     * @param \Wealthbot\RiaBundle\Entity\AdvisorCode $advisorCodes
     *
     * @return Custodian
     */
    public function addAdvisorCode(\Wealthbot\RiaBundle\Entity\AdvisorCode $advisorCodes)
    {
        $this->advisorCodes[] = $advisorCodes;

        return $this;
    }

    /**
     * Remove advisorCodes.
     *
     * @param \Wealthbot\RiaBundle\Entity\AdvisorCode $advisorCodes
     */
    public function removeAdvisorCode(\Wealthbot\RiaBundle\Entity\AdvisorCode $advisorCodes)
    {
        $this->advisorCodes->removeElement($advisorCodes);
    }

    /**
     * Get advisorCodes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdvisorCodes()
    {
        return $this->advisorCodes;
    }
}
