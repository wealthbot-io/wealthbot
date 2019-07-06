<?php

namespace App\PasInterfaces;

class PositionsData extends BaseData
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
        $tableData = [];
        $shortDate = $date->format('Y-m-d');
        $this->perPage = 20;

        $builder = $this
            ->mongoManager
            ->getRepository('App\Entity\Position')
            ->createQueryBuilder('position')
            ->field('importDate')
            ->equals($shortDate)
            ->sort('accountNumber', 'ASC')
        ;

        $positions = $this->paginator->paginate($builder, $page, $this->perPage);

        foreach ($positions as $position) {
            $account = $this->getSystemAccountByAccountNumber($position->getAccountNumber());

            $ceShares = (float) $position->getCeShares();
            $custodialShares = (float) $position->getAmount();

            $tableData[] = [
                'ria' => $account ? $account->getClient()->getRia()->getFullName() : '',
                'custodian' => $account ? $account->getClient()->getCustodian() : '',
                'last_name' => $account ? $account->getClient()->getLastName() : '',
                'first_name' => $account ? $account->getClient()->getFirstName() : '',
                'acct_number' => $position->getAccountNumber(),
                'symbol' => $position->getSymbol(),
                'ce_shares' => $ceShares,
                'custodial_shares' => $custodialShares,
                'difference' => $custodialShares - $ceShares,
            ];
        }

        return ['data' => $tableData, 'pagination' => $positions];
    }

    /**
     * Method must return FileType, for example "POS".
     *
     * @return mixed
     */
    public function getFileType()
    {
        return self::DATA_TYPE_POSITIONS;
    }
}
