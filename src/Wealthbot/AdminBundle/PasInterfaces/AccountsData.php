<?php

namespace Wealthbot\AdminBundle\PasInterfaces;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Wealthbot\AdminBundle\Document\Portfolio;
use Wealthbot\AdminBundle\Service\BusinessCalendar;
use Wealthbot\ClientBundle\Entity\SystemAccount;

class AccountsData implements DataInterface
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \Doctrine\ODM\MongoDB\DocumentManager */
    private $mongoManager;

    /** @var \Wealthbot\AdminBundle\Service\BusinessCalendar */
    private $businessCalendar;

    public function __construct(EntityManager $em, DocumentManager $mongoManager, BusinessCalendar $businessCalendar)
    {
        $this->em = $em;
        $this->mongoManager = $mongoManager;
        $this->businessCalendar = $businessCalendar;
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
        $tableData = [];

        $shortDate = $date->format('Y-m-d');

        $accounts = $this
            ->mongoManager
            ->getRepository('WealthbotAdminBundle:Portfolio')
            ->findBy(['importDate' => $shortDate])
        ;

        $advisorCodes = $this->em->getRepository('WealthbotRiaBundle:AdvisorCode')->findAll();
        $advisorByCode = [];

        foreach ($advisorCodes as $advisorCode) {
            $advisorByCode[$advisorCode->getName()] = $advisorCode->getRiaCompany()->getRia();
        }

        foreach ($accounts as $account) {
            $advisorCode = $account->getAdvisorId();
            $advisorName = '';
            if (array_key_exists($advisorCode, $advisorByCode)) {
                $advisorName = $advisorByCode[$advisorCode]->getRiaCompanyInformation()->getName();
            }

            $tableData[] = [
                'ria' => $advisorName,
                'last_name' => $account->getLastName(),
                'first_name' => $account->getFirstName(),
                'acct_number' => $account->getAccountNumber(),
                'type' => $account->getAccountType(),
                'warning' => false,
            ];
        }

        //Find ALL accounts needs to be in this day, add it to list and mark by red color.

        $transferAccountDate = new \DateTime();
        $transferAccountDate->setTime(0, 0, 0);
        $transferAccountDate = $this->businessCalendar->addBusinessDays($transferAccountDate, -SystemAccount::DAYS_WAIT_TRANSFER_OR_ROLLOVER_ACCOUNT);

        $newAccountDate = new \DateTime();
        $newAccountDate->setTime(0, 0, 0);
        $newAccountDate = $this->businessCalendar->addBusinessDays($newAccountDate, -SystemAccount::DAYS_WAIT_NEW_ACCOUNT);

        /** @var SystemAccount[] $notAcceptedAccounts */
        $notAcceptedAccounts = $this->em->getRepository('WealthbotClientBundle:SystemAccount')->getMustBeAcceptedAlready($transferAccountDate, $newAccountDate);
        foreach ($notAcceptedAccounts as $account) {
            $advisorName = $account->getClient()->getRia()->getRiaCompanyInformation()->getName();
            $tableData[] = [
                'ria' => $advisorName,
                'last_name' => $account->getClient()->getLastName(),
                'first_name' => $account->getClient()->getFirstName(),
                'acct_number' => $account->getAccountNumber(),
                'type' => $account->getType(),
                'warning' => true,
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
        return self::DATA_TYPE_ACCOUNTS;
    }
}
