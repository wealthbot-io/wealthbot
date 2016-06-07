<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 24.06.13
 * Time: 16:45
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Tests\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\AdminBundle\Entity\Fee;
use Wealthbot\AdminBundle\Manager\FeeManager;
use Wealthbot\UserBundle\Entity\User;

class FeeManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $adminFeeData = [
        [
            'top_tier' => 10000,
            'fee_without_retirement' => 0.0010,
        ],
        [
            'top_tier' => 20000,
            'fee_without_retirement' => 0.0020,
        ],
        [
            'top_tier' => Fee::INFINITY,
            'fee_without_retirement' => 0.0020,
        ],
    ];

    protected $feeData = [
        [
            'top_tier' => 100000,
            'fee_without_retirement' => 0.1,
        ],
        [
            'top_tier' => 500000,
            'fee_without_retirement' => 0.01,
        ],
        [
            'top_tier' => 500000.01,
            'fee_without_retirement' => 0.005,
        ],
        [
            'top_tier' => 10000000000,
            'fee_without_retirement' => 0.025,
        ],
    ];

    public function testGetClientFees()
    {
        $feeManager = new FeeManager(
            $this->getMockEntityManager(),
            $this->getUserManagerMock(),
            $this->getMockCashCalculationManager(),
            $this->getMockPeriodManager(),
            $this->getMockBillingSpecManager()
        );

        $riaUser = $this->getRiaUser();

        $riaFees = [
            [
                'tier_top' => 1000,
                'fee_without_retirement' => 0.0021,
            ],
            [
                'tier_top' => 11000,
                'fee_without_retirement' => 0.0022,
            ],
            [
                'tier_top' => Fee::INFINITY,
                'fee_without_retirement' => 0.0023,
            ],
        ];

        $calculatedResult = $feeManager->getClientFees($riaUser, $riaFees);

        $expectedResult = [
            [
                'tier_bottom' => 0,
                'tier_top' => 1000,
                'fee_without_retirement' => 0.0031,
            ],
            [
                'tier_bottom' => 1000.01,
                'tier_top' => 10000,
                'fee_without_retirement' => 0.0032,
            ],
            [
                'tier_bottom' => 10000.01,
                'tier_top' => 11000,
                'fee_without_retirement' => 0.0042,
            ],
            [
                'tier_bottom' => 11000.01,
                'tier_top' => 20000,
                'fee_without_retirement' => 0.0043,
            ],
            [
                'tier_bottom' => 20000.01,
                'tier_top' => Fee::INFINITY,
                'fee_without_retirement' => 0.0043,
            ],

        ];

        $this->assertSame($expectedResult, $calculatedResult, '', 0.00001);
    }

    public function getMockBillingSpecManager()
    {
        $mb = $this->getMockBuilder('Wealthbot\AdminBundle\Manager\BillingSpecManager')
            ->disableOriginalConstructor();

        $mock = $mb->getMock();

        return $mock;
    }

    public function getUserManagerMock()
    {
        $mb = $this->getMockBuilder('Wealthbot\UserBundle\Manager\UserManager')
            ->disableOriginalConstructor();

        $mock = $mb->getMock();

        return $mock;
    }

    private function getMockEntityManager()
    {
        $mockEm = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(['getRepository', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockEm->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->getMockRepository()));

        return $mockEm;
    }

    private function getMockCashCalculationManager()
    {
        $mock = $this->getMockBuilder('Wealthbot\ClientBundle\Manager\CashCalculationManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function getMockPeriodManager()
    {
        $mock = $this->getMockBuilder('Wealthbot\RiaBundle\Service\Manager\PeriodManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function getMockRepository()
    {
        $mockRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

//        $mockRepository->expects($this->any())
//            ->method('findBy')
//            ->will($this->returnCallback(array($this, 'returnAdminFees')));

        return $mockRepository;
    }

    protected function getRiaUser()
    {
        $ria = new User();

        $adminBillingSpec = new BillingSpec();
        $adminBillingSpec->setName('Admin Billing Spec for RIA TEST');
        $adminBillingSpec->addAppointedUser($ria);
        $adminBillingSpec->setFees($this->returnAdminFees());
        $adminBillingSpec->setMaster(true);
        $adminBillingSpec->setType(BillingSpec::TYPE_TIER);
        $adminBillingSpec->setMinimalFee(0);

        $ria->setAppointedBillingSpec($adminBillingSpec);

        return $ria;
    }

    public function returnFees()
    {
        $a = new ArrayCollection();

        foreach ($this->feeData as $feeData) {
            $fee = new Fee();
            $fee->setTierTop($feeData['top_tier']);
            $fee->setFeeWithoutRetirement($feeData['fee_without_retirement']);

            $a->add($fee);
        }

        return $a;
    }

    public function returnAdminFees()
    {
        $a = new ArrayCollection();

        foreach ($this->adminFeeData as $feeData) {
            $fee = new Fee();
            $fee->setTierTop($feeData['top_tier']);
            $fee->setFeeWithoutRetirement($feeData['fee_without_retirement']);

            $a->add($fee);
        }

        return $a;
    }

    public function testFeeCalculator()
    {
        $feeManager = new FeeManager(
            $this->getMockEntityManager(),
            $this->getUserManagerMock(),
            $this->getMockCashCalculationManager(),
            $this->getMockPeriodManager(),
            $this->getMockBillingSpecManager()
        );

        $fees = $this->returnFees();
        $c = $feeManager->calculateFee(600000, $fees);
        $this->assertSame(16500.0, $c, 'Invalid calculated fee value (FeeManager::calculateFee): '.$c);
    }

    public function testFeeBilled()
    {
        $feeManager = new FeeManager(
            $this->getMockEntityManager(),
            $this->getUserManagerMock(),
            $this->getMockCashCalculationManager(),
            $this->getMockPeriodManager(),
            $this->getMockBillingSpecManager()
        );

        $fees = $this->returnFees();
        $fee = $feeManager->calculateFee(600000, $fees);
        $c = $feeManager->calculateFeeBilled($fee, 30, 45);

        $this->assertSame(11000.0, $c, "Invalid calculated fee billed (FeeManager::calculateFeeBilled): {$c}");
    }
}
