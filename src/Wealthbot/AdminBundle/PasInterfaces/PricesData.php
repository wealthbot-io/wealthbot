<?php

namespace Wealthbot\AdminBundle\PasInterfaces;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Wealthbot\AdminBundle\Document\Price;

class PricesData extends BaseData
{
    /**
     * Implement loading data for pas-admin
     *
     * Use services with tag wealthbot_admin.pas_files_loader
     *
     * @param \DateTime $date
     * @param int $page
     * @return Array
     */
    public function load(\DateTime $date, $page = 0)
    {
        $tableData = array();
        $shortDate = $date->format('Y-m-d');

        /** @var Price[] $prices */
        $prices = $this
            ->mongoManager
            ->getRepository('WealthbotAdminBundle:Price')
            ->findBy(array('importDate' => $shortDate))
        ;

        foreach($prices as $price){
            $tableData[] = array(
                'symbol' => $price->getSymbol(),
                'price'  => $price->getPrice()
            );
        }

        return array('data' => $tableData);
    }

    /**
     * Method must return FileType, for example "POS"
     *
     * @return mixed
     */
    public function getFileType()
    {
        return self::DATA_TYPE_PRICES;
    }


} 