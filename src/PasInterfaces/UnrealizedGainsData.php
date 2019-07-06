<?php

namespace App\PasInterfaces;

class UnrealizedGainsData extends BaseData
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
            ->getRepository('App\Entity\Unrealized')
            ->createQueryBuilder('unrealized')
            ->field('importDate')
            ->equals($shortDate)
            ->sort('accountNumber', 'ASC')
        ;

        $unrealized = $this->paginator->paginate($builder, $page, $this->perPage);

        foreach ($unrealized as $row) {
            $account = $this->getSystemAccountByAccountNumber($row->getAccountNumber());

            $lotDate = $row->getLotDate();
            $lotDate = empty($lotDate) ? '' : new \DateTime($lotDate);

            $custodialDate = $row->getOriginalPurchaseDate();
            $custodialDate = new \DateTime($custodialDate);

            $tableData[] = [
                'ria' => $account ? $account->getClient()->getRia()->getFullName() : '',
                'custodian' => $row->getCustodialId(),
                'last_name' => $account ? $account->getClient()->getLastName() : '',
                'first_name' => $account ? $account->getClient()->getFirstName() : '',
                'acct_number' => $row->getAccountNumber(),
                'symbol' => $row->getSymbol(),
                'ce_shares' => $row->getLotQuantity(),
                'ce_cost_basis' => $row->getLotAmount(),
                'ce_date' => empty($lotDate) ? '' : $lotDate->format('m/d/Y'),
                'custodial_shares' => $row->getCurrentQty(),
                'custodial_cost_basis' => $row->getCostBasisUn(),
                'custodial_date' => $custodialDate->format('m/d/Y'),
            ];
        }

        return ['data' => $tableData, 'pagination' => $unrealized];
    }

    /**
     * Method must return FileType, for example "POS".
     *
     * @return mixed
     */
    public function getFileType()
    {
        return self::DATA_TYPE_UNREALIZED_GAINS;
    }
}
