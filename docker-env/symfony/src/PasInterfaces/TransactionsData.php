<?php

namespace App\PasInterfaces;

use App\Entity\Transaction;

class TransactionsData extends BaseData
{
    /**
     * Implement loading data for pas-admin.
     *
     * Use services with tag wealthbot_admin.pas_files_loader
     *
     * @param \DateTime $date
     * @param int       $page
     *
     * @return array
     */
    public function load(\DateTime $date, $page = 0)
    {
        $this->perPage = 20;
        $tableData = [];
        $shortDate = $date->format('Y-m-d');

        $builder = $this
            ->mongoManager
            ->getRepository('App\Entity\Transaction')
            ->createQueryBuilder('transaction')
            ->field('importDate')
            ->equals($shortDate)
            ->sort('accountNumber', 'ASC')
        ;

        $transactions = $this->paginator->paginate($builder, $page, $this->perPage);

        foreach ($transactions as $transaction) {
            $tableData[] = $this->toArray($transaction);
        }

        return ['data' => json_encode($tableData), 'pagination' => $transactions];
    }

    /**
     * @param Transaction $transaction
     *
     * @return array
     */
    public function toArray(Transaction $transaction)
    {
        $accountNumber = $transaction->getAccountNumber();
        $account = $this->getSystemAccountByAccountNumber($accountNumber);
        $security = $this->getSecurityBySymbol($transaction->getSymbol());

        return [
            'ria' => $account ? $account->getClient()->getRia()->getFullName() : '',
            'last_name' => $account ? $account->getClient()->getLastName() : '',
            'first_name' => $account ? $account->getClient()->getFirstName() : '',
            'acct_number' => $accountNumber,
            'status' => $transaction->getStatus(),
            'security_type' => $security ? $security->getSecurityType()->getName() : '',
            'transaction_type' => $transaction->getTransactionCode(),
            'trade_date' => $transaction->getTxDate(),
            'symbol' => $transaction->getSymbol(),
            'shares' => $transaction->getQty(),
            'gross_amount' => $transaction->getGrossAmount(),
            'cash_id' => $transaction->getTransferAccount(),
            'fees' => $transaction->getFee(),
            'original_trade_date' => '', // ignore for now
            'settlement_date' => $transaction->getSettleDate(),
            'total_market_value' => '',
            'matching_method' => $transaction->getClosingMethod(),
            'notes' => $transaction->getNotes(),
            'origination' => '',
            'entry_date' => $transaction->getImportDate(),
            'interface_file' => '',
            'net_amount' => $transaction->getNetAmount(),
            'username' => $transaction->getUsername(),
        ];
    }

    /**
     * Method must return FileType, for example "POS".
     *
     * @return mixed
     */
    public function getFileType()
    {
        return self::DATA_TYPE_TRANSACTIONS;
    }
}
