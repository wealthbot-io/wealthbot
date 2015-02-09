<?php

namespace Wealthbot\AdminBundle\PasInterfaces;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Wealthbot\UserBundle\Entity\User;

class AllFilesData  extends BaseData
{
    /**
     * Implement loading data for files.
     *
     * @param \DateTime $date
     * @param int $page
     * @return Array
     */
    public function load(\DateTime $date, $page = 0)
    {
        $table = array();
        //1. select all RIAs, select codes: get all RiaCompanyInformations, and select list of codes.

        /** @var User[] $rias */
        $rias = $this->em->getRepository('WealthbotUserBundle:User')->getRiasOrderedByName();
        $shortDate = $date->format('Y-m-d');

        foreach($rias as $ria) {
            $advisorCodes = $ria->getRiaCompanyInformation()->getAdvisorCodes();

            if (empty($advisorCodes)) {
                continue;
            }

            foreach($advisorCodes as $advisorCode) {
                $transaction = $this
                    ->mongoManager
                    ->getRepository('WealthbotAdminBundle:Transaction')
                    ->findOneBy(array('advisorCode' => $advisorCode->getName(), 'importDate' => $shortDate))
                ;

                $security = $this
                    ->mongoManager
                    ->getRepository('WealthbotAdminBundle:Security')
                    ->findOneBy(array('importDate' => $shortDate))
                ;

                $price = $this
                    ->mongoManager
                    ->getRepository('WealthbotAdminBundle:Price')
                    ->findOneBy(array('importDate' => $shortDate))
                ;

                $portfolio = $this
                    ->mongoManager
                    ->getRepository('WealthbotAdminBundle:Portfolio')
                    ->findOneBy(array('importDate' => $shortDate, 'advisorId' => $advisorCode->getName()))
                ;

                $position = $this
                    ->mongoManager
                    ->getRepository('WealthbotAdminBundle:Position')
                    ->findOneBy(array('importDate' => $shortDate))
                ;

                $realized = $this
                    ->mongoManager
                    ->getRepository('WealthbotAdminBundle:Realized')
                    ->findOneBy(array('importDate' => $shortDate))
                ;

                $unrealized = $this
                    ->mongoManager
                    ->getRepository('WealthbotAdminBundle:Unrealized')
                    ->findOneBy(array('importDate' => $shortDate))
                ;

                //TODO: CE-495 - add unrealized_gains and realized_gains.

                $table[] = array(
                    'ria'               => $ria->getRiaCompanyInformation()->getName(),
                    'code'              => $advisorCode->getName(),
                    'securities'        => ($security !== null ? 'Received' : 'Not Received'),
                    'prices'            => ($price !== null ? 'Received' : 'Not Received'),
                    'portfolios'        => ($portfolio !== null ? 'Received' : 'Not Received'),
                    'positions'         => ($position !== null ? 'Received' : 'Not Received'),
                    'transactions'      => ($transaction !== null ? 'Received' : 'Not Received'),
                    'unrealized_gains'  => ($unrealized !== null ? 'Received' : 'Not Received'),
                    'realized_gains'    => ($realized !== null ? 'Received' : 'Not Received')
                );
            }
        }

        return array('data' => $table);
    }

    public function getFileType()
    {
        return self::DATA_TYPE_ALL_FILES;
    }
}