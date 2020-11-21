<?php

namespace App\PasInterfaces;

interface DataInterface
{
    const DATA_TYPE_ALL_FILES = 'all_files';
    const DATA_TYPE_SECURITIES = 'securities';
    const DATA_TYPE_PRICES = 'prices';
    const DATA_TYPE_ACCOUNTS = 'accounts';
    const DATA_TYPE_POSITIONS = 'positions';
    const DATA_TYPE_TRANSACTIONS = 'transactions';
    const DATA_TYPE_UNREALIZED_GAINS = 'unrealized_gains';
    const DATA_TYPE_REALIZED_GAINS = 'realized_gains';
    const DATA_TYPE_TRADE_RECON = 'trade_recon';
    const DATA_TYPE_FEE_RECON = 'fee_recon';

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
    public function load(\DateTime $date, $page);

    /**
     * Method must return FileType, for example "POS".
     *
     * @return mixed
     */
    public function getFileType();
}
