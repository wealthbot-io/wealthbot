<?php

namespace Pas;

use Model\Pas\SystemClientAccount as SystemClientAccountModel;
use Model\Pas\Repository\LotRepository as LotRepo;
use Model\Pas\Repository\SecurityRepository as SecurityRepo;
use Model\Pas\Repository\SystemClientAccountRepository as SystemClientAccountRepo;
use Model\Pas\DocumentRepository\BaseRepository as DocumentBaseRepo;
use Model\Pas\DocumentRepository\UnrealizedRepository as DocumentUnrealizedRepo;
use Wealthbot\ClientBundle\Entity\Lot as WealthbotLot;

class Unrealized
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lotRepo = new LotRepo();
        $this->securityRepo = new SecurityRepo();
        $this->accountRepo = new SystemClientAccountRepo();
        $this->docUnrealizedRepo = new DocumentUnrealizedRepo();
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
        foreach ($data as $row) {
            if (null == $security = $this->securityRepo->findOneBySymbol($row['symbol'])) {
                $this->docUnrealizedRepo->changeStatusById($row['_id'], DocumentBaseRepo::STATUS_NOT_POSTED);
                continue;
            }

            $lot = $this->lotRepo->findOneBy(array(
                'status'      => WealthbotLot::LOT_INITIAL,
                'quantity'    => $row['current_qty'],
                'security_id' => $security->getId(),
                'client_system_account_id' => $account->getId()
            ));

            if (null == $lot) {
                $this->docUnrealizedRepo->changeStatusById($row['_id'], DocumentBaseRepo::STATUS_NOT_POSTED);
                continue;
            }

            $this->docUnrealizedRepo->update($row['_id'], array(
                'status'       => DocumentBaseRepo::STATUS_POSTED,
                'lot_date'     => $lot->getDate(),
                'lot_amount'   => $lot->getAmount(),
                'lot_quantity' => $lot->getQuantity()
            ));
        }
    }

    /**
     * @param string $startDate
     * @param string $endDate
     */
    public function run($startDate, $endDate)
    {
        // Get all account by date from mongo
        $accountNumbers = $this->docUnrealizedRepo->getUniqueAccount($startDate, $endDate);
        foreach ($accountNumbers as $accountNumber) {
            // Check account exists
            if (null == $account = $this->getAccount($accountNumber)) {
                $this->docUnrealizedRepo->changeStatusByAccountNumber($accountNumber, $startDate, $endDate, DocumentBaseRepo::STATUS_NOT_POSTED);
                continue;
            }

            $unrealizes = $this->docUnrealizedRepo->getAllByAccountNumber($accountNumber, $startDate, $endDate);
            $this->process($account, $unrealizes);
        }
    }
}