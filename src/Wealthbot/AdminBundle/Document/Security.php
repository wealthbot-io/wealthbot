<?php

namespace Wealthbot\AdminBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="securities")
 */
class Security
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String(name="symbol")
     */
    protected $symbol;

    /**
     * @MongoDB\String(name="security_type")
     */
    protected $securityType;

    /**
     * @MongoDB\String(name="description")
     */
    protected $description;

    /**
     * @MongoDB\String(name="expiration_date")
     */
    protected $expirationDate;

    /**
     * @MongoDB\String(name="call_date")
     */
    protected $callDate;

    /**
     * @MongoDB\Int(name="call_price")
     */
    protected $callPrice;

    /**
     * @MongoDB\String(name="issue_date")
     */
    protected $issueDate;
    /**
     * @MongoDB\String(name="first_coupon")
     */
    protected $firstCoupon;

    /**
     * @MongoDB\Int(name="interest_rate")
     */
    protected $interestRate;

    /**
     * @MongoDB\Int(name="share_per_contract")
     */
    protected $sharePerContract;

    /**
     * @MongoDB\Int(name="annual_income")
     */
    protected $annualIncome;

    /**
     * @MongoDB\String(name="comment")
     */
    protected $comment;

    /**
     * @MongoDB\String(name="created")
     */
    protected $created;

    /**
     * @MongoDB\String(name="import_date")
     */
    protected $importDate;

    /**
     * @MongoDB\String(name="source")
     */
    protected $source;

    /**
     * @param mixed $annualIncome
     */
    public function setAnnualIncome($annualIncome)
    {
        $this->annualIncome = $annualIncome;
    }

    /**
     * @return mixed
     */
    public function getAnnualIncome()
    {
        return $this->annualIncome;
    }

    /**
     * @param mixed $callDate
     */
    public function setCallDate($callDate)
    {
        $this->callDate = $callDate;
    }

    /**
     * @return mixed
     */
    public function getCallDate()
    {
        return $this->callDate;
    }

    /**
     * @param mixed $callPrice
     */
    public function setCallPrice($callPrice)
    {
        $this->callPrice = $callPrice;
    }

    /**
     * @return mixed
     */
    public function getCallPrice()
    {
        return $this->callPrice;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $expirationDate
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return mixed
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param mixed $firstCoupon
     */
    public function setFirstCoupon($firstCoupon)
    {
        $this->firstCoupon = $firstCoupon;
    }

    /**
     * @return mixed
     */
    public function getFirstCoupon()
    {
        return $this->firstCoupon;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $importDate
     */
    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
    }

    /**
     * @return mixed
     */
    public function getImportDate()
    {
        return $this->importDate;
    }

    /**
     * @param mixed $interestRate
     */
    public function setInterestRate($interestRate)
    {
        $this->interestRate = $interestRate;
    }

    /**
     * @return mixed
     */
    public function getInterestRate()
    {
        return $this->interestRate;
    }

    /**
     * @param mixed $issueDate
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;
    }

    /**
     * @return mixed
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * @param mixed $securityType
     */
    public function setSecurityType($securityType)
    {
        $this->securityType = $securityType;
    }

    /**
     * @return mixed
     */
    public function getSecurityType()
    {
        return $this->securityType;
    }

    /**
     * @param mixed $sharePerContract
     */
    public function setSharePerContract($sharePerContract)
    {
        $this->sharePerContract = $sharePerContract;
    }

    /**
     * @return mixed
     */
    public function getSharePerContract()
    {
        return $this->sharePerContract;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}
