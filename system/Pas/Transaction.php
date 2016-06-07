<?php

namespace Pas;

use Model\Pas\Transaction as TransactionModel;
use Model\Pas\SystemClientAccount as SystemClientAccountModel;

use Model\Pas\Repository\SecurityRepository;
use Model\Pas\Repository\TransactionRepository;
use Model\Pas\Repository\ClosingMethodRepository;
use Model\Pas\Repository\TransactionTypeRepository;
use Model\Pas\Repository\SystemClientAccountRepository;
use Model\Pas\Repository\BillItemRepository;

use Model\Pas\DocumentRepository\BaseRepository as DocumentBaseRepo;
use Model\Pas\DocumentRepository\TransactionRepository as DocumentTransactionRepo;

class Transaction extends Pas
{
    /**
     * @var array
     */
    protected $collection = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->docTransactionRepo = new DocumentTransactionRepo();
        $this->initRepositories();
    }

    /**
     * Init repositories
     */
    public function initRepositories()
    {
        $this->addRepository(new TransactionRepository());
        $this->addRepository(new ClosingMethodRepository());
        $this->addRepository(new TransactionTypeRepository());
        $this->addRepository(new SystemClientAccountRepository());
        $this->addRepository(new BillItemRepository());
        $this->addRepository(new SecurityRepository());
    }

    public function getSecurity($symbol)
    {
        if (null == $security = $this->getRepository('Security')->findOneBySymbol($symbol)) {
            // TODO: add Error log
        }
        return $security;
    }

    public function getTransactionType($name)
    {
        if (null == $transactionType = $this->getRepository('TransactionType')->findOneByName($name)) {
            // TODO: add Error log
        }
        return $transactionType;
    }

    public function getClosingMethod($name)
    {
        if (null == $closingMethod = $this->getRepository('ClosingMethod')->findOneByName(empty($name) ? TransactionModel::CLOSING_METHOD_NAME : $name)) {
            // TODO: add Error log
        }
        return $closingMethod;
    }

    public function getAccount($number)
    {
        if (null == $account = $this->getRepository('SystemClientAccount')->findOneByAccountNumber($number)) {
            // TODO: add Error log
        }
        return $account;
    }

    /**
     * @param SystemClientAccountModel $account
     * @param TransactionModel $model
     * @return bool
     */
    public function updateFeeCollected(SystemClientAccountModel $account, TransactionModel $model)
    {
        if ($model->isMFEE()) {
            $billItem = $this->getRepository('BillItem')->findOneByAccountAndPeriod($account->getId(), $model->getTxDateAsDateTime());
            if ($billItem) {
                $billItem->setFeeCollected($billItem->getFeeCollected() + $model->getNetAmount());
                $billItem->setStatusIsCollected();
                return $this->getRepository('BillItem')->update($billItem->getId(), $billItem);
            }
        }
        // TODO: add error log
    }

    /**
     * Create transaction
     *
     * @param \Model\Pas\SystemClientAccount $account
     * @param array $data
     * @return int|null
     */
    public function create(SystemClientAccountModel $account, array $data)
    {
        if (null == $security = $this->getSecurity($data['symbol'])) return null;
        if (null == $closingMethod = $this->getClosingMethod($data['closing_method'])) return null;
        if (null == $transactionType = $this->getTransactionType($data['transaction_code'])) return null;

        $model = new TransactionModel();
        $model->loadFromArray($data);
        $model->setStatus('verified');
        $model->setAccountId($account->getId());
        $model->setSecurityId($security->getId());
        $model->setClosingMethodId($closingMethod->getId());
        $model->setTransactionTypeId($transactionType->getId());

        $id = $this->getRepository('Transaction')->save($model);

        if ($id && $model->isCreateLot()) {
            $data['transaction_id'] = $id;
            $this->collection[$data['tx_date']][] = $data;
        }

        if ($id) {
            // Update bill item fee collected
            $this->updateFeeCollected($account, $model);
        }

        return $id;
    }

    /**
     * @param \Model\Pas\SystemClientAccount $account
     * @param array $data
     */
    public function process(SystemClientAccountModel $account, array $data)
    {
        foreach ($data as $row) {
            $this->getRepository('Transaction')->beginTransaction();
            if (null === $transaction = $this->create($account, $row)) {
                // TODO: add error log
                $this->getRepository('Transaction')->rollback();
                $this->docTransactionRepo->changeStatusById($row['_id'], DocumentBaseRepo::STATUS_NOT_POSTED);
            } else {
                $this->getRepository('Transaction')->commit();
                $this->docTransactionRepo->changeStatusById($row['_id'], DocumentBaseRepo::STATUS_POSTED);
            }
        }
    }

    /**
     * @param string $startDate
     * @param string $endDate
     */
    public function run($startDate, $endDate)
    {
        // Get all account by date from mongo
        $accountNumbers = $this->docTransactionRepo->getUniqueAccount($startDate, $endDate);
        foreach ($accountNumbers as $accountNumber) {
            // Check account exists
            if (null == $account = $this->getAccount($accountNumber)) {
                $this->docTransactionRepo->changeStatusByAccountNumber($accountNumber, $startDate, $endDate, DocumentBaseRepo::STATUS_NOT_POSTED);
                continue;
            }

            // Get all transaction by account number
            $data = $this->docTransactionRepo->getAllByAccountNumber($accountNumber, $startDate, $endDate);
            $this->process($account, $data);
        }

        // Generate lots
        $lot = new Lot();
        $lot->run($this->collection);
    }
}