<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 24.01.14
 * Time: 16:17.
 */

namespace Wealthbot\RiaBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wealthbot\RiaBundle\Document\FirmMetric;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Entity\User;

class UpdateFirmMetricsCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('rx:ria:update-firm-metrics')
            ->setDescription('Update ria firm metrics')
            ->setHelp(<<<EOT
The <info>rx:ria:update-firm-metrics</info> command update ria firm metrics:

  <info>php app/console rx:ria:update-firm-metrics</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $config = $em->getConfiguration();
        $config->addCustomStringFunction('YEAR', 'Wealthbot\MailerBundle\DQL\DatetimeFunction\Year');
        $config->addCustomStringFunction('QUARTER', 'Wealthbot\MailerBundle\DQL\DatetimeFunction\Quarter');

        $companyInformation = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findAll();

        /** @var RiaCompanyInformation $company */
        foreach ($companyInformation as $company) {
            $ria = $company->getRia();
            $output->writeln(sprintf('Updating metrics for %s (ria id: %d)...', $company->getName(), $ria->getId()));

            $clients = $this->calculateClientsByRiaAndStatus($em, $ria, Profile::CLIENT_STATUS_CLIENT);
            $prospects = $this->calculateClientsByRiaAndStatus($em, $ria, Profile::CLIENT_STATUS_PROSPECT);

            $firmMetric = $dm->getRepository('WealthbotRiaBundle:FirmMetric')->findOneBy(['companyInformationId' => $company->getId()]);
            if (!$firmMetric) {
                $firmMetric = new FirmMetric();
            }

            $firmMetric->setCompanyInformationId($company->getId());
            $firmMetric->setClients($clients['result']);
            $firmMetric->setClientsQtdChange($clients['qtd_change']);
            $firmMetric->setClientsYearChange($clients['year_change']);
            $firmMetric->setProspects($prospects['result']);
            $firmMetric->setProspectsQtdChange($prospects['qtd_change']);
            $firmMetric->setProspectsYearChange($prospects['year_change']);

            $dm->persist($firmMetric);
            $output->writeln('Ok.');
        }

        $dm->flush();
        $output->writeln('Complete.');
    }

    private function calculateClientsByRiaAndStatus(EntityManager $em, User $ria, $status)
    {
        $repository = $em->getRepository('WealthbotUserBundle:Profile');

        $today = new \DateTime();
        $year = $today->format('Y');
        $quarter = $this->getQuarter($today);

        $getClients = function (User $ria, $status, \DateTime $currDate, $year = null, $quarter = null) use ($repository) {
            $qb = $repository->createQueryBuilder('p');
            $qb->select('COUNT(u.id) as result')
                ->leftJoin('p.user', 'u')
                ->where('p.ria = :ria')
                ->andWhere('p.client_status = :status')
                ->andWhere('u.created < :currDate')
                ->setParameters([
                    'ria' => $ria,
                    'status' => $status,
                    'currDate' => $currDate->format('Y-m-d'),
                ]);

            if (null !== $quarter && null !== $year) {
                $qb->andWhere('QUARTER(u.created) = :quarter')
                    ->andWhere('YEAR(u.created) = :year')
                    ->setParameter('quarter', $quarter)
                    ->setParameter('year', $year);
            } elseif (null !== $year) {
                $qb->andWhere('YEAR(u.created) <= :year')
                    ->setParameter('year', $year);
            }

            return $qb->getQuery()->getOneOrNullResult();
        };

        $clients = $getClients($ria, $status, $today);

        $clientsYearNew = $getClients($ria, $status, $today, $year);
        $clientsYearOld = $getClients($ria, $status, $today, $year - 1);

        $clientsQtdNew = $getClients($ria, $status, $today, $year, $quarter);

        if ($quarter > 1) {
            $prevQuarter = $quarter - 1;
            $prevQuarterYear = $year;
        } else {
            $prevQuarter = 4;
            $prevQuarterYear = $year - 1;
        }

        $clientsQtdOld = $getClients($ria, $status, $today, $prevQuarterYear, $prevQuarter);

        return [
            'result' => (int) $clients['result'],
            'qtd_change' => round(($clientsQtdOld['result'] === 0 ? 0 : ($clientsQtdNew['result'] - $clientsQtdOld['result']) * 100 / $clientsQtdOld['result']), 2),
            'year_change' => round(($clientsYearOld['result'] === 0 ? 0 : ($clientsYearNew['result'] - $clientsYearOld['result']) * 100 / $clientsYearOld['result']), 2),
        ];
    }

    private function getQuarter(\DateTime $date)
    {
        $month = (int) $date->format('m');
        if ($month >= 1 && $month <= 3) {
            $quarter = 1;
        } elseif ($month >= 4 && $month <= 6) {
            $quarter = 2;
        } elseif ($month >= 7 && $month <= 9) {
            $quarter = 3;
        } else {
            $quarter = 4;
        }

        return $quarter;
    }
}
