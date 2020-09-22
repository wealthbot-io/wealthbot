<?php

namespace App\PasInterfaces;

use App\Entity\Security;

class SecuritiesData extends BaseData
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

        /** @var Security[] $securities */
        $securities = $this
            ->mongoManager
            ->getRepository('App\Entity\Security')
            ->findBy(['importDate' => $shortDate])
        ;

        foreach ($securities as $security) {
            $tableData[] = [
                'symbol' => $security->getSymbol(),
                'type' => $security->getSecurityType(),
                'description' => $security->getDescription(),
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
        return self::DATA_TYPE_SECURITIES;
    }
}
