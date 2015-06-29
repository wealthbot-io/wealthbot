<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 07.03.13
 * Time: 14:31
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityType;
use Wealthbot\AdminBundle\Entity\Subclass;

class LoadSecurityData extends AbstractFixture implements OrderedFixtureInterface
{
    private $securities = array(
        array(
            'name' => 'Vanguard Total Stock Market ETF',
            'symbol' => 'VTI',
            'security_type' => 'EQ',
            'exp_ratio' => 0.06
        ),
        array(
            'name' => 'iShares S&P 500 Index',
            'symbol' => 'IVV',
            'security_type' => 'EQ',
            'exp_ratio' => 0.09
        ),
        array(
            'name' => 'Vanguard Value ETF',
            'symbol' => 'VTV',
            'security_type' => 'EQ',
            'exp_ratio' => 0.1
        ),
        array(
            'name' => 'iShares S&P SmallCap 600 Index Fund',
            'symbol' => 'IJR',
            'security_type' => 'EQ',
            'exp_ratio' => 0.2
        ),
        array(
            'name' => 'iShares S&P SmallCap 600 Value Index',
            'symbol' => 'IJS',
            'security_type' => 'EQ',
            'exp_ratio' => 0.25
        ),
        array(
            'name' => 'Vanguard Europe Pacific ETF',
            'symbol' => 'VEA',
            'security_type' => 'EQ',
            'exp_ratio' => 0.12
        ),
        array(
            'name' => 'iShares MSCI EAFE Value Index',
            'symbol' => 'EFV',
            'security_type' => 'EQ',
            'exp_ratio' => 0.4
        ),
        array(
            'name' => 'Vanguard FTSE All-Wld ex-US SmCp Idx ETF',
            'symbol' => 'VSS',
            'security_type' => 'EQ',
            'exp_ratio' => 0.28
        ),
        array(
            'name' => 'iShares MSCI EAFE Small Cap Index',
            'symbol' => 'SCZ',
            'security_type' => 'EQ',
            'exp_ratio' => 0.4
        ),
        array(
            'name' => 'Vanguard Emerging Markets Stock ETF',
            'symbol' => 'VWO',
            'security_type' => 'EQ',
            'exp_ratio' => 0.2
        ),
        array(
            'name' => 'PowerShares DB Commodity Index Tracking',
            'symbol' => 'DBC',
            'security_type' => 'EQ',
            'exp_ratio' => 0.75
        ),
        array(
            'name' => 'Vanguard REIT Index ETF',
            'symbol' => 'VNQ',
            'security_type' => 'EQ',
            'exp_ratio' => 0.1
        ),
        array(
            'name' => 'SPDR Dow Jones Intl Real Estate',
            'symbol' => 'RWX',
            'security_type' => 'EQ',
            'exp_ratio' => 0.59
        ),
        array(
            'name' => 'Vanguard Interm-Tm Corp Bd Idx ETF',
            'symbol' => 'VCIT',
            'security_type' => 'EQ',
            'exp_ratio' => 0.14
        ),
         array(
            'name' => 'Vanguard Total International Stock ETF',
            'symbol' => 'VXUS',
            'security_type' => 'EQ',
            'exp_ratio' => 0.14
        ),
        array(
            'name' => 'Vanguard Interm-Tm Govt Bd Idx ETF',
            'symbol' => 'VGIT',
            'security_type' => 'EQ',
            'exp_ratio' => 0.14
        ),
        array(
            'name' => 'iShares S&P National AMT-Free Muni Bond',
            'symbol' => 'MUB',
            'security_type' => 'EQ',
            'exp_ratio' => 0.25
        ),
        array(
            'name' => 'Vanguard Short-Term Bond ETF',
            'symbol' => 'BSV',
            'security_type' => 'EQ',
            'exp_ratio' => 0.11
        ),
        array(
            'name' => 'Vanguard Short-Term Corp Bd Idx ETF',
            'symbol' => 'VCSH',
            'security_type' => 'EQ',
            'exp_ratio' => 0.14
        ),
        array(
            'name' => 'Vanguard Short-Term Govt Bd Idx ETF',
            'symbol' => 'VGSH',
            'security_type' => 'EQ',
            'exp_ratio' => 0.14
        ),
          array(
            'name' => 'Vanguard Total Bond Market ETF',
            'symbol' => 'BND',
            'security_type' => 'EQ',
            'exp_ratio' => 0.07
        ),
        array(
            'name' => 'SPDR Nuveen Barclays Capital S/T Muni Bond',
            'symbol' => 'SHM',
            'security_type' => 'EQ',
            'exp_ratio' => 0.2
        ),
        array(
            'name' => 'iShares Barclays TIPS Bond',
            'symbol' => 'TIP',
            'security_type' => 'EQ',
            'exp_ratio' => 0.2
        ),
        array(
            'name' => 'SPDR Barclays Capital High Yield Bond',
            'symbol' => 'JNK',
            'security_type' => 'EQ',
            'exp_ratio' => 0.4
        ),
        array(
            'name' => 'PowerShares Emerging Mkts Sovereign Debt',
            'symbol' => 'PCY',
            'security_type' => 'EQ',
            'exp_ratio' => 0.5
        ),
        array(
            'name' => 'SPDR Barclays Capital Intl Treasury Bond',
            'symbol' => 'BWX',
            'security_type' => 'EQ',
            'exp_ratio' => 0.5
        ),
        array(
            'name' => 'SPDR DB Intl Govt Infl-Protected Bond',
            'symbol' => 'WIP',
            'security_type' => 'EQ',
            'exp_ratio' => 0.5
        )
    );

    function load(ObjectManager $manager)
    {
        $this->loadSecurities($manager);
        $this->loadModelSecurities($manager);

        $manager->flush();
    }

    private function loadSecurities(ObjectManager $manager)
    {
        $repository = $manager->getRepository('WealthbotAdminBundle:Security');

        foreach ($this->securities as  $item) {
            $security = $repository->findOneBySymbol($item['symbol']);

            if (!$security) {
                /** @var SecurityType $securityType */
                $securityType = $this->getReference('security-type-' . $item['security_type']);

                $security = new Security();
                $security->setName($item['name']);
                $security->setSymbol($item['symbol']);
                $security->setSecurityType($securityType);
                $security->setExpenseRatio($item['exp_ratio']);

                $manager->persist($security);
            }

            $this->addReference('security-' . $item['symbol'], $security);
        }
    }

    private function loadModelSecurities(ObjectManager $manager)
    {
        $subclassIndex = 1;
        $subclassesCount = 9;
        $maxSecuritiesCount = floor(count($this->securities) / $subclassesCount);

        $i = 1;
        foreach ($this->securities as $item) {
            if ($i > ($maxSecuritiesCount * $subclassIndex) && $subclassIndex < $subclassesCount) {
                $subclassIndex++;
            }

            /** @var Security $security */
            /** @var Subclass $subclass */
            $security = $this->getReference('security-' . $item['symbol']);
            $subclass = $this->getReference('subclass-' . $subclassIndex);

            $securityAssignment = new SecurityAssignment();
            $securityAssignment->setSecurity($security);
            $securityAssignment->setSubclass($subclass);
            $securityAssignment->setModel($subclass->getAssetClass()->getModel());

            $manager->persist($securityAssignment);
            $this->addReference('model-security-assignment-' . $i, $securityAssignment);

            $i++;
        }
    }

    public function getOrder()
    {
        return 4;
    }
}