<?php

namespace Wealthbot\RiaBundle\Entity;

/**
 * AdvisorCode.
 */
class AdvisorCode
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $riaCompanyId;

    /**
     * @var int
     */
    private $custodianId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Wealthbot\RiaBundle\Entity\RiaCompanyInformation
     */
    private $riaCompany;

    /**
     * @var \Wealthbot\AdminBundle\Entity\Custodian
     */
    private $custodian;

    public function __toString()
    {
        return $this->getName();
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
     * Set riaCompanyId.
     *
     * @param int $riaCompanyId
     *
     * @return AdvisorCode
     */
    public function setRiaCompanyId($riaCompanyId)
    {
        $this->riaCompanyId = $riaCompanyId;

        return $this;
    }

    /**
     * Get riaCompanyId.
     *
     * @return int
     */
    public function getRiaCompanyId()
    {
        return $this->riaCompanyId;
    }

    /**
     * Set custodianId.
     *
     * @param int $custodianId
     *
     * @return AdvisorCode
     */
    public function setCustodianId($custodianId)
    {
        $this->custodianId = $custodianId;

        return $this;
    }

    /**
     * Get custodianId.
     *
     * @return int
     */
    public function getCustodianId()
    {
        return $this->custodianId;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return AdvisorCode
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
     * Set riaCompany.
     *
     * @param \Wealthbot\RiaBundle\Entity\RiaCompanyInformation $riaCompany
     *
     * @return AdvisorCode
     */
    public function setRiaCompany(\Wealthbot\RiaBundle\Entity\RiaCompanyInformation $riaCompany = null)
    {
        $this->riaCompany = $riaCompany;

        return $this;
    }

    /**
     * Get riaCompany.
     *
     * @return \Wealthbot\RiaBundle\Entity\RiaCompanyInformation
     */
    public function getRiaCompany()
    {
        return $this->riaCompany;
    }

    /**
     * Set custodian.
     *
     * @param \Wealthbot\AdminBundle\Entity\Custodian $custodian
     *
     * @return AdvisorCode
     */
    public function setCustodian(\Wealthbot\AdminBundle\Entity\Custodian $custodian = null)
    {
        $this->custodian = $custodian;

        return $this;
    }

    /**
     * Get custodian.
     *
     * @return \Wealthbot\AdminBundle\Entity\Custodian
     */
    public function getCustodian()
    {
        return $this->custodian;
    }
}
