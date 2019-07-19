<?php

namespace App\PasInterfaces;

use App\Entity\Price;

class PricesData extends BaseData
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

        /** @var Price[] $prices */
        $prices = $this
            ->mongoManager
            ->getRepository('App\Entity\Price')
            ->findBy(['importDate' => $shortDate])
        ;

        foreach ($prices as $price) {
            $tableData[] = [
                'symbol' => $price->getSymbol(),
                'price' => $price->getPrice(),
            ];
        }

        return ['data' => $tableData];
    }

    /**
     * Method must return FileType, for example "POS".
     *
     * @return mixed
     */
    public function getFileType()
    {
        return self::DATA_TYPE_PRICES;
    }
}
