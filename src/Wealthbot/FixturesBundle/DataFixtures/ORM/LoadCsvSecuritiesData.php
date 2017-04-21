<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.07.13
 * Time: 19:21
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityType;
use Wealthbot\FixturesBundle\Model\AbstractCsvFixture;

class LoadCsvSecuritiesData extends AbstractCsvFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $usedSymbols = [];
        $repository = $manager->getRepository('WealthbotAdminBundle:Security');
        $securities = $this->getCsvData('securities.csv');

        foreach ($securities as $index => $item) {
            if ($index === 0) {
                continue;
            }

            $name = trim($item[0]);
            $symbol = trim($item[1]);
            $typeString = 'security-type-'.((trim($item[2]) === 'ETF') ? 'EQ' : 'MU');
            $expenseRatio = round((float) str_replace(',', '.', trim($item[3])), 2);

            /** @var SecurityType $securityType */
            $securityType = $this->getReference($typeString);

            $exist = $repository->findOneBySymbol($symbol);
            if (!$exist && !in_array($symbol, $usedSymbols)) {
                $usedSymbols[] = $symbol;

                $security = new Security();
                $security->setName($name);
                $security->setSymbol($symbol);
                $security->setSecurityType($securityType);
                $security->setExpenseRatio($expenseRatio);

                $manager->persist($security);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}
