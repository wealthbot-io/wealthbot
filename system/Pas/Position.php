<?php

namespace Pas;

use Model\Pas\Security as SecurityModel;
use Model\Pas\ClientAccountValue as ClientAccountValueModel;
use Model\Pas\ClientPortfolioValue as ClientPortfolioValueModel;
use Model\Pas\SystemClientAccount as SystemClientAccountModel;
use Model\Pas\Repository\ClientPortfolioRepository as ClientPortfolioRepo;
use Model\Pas\Repository\SystemClientAccountRepository as SystemClientAccountRepo;
use Model\Pas\Repository\ClientAccountValueRepository as ClientAccountValueRepo;
use Model\Pas\Repository\ClientPortfolioValueRepository as ClientPortfolioValueRepo;
use Model\Pas\DocumentRepository\BaseRepository as DocumentBaseRepo;
use Model\Pas\DocumentRepository\PositionRepository as DocumentPositionRepo;

class Position
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clientPortfolioRepo = new ClientPortfolioRepo();
        $this->clientAccountValueRepo = new ClientAccountValueRepo();
        $this->systemClientAccountRepo = new SystemClientAccountRepo();
        $this->clientPortfolioValueRepo = new ClientPortfolioValueRepo();
        $this->docPositionRepo = new DocumentPositionRepo();
    }

    /**
     * @param $number
     * @return \Model\Pas\SystemClientAccount|null
     */
    protected function getAccount($number)
    {
        if (null == $account = $this->systemClientAccountRepo->findOneByAccountNumber($number)) {
            //$this->bugTracker->addError("System client account by account number [{$number}] not found.");
        }
        return $account;
    }

    /**
     * @param \Model\Pas\SystemClientAccount $account
     * @return \Model\Pas\ClientPortfolio|null
     */
    protected function getPortfolio(SystemClientAccountModel $account)
    {
        if (null == $portfolio = $this->clientPortfolioRepo->findOneByClientId($account->getClientId())) {
            //$this->bugTracker->addError("Client portfolio by client id [{$clientId}] not found.");
        }
        return $portfolio;
    }

    /**
     * @param string $date
     */
    protected function calculateClientPortfolioValue($date)
    {
        $clientAccountValues = $this->clientAccountValueRepo->getAllSumByDate($date);
        foreach ($clientAccountValues as $clientAccountValue) {
            $model = new ClientPortfolioValueModel();
            $model->setClientPortfolioId($clientAccountValue->getClientPortfolioId());
            $model->setTotalValue($clientAccountValue->getTotalValue());
            $model->setTotalInSecurities($clientAccountValue->getTotalInSecurities());
            $model->setTotalCashInAccounts($clientAccountValue->getTotalCashInAccount());
            $model->setTotalCashInMoneyMarket($clientAccountValue->getTotalCashInMoneyMarket());
            $model->setSasCash($clientAccountValue->getSasCash());
            $model->setCashBuffer($clientAccountValue->getCashBuffer());
            $model->setBillingCash($clientAccountValue->getBillingCash());
            $model->setDate($date);

            // Save client portfolio value
            $this->clientPortfolioValueRepo->save($model);
        }
    }

    public function process($data)
    {
    }

    /**
     * @param string $startDate
     * @param string $endDate
     */
    public function run($startDate, $endDate)
    {
        // Get all account by date from mongo
        $accountNumbers = $this->docPositionRepo->getUniqueAccount($startDate, $endDate);
        foreach ($accountNumbers as $accountNumber) {
            // Check account exists
            if (null == $account = $this->getAccount($accountNumber)) {
                $this->docPositionRepo->changeStatusByAccountNumber($accountNumber, $startDate, $endDate, DocumentBaseRepo::STATUS_NOT_POSTED);
                continue;
            }

            // Check portfolio exists
            if (null == $portfolio = $this->getPortfolio($account)) {
                $this->docPositionRepo->changeStatusByAccountNumber($accountNumber, $startDate, $endDate, DocumentBaseRepo::STATUS_NOT_POSTED);
                continue;
            }

            $model = new ClientAccountValueModel();

            // Get all position by account number
            $positions = $this->docPositionRepo->getAllByAccountNumber($accountNumber, $startDate, $endDate);
            foreach ($positions as $position) {
                ($position['security_type'] == SecurityModel::SYMBOL_IDA12 || $position['symbol'] == SecurityModel::SYMBOL_CASH)
                    ? $model->setTotalCashInMoneyMarket($model->getTotalCashInMoneyMarket() + (float) $position['amount'])
                    : $model->setTotalInSecurities($model->getTotalInSecurities() + (float) $position['amount'])
                ;
            }

            $model->setDate($startDate);
            $model->setClientPortfolioId($portfolio->getId());
            $model->setSystemClientAccountId($account->getId());

            // Save client account value
            $this->clientAccountValueRepo->save($model);
        }

        // Calculate client portfolio value
        $this->calculateClientPortfolioValue($startDate);
    }
}