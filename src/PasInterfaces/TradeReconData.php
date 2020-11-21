<?php

namespace App\PasInterfaces;

use App\Manager\TradeReconManager;

class TradeReconData implements DataInterface
{
    protected $tradeReconManager;


    public function __construct(TradeReconManager $tradeReconManager)
    {
        $this->tradeReconManager = $tradeReconManager;
    }

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
        $clientName = '';
        $dateFrom = clone $date;
        $dateTo = clone $date;

        return ['data' => $this->tradeReconManager->getValues($dateFrom, $dateTo, null, $clientName)];
    }

    /**
     * Method must return FileType, for example "POS".
     *
     * @return mixed
     */
    public function getFileType()
    {
        return self::DATA_TYPE_TRADE_RECON;
    }
}
