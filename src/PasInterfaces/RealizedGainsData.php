<?php

namespace App\PasInterfaces;

use App\Entity\Realized;

class RealizedGainsData extends BaseData
{
    /**
     * Implement loading data for pas-admin.
     *
     * Use services with tag wealthbot_admin.pas_files_loader
     *
     * @param \DateTime $date
     * @param $page
     *
     * @return array
     */
    public function load(\DateTime $date, $page = 0)
    {
        $tableData = [];
        $shortDate = $date->format('Y-m-d');

        $builder = $this
            ->mongoManager
            ->getRepository('App\Entity\Realized')
            ->createQueryBuilder('realized')
            ->field('importDate')
            ->equals($shortDate)
            ->sort('accountNumber', 'ASC')
        ;

        $realized = $this->paginator->paginate($builder, $page, $this->perPage);

        foreach ($realized as $row) {
            $account = $this->getSystemAccountByAccountNumber($row->getAccountNumber());

            $openDate = $row->getOpenDate();
            $openDate = new \DateTime($openDate);
            $closeDate = $row->getCloseDate();
            $closeDate = new \DateTime($closeDate);

            $tableData[] = [
                'ria' => $account ? $account->getClient()->getRia()->getFullName() : '',
                'custodian' => $account ? $account->getClient()->getCustodian()->getName() : '',
                'last_name' => $account ? $account->getClient()->getLastName() : '',
                'first_name' => $account ? $account->getClient()->getFirstName() : '',
                'acct_number' => $row->getAccountNumber(),
                'symbol' => $row->getTickerSymbol(),
                'shares_sold' => $row->getSharesSold(),
                'proceeds' => $row->getProceeds(),
                'cost' => $row->getCost(),
                'st_gain_loss' => $row->getStGainLoss(),
                'lt_gain_loss' => $row->getLtGainLoss(),
                'trading_method' => $row->getTradingMethod(),
                'lot_open_date' => $openDate->format('m/d/Y'),
                'lot_close_date' => $closeDate->format('m/d/Y'),
                'ce_match' => (Realized::STATUS_MATCH === $row->getStatus()) ? 'Yes' : 'No',
            ];
        }

        return ['data' => $tableData, 'pagination' => $realized];
    }

    /**
     * Method must return FileType, for example "POS".
     *
     * @return mixed
     */
    public function getFileType()
    {
        return self::DATA_TYPE_REALIZED_GAINS;
    }
}
