<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RiaCompanyInformation extends Base
{

    /** @var Ria */
    private $ria;

    /** @var  bool */
    private $useTransactionFees;

    /** 6.A. RIA min tx amount figure */
    private $transactionMinAmount;

    /** 6.A. does advisor have buy/sell min for some Securities */
    private $buySellMins;


    /**
     * @param Ria $ria
     * @return $this
     */
    public function setRia(Ria $ria)
    {
        $this->ria = $ria;

        return $this;
    }

    /**
     * @return Ria
     */
    public function getRia()
    {
        return $this->ria;
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
     * @param int $transactionMinAmount
     */
    public function setTransactionMinAmount($transactionMinAmount)
    {
        $this->transactionMinAmount = $transactionMinAmount;
    }

    /**
     * @return boolean
     */
    public function getTransactionMinAmount()
    {
        return $this->transactionMinAmount;
    }

    /**
     * @param int $transactionMinAmountPercent
     */
    public function setTransactionMinAmountPercent($transactionMinAmountPercent)
    {
        $this->transactionMinAmountPercent = $transactionMinAmountPercent;
    }

    /**
     * @return boolean
     */
    public function getTransactionMinAmountPercent()
    {
        return $this->transactionMinAmountPercent;
    }

    /**
    * @param int $buySellMins
    */
    public function setBuySellMins($buySellMins)
    {
        $this->buySellMins = $buySellMins;
    }

    /**
     * @return boolean
     */
    public function getBuySellMins()
    {
        return $this->buySellMins;
    }

    public function getRelations()
    {
        return array(
            'ria' => 'Model\WealthbotRebalancer\Ria'
        );
    }
}