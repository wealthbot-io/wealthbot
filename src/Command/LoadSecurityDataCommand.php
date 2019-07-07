<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Security;

class LoadSecurityDataCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('wealthbot:fixtures:security')
            ->setDescription('Load security data form CSV.')
            ->setHelp('');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $securityRepo = $em->getRepository('App\Entity\Security');
        $securityTypeRepo = $em->getRepository('App\Entity\SecurityType');

        $i = 0;
        $typeHash = [];

        $securities = $this->loadCsvData('security_full.csv', $maxLength = 10000, $delimiter = ',');
        foreach ($securities as $index => $item) {
            if (0 === $index) {
                continue;
            }

            $type = trim($item[0]);
            $type = 'ETF' === $type ? 'EQ' : 'MU';
            $symbol = trim($item[1]);
            $name = trim($item[2]);
            $ratio = round((float) str_replace(',', '.', trim($item[3])), 2);

            if (isset($typeHash[$type])) {
                $securityType = $typeHash[$type];
            } else {
                $securityType = $securityTypeRepo->findOneByName($type);
                if ($securityType) {
                    $typeHash[$type] = $securityType;
                }
            }

            $security = $securityRepo->findOneBySymbol($symbol);
            if (!$security && $securityType) {
                if (0 === (++$i % 100)) {
                    $security = new Security();
                    $security->setName($name);
                    $security->setSymbol($symbol);
                    $security->setSecurityType($securityType);
                    $security->setExpenseRatio($ratio);
                    $em->persist($security);
                    $output->writeln("Security items [{$i}] has been loaded.");
                }
            }
        }

        $em->flush();
        $em->clear();
        $output->writeln("Security items [{$i}] has been loaded.");
        $output->writeln('Success!');
    }

    protected function loadCsvData($filename, $maxLength = 1000, $delimiter = ';')
    {
        $data = [];
        $path = getcwd().'/src/WealthBot/FixturesBundle/DataFixtures/CSV/'.$filename;
        $handle = fopen($path, 'r');

        if (false !== $handle) {
            while ($item = fgetcsv($handle, $maxLength, $delimiter)) {
                $data[] = $item;
            }
            fclose($handle);
        }

        return $data;
    }
}
