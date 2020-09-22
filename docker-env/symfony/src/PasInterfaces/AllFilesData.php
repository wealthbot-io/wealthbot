<?php

namespace App\PasInterfaces;

use App\Entity\User;

class AllFilesData extends BaseData
{
    /**
     * Implement loading data for files.
     *
     * @param \DateTime $date
     * @param int       $page
     *
     * @return array
     */
    public function load(\DateTime $date, $page = 0)
    {
        $table = [];
        //1. select all RIAs, select codes: get all RiaCompanyInformations, and select list of codes.

        /** @var User[] $rias */
        $rias = $this->em->getRepository('App\Entity\User')->getRiasOrderedByName();
        $shortDate = $date->format('Y-m-d');

        foreach ($rias as $ria) {
            $advisorCodes = $ria->getRiaCompanyInformation()->getAdvisorCodes();

            if (empty($advisorCodes)) {
                continue;
            }

            foreach ($advisorCodes as $advisorCode) {
                $transaction = $this
                    ->mongoManager
                    ->getRepository('App\Entity\Transaction')
                    ->findOneBy(['advisorCode' => $advisorCode->getName(), 'importDate' => $shortDate])
                ;

                $security = $this
                    ->mongoManager
                    ->getRepository('App\Entity\Security')
                    ->findOneBy(['importDate' => $shortDate])
                ;

                $price = $this
                    ->mongoManager
                    ->getRepository('App\Entity\Price')
                    ->findOneBy(['importDate' => $shortDate])
                ;

                $portfolio = $this
                    ->mongoManager
                    ->getRepository('App\Entity\Portfolio')
                    ->findOneBy(['importDate' => $shortDate, 'advisorId' => $advisorCode->getName()])
                ;

                $position = $this
                    ->mongoManager
                    ->getRepository('App\Entity\Position')
                    ->findOneBy(['importDate' => $shortDate])
                ;

                $realized = $this
                    ->mongoManager
                    ->getRepository('App\Entity\Realized')
                    ->findOneBy(['importDate' => $shortDate])
                ;

                $unrealized = $this
                    ->mongoManager
                    ->getRepository('App\Entity\Unrealized')
                    ->findOneBy(['importDate' => $shortDate])
                ;

                //TODO: CE-495 - add unrealized_gains and realized_gains.

                $table[] = [
                    'ria' => $ria->getRiaCompanyInformation()->getName(),
                    'code' => $advisorCode->getName(),
                    'securities' => (null !== $security ? 'Received' : 'Not Received'),
                    'prices' => (null !== $price ? 'Received' : 'Not Received'),
                    'portfolios' => (null !== $portfolio ? 'Received' : 'Not Received'),
                    'positions' => (null !== $position ? 'Received' : 'Not Received'),
                    'transactions' => (null !== $transaction ? 'Received' : 'Not Received'),
                    'unrealized_gains' => (null !== $unrealized ? 'Received' : 'Not Received'),
                    'realized_gains' => (null !== $realized ? 'Received' : 'Not Received'),
                ];
            }
        }

        return ['data' => $table];
    }

    public function getFileType()
    {
        return self::DATA_TYPE_ALL_FILES;
    }
}
