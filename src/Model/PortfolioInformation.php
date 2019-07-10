<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.05.13
 * Time: 14:27
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Entity\ModelAssumption;
use App\Model\CeModelEntityInterface;
use App\Model\CeModelInterface;
use App\Entity\User;

class PortfolioInformation
{
    /** @var User */
    private $user;

    /** @var CeModelInterface */
    private $model;

    /** @var ModelAssumption */
    private $assumption;

    /** @var array */
    private $fees;

    /** @var float */
    private $fundExpenses;

    /** @var float */
    private $investmentMarket;

    /** @var array */
    private $modelEntities;

    /** @var bool */
    private $isQualified;

    private $modelEntitiesJson;

    /** @var array() */
    private $transactionCommissionFee;

    /** @var bool */
    private $isShowPerformanceSection;

    public function __construct()
    {
        $this->assumption = null;
        $this->fees = [];
        $this->fundExpenses = null;
        $this->investmentMarket = null;
        $this->modelEntities = ['qualified' => [], 'non_qualified' => []];
        $this->isQualified = false;
        $this->modelEntitiesJson = [];
        $this->transactionCommissionFee = [];
        $this->isShowPerformanceSection = false;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get client.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set model.
     *
     * @param CeModelInterface $model
     *
     * @return $this
     */
    public function setModel(CeModelInterface $model)
    {
        $this->model = $model;
        $this->model->buildGroupModelEntities();

        return $this;
    }

    /**
     * Get model.
     *
     * @return CeModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set qualified flag.
     *
     * @param bool $isQualified
     *
     * @return $this
     */
    public function setIsQualifiedModel($isQualified)
    {
        $this->isQualified = $isQualified;
        $this->fundExpenses = null;
        $this->investmentMarket = null;

        return $this;
    }

    /**
     * Get qualified flag.
     *
     * @return bool
     */
    public function getIsQualifiedModel()
    {
        return $this->isQualified;
    }

    /**
     * Get model entities.
     *
     * @return array
     */
    public function getModelEntities()
    {
        if ($this->isQualified) {
            return $this->getQualifiedModelEntities();
        }

        return $this->getNonQualifiedModelEntities();
    }

    /**
     * Get Qualified model entities.
     *
     * @return array
     */
    public function getQualifiedModelEntities()
    {
        return $this->model->getQualifiedModelEntities();
    }

    /**
     * Get Non Qualified model entities.
     *
     * @return array
     */
    public function getNonQualifiedModelEntities()
    {
        return $this->model->getNonQualifiedModelEntities();
    }

    /**
     * Get model entities information as json.
     *
     * @return string
     */
    public function getModelEntitiesAsJson()
    {
        if (!empty($this->modelEntitiesJson)) {
            return $this->modelEntitiesJson;
        }

        $data = [];
        /** @var CeModelEntityInterface $entity */
        foreach ($this->getModelEntities() as $entity) {
            $data[] = $entity->toArray();
        }

        $this->modelEntitiesJson = json_encode($data);

        return $this->modelEntitiesJson;
    }

    /**
     * Set fees.
     *
     * @param array $fees
     *
     * @return $this
     */
    public function setFees(array $fees = [])
    {
        $this->fees = $fees;

        return $this;
    }

    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Get assumption.
     *
     * @return array
     */
    public function getAssumption()
    {
        return $this->model->getAssumption();
    }

    /**
     * Get fund expenses.
     *
     * @return float
     */
    public function getFundExpenses()
    {
        if (null === $this->fundExpenses) {
            $this->fundExpenses = 0;

            /** @var CeModelEntityInterface $entity */
            foreach ($this->getModelEntities() as $entity) {
                $percent = $entity->getPercent();
                $expenseRatio = $entity->getSecurityAssignment()->getExpenseRatio();

                $this->fundExpenses += $percent * $expenseRatio / 100;
            }
        }

        return round($this->fundExpenses, 2);
    }

    /**
     * Get generous investment market.
     *
     * @return float
     */
    public function getGenerousInvestmentMarket()
    {
        $generousMarketReturn = $this->model->getGenerousMarketReturn();
        $gim = $this->getInvestmentMarket() * ($generousMarketReturn ? $generousMarketReturn : 1.2);

        return round($gim, 2);
    }

    /**
     * Get average investment market.
     *
     * @return float
     */
    public function getAverageInvestmentMarket()
    {
        return round(($this->getInvestmentMarket() * 1), 2);
    }

    public function getLowInvestmentMarket()
    {
        $lowMarketReturn = $this->model->getLowMarketReturn();
        $lim = $this->getInvestmentMarket() * ($lowMarketReturn ? $lowMarketReturn : 0.8);

        return round($lim, 2);
    }

    /**
     * Get investment market.
     *
     * @return float
     */
    public function getInvestmentMarket()
    {
        if (null === $this->investmentMarket) {
            $this->investmentMarket = 0;

            /** @var CeModelEntityInterface $entity */
            foreach ($this->getModelEntities() as $entity) {
                $percent = $entity->getPercent();
                $expectedPerformance = $entity->getSubclass()->getExpectedPerformance();

                $this->investmentMarket += $percent * $expectedPerformance / 100;
            }
        }

        return round($this->investmentMarket, 2);
    }

    public function setTransactionCommissionFees($transactionCommissionFees)
    {
        $this->transactionCommissionFee = $transactionCommissionFees;
    }

    public function getTransactionCommissionFees()
    {
        return $this->transactionCommissionFee;
    }

    /**
     * Get commissions as string.
     *
     * @return string|null
     */
    public function getCommissionsAsString()
    {
        $commissions = [];

        if ($this->model->getOwner()->hasRole('ROLE_RIA')) {
            $commissions = $this->getTransactionCommissionFees();
        } elseif ($this->model->getOwner()->hasRole('ROLE_CLIENT')) {
            $commissions = $this->getModel()->getCommissions();
        }

        $resultStr = null;
        if (!empty($commissions)) {
            if ($commissions[0] === $commissions[1]) {
                if (!$commissions[0]) {
                    return $resultStr;
                }

                unset($commissions[1]);
            }

            $strCommissions = array_map(function ($item) {
                return '$'.number_format($item, 2);
            }, $commissions);

            $resultStr = implode(' - ', $strCommissions);
        }

        return $resultStr;
    }

    /**
     * Get forecast.
     *
     * @return int|null
     */
    public function getForecast()
    {
        return $this->model->getForecast();
    }
}
