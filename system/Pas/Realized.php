<?php

namespace Pas;

use Model\Pas\SystemClientAccount as SystemClientAccountModel;

use Model\Pas\Repository\LotRepository as LotRepo;
use Model\Pas\Repository\SystemClientAccountRepository as SystemClientAccountRepo;

use Model\Pas\DocumentRepository\BaseRepository as DocumentBaseRepo;
use Model\Pas\DocumentRepository\RealizedRepository as DocumentRealizedRepo;

class Realized
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lotRepo = new LotRepo();
        $this->accountRepo = new SystemClientAccountRepo();
        $this->docRelizedRepo = new DocumentRealizedRepo();
    }

    /**
     * @param $number
     * @return \Model\Pas\SystemClientAccount|null
     */
    protected function getAccount($number)
    {
        if (null == $account = $this->accountRepo->findOneByAccountNumber($number)) {
            //$this->bugTracker->addError("System client account by account number [{$number}] not found.");
        }
        return $account;
    }

    /**
     * @param SystemClientAccountModel $account
     * @param array $data
     */
    public function process(SystemClientAccountModel $account, array $data)
    {
        $lots = $this->lotRepo->findAllClosedBy(array(
            'date'       => $data['close_date'],
            'quantity'   => $data['shares_sold'],
            'account_id' => $account->getId()
        ));

        foreach ($lots as $lot) {
            $realizedGain   = (int) $lot->getRealizedGain();
            $realizedGainST = (int) $data['st_gain_loss'];
            $realizedGainLT = (int) $data['lt_gain_loss'];

            if ($realizedGain < 0) {
                $status = $realizedGainST == abs($realizedGain) ? DocumentRealizedRepo::STATUS_MATCH : DocumentRealizedRepo::STATUS_NOT_MATCH;
            } else {
                $status = $realizedGainLT == abs($realizedGain) ? DocumentRealizedRepo::STATUS_MATCH : DocumentRealizedRepo::STATUS_NOT_MATCH;
            }

            $this->docRelizedRepo->changeStatusById($data['_id'], $status);
        }
    }

    /**
     * @param string $startDate
     * @param string $endDate
     */
    public function run($startDate, $endDate)
    {
        // Get all account by date from mongo
        $accountNumbers = $this->docRelizedRepo->getUniqueAccount($startDate, $endDate);
        foreach ($accountNumbers as $accountNumber) {
            // Check account exists
            if (null == $account = $this->getAccount($accountNumber)) {
                $this->docRelizedRepo->changeStatusByAccountNumber($accountNumber, $startDate, $endDate, DocumentBaseRepo::STATUS_NOT_POSTED);
                continue;
            }

            $realizes = $this->docRelizedRepo->getAllByAccountNumber($accountNumber, $startDate, $endDate);
            foreach ($realizes as $realized) {
                $this->proces($account, $realized);
            }
        }
    }
}