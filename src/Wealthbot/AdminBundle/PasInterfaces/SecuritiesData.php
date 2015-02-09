<?php


namespace Wealthbot\AdminBundle\PasInterfaces;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Wealthbot\AdminBundle\Document\Security;

class SecuritiesData extends BaseData
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

        /** @var Security[] $securities */
        $securities = $this
            ->mongoManager
            ->getRepository('WealthbotAdminBundle:Security')
            ->findBy(array('importDate' => $shortDate))
        ;

        foreach($securities as $security){
            $tableData[] = array(
                'symbol'      => $security->getSymbol(),
                'type'        => $security->getSecurityType(),
                'description' => $security->getDescription()
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
        return self::DATA_TYPE_SECURITIES;
    }
} 