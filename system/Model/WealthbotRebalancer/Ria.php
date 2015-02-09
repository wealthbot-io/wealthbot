<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Ria extends Base {

    const REBALANCED_FREQUENCY_QUARTERLY = 1;
    const REBALANCED_FREQUENCY_SEMI_ANNUALLY = 2;
    const REBALANCED_FREQUENCY_ANNUALLY = 3;
    const REBALANCED_FREQUENCY_TOLERANCE_BANDS = 4;

    private $email;

    private $isActive;

    /** @var boolean */
    private $isTlhEnabled;

    /** @var float */
    private $minTlh;

    /** @var float */
    private $minTlhPercent;

    /** @var float */
    private $minRelationshipValue;

    private $rebalancingFrequency;

    /** @var float */
    private $clientTaxBracket;

    /** @var bool */
    private $isUseMunicipalBond;

    /** @var bool */
    private $useTransactionFees;

    /** @var float */
    private $transactionMinAmount;

    /** @var float */
    private $buySellMins;

    /**
     * @param mixed $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param RiaCompanyInformation $riaCompanyInformation
     * @return $this
     */
    public function setRiaCompanyInformation(RiaCompanyInformation $riaCompanyInformation)
    {
        $this->riaCompanyInformation = $riaCompanyInformation;

        return $this;
    }

    /**
     * @return RiaCompanyInformation
     */
    public function getRiaCompanyInformation()
    {
        return $this->riaCompanyInformation;
    }

    /**
     * @param int $rebalancingFrequency
     * @return $this
     */
    public function setRebalancingFrequency($rebalancingFrequency)
    {
        $this->rebalancingFrequency = $rebalancingFrequency;

        return $this;
    }

    /**
     * @return int
     */
    public function getRebalancingFrequency()
    {
        return $this->rebalancingFrequency;
    }

    /**
     * @param boolean $isTlhEnabled
     */
    public function setIsTlhEnabled($isTlhEnabled)
    {
        $this->isTlhEnabled = $isTlhEnabled;
    }

    /**
     * @return boolean
     */
    public function getIsTlhEnabled()
    {
        return $this->isTlhEnabled;
    }

    /**
     * @param float $clientTaxBracket
     * @return $this
     */
    public function setClientTaxBracket($clientTaxBracket)
    {
        $this->clientTaxBracket = $clientTaxBracket;

        return $this;
    }

    /**
     * @return float
     */
    public function getClientTaxBracket()
    {
        return $this->clientTaxBracket;
    }

    /**
     * @param float $minTlh
     */
    public function setMinTlh($minTlh)
    {
        $this->minTlh = $minTlh;
    }

    /**
     * @return float
     */
    public function getMinTlh()
    {
        return $this->minTlh;
    }

    /**
     * @param float $minTlhPercent
     */
    public function setMinTlhPercent($minTlhPercent)
    {
        $this->minTlhPercent = $minTlhPercent;
    }

    /**
     * @return float
     */
    public function getMinTlhPercent()
    {
        return $this->minTlhPercent;
    }

    /**
     * @param float $minRelationshipValue
     * @return $this
     */
    public function setMinRelationshipValue($minRelationshipValue)
    {
        $this->minRelationshipValue = $minRelationshipValue;

        return $this;
    }

    /**
     * @return float
     */
    public function getMinRelationshipValue()
    {
        return $this->minRelationshipValue;
    }

    /**
     * @param bool $isUseMunicipalBond
     * @return $this
     */
    public function setIsUseMunicipalBond($isUseMunicipalBond)
    {
        $this->isUseMunicipalBond = (bool) $isUseMunicipalBond;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsUseMunicipalBond()
    {
        return $this->isUseMunicipalBond;
    }

    /**
     * @param bool $useTransactionFees
     * @return $this
     */
    public function setUseTransactionFees($useTransactionFees)
    {
        $this->useTransactionFees = (bool) $useTransactionFees;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseTransactionFees()
    {
        return $this->useTransactionFees;
    }

    /**
     * @return float
     */
    public function setTransactionMinAmount($transactionMinAmount)
    {
        $this->transactionMinAmount = $transactionMinAmount;
    }

    /**
     * @return float
     */
    public function getTransactionMinAmount()
    {
        return $this->transactionMinAmount;
    }

    public function getRelations()
    {
        return array(
            'ria_company_information' => 'Model\WealthbotRebalancer\RiaCompanyInformation'
        );
    }
//    public function loadFromArray(array $data = array())
//    {
//        foreach ($data as $key => $value) {
//            if ($key === 'clients') {
//                $clients = new ClientCollection();
//                foreach ($value as $clientData) {
//                    $class = 'Model\WealthbotRebalancer\Client';
//
//                    $client = new $class;
//                    $client->loadFromArray($clientData);
//
//                    $clients->add($client, $client->getId());
//                }
//
//                $this->setClients($clients);
//            } else {
//                $this->$key = $value;
//            }
//        }
//    }


}