<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.06.13
 * Time: 18:00
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Tests\Model;

use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Model\CeModel;
use Wealthbot\AdminBundle\Model\CeModelEntity;
use Wealthbot\ClientBundle\Model\PortfolioInformation;

class PortfolioInformationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  PortfolioInformation */
    private $portfolioInformation;

    public function setUp()
    {
        $subclassMock1 = $this->getMock('Wealthbot\AdminBundle\Entity\Subclass', ['getId']);
        $subclassMock1->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $subclassMock1->setName('Subclass1');
        $subclassMock1->setExpectedPerformance(0.5);

        $subclassMock2 = $this->getMock('Wealthbot\AdminBundle\Entity\Subclass', ['getId']);
        $subclassMock2->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));
        $subclassMock2->setName('Subclass2');
        $subclassMock2->setExpectedPerformance(0.7);

        $entity1 = new CeModelEntity();
        $security1 = new Security();
        $security1->setExpenseRatio(0.6);
        $securityAssignment1 = new SecurityAssignment();
        $securityAssignment1->setSecurity($security1);
        $securityAssignment1->setSubclass($subclassMock1);
        $entity1->setSubclass($subclassMock1);
        $entity1->setPercent(40);
        $entity1->setSecurityAssignment($securityAssignment1);

        $entity2 = new CeModelEntity();
        $security2 = new Security();
        $security2->setExpenseRatio(0.3);
        $securityAssignment2 = new SecurityAssignment();
        $securityAssignment2->setSecurity($security2);
        $securityAssignment2->setSubclass($subclassMock2);
        $entity2->setSubclass($subclassMock2);
        $entity2->setPercent(60);
        $entity2->setSecurityAssignment($securityAssignment2);

        $entity3 = new CeModelEntity();
        $security3 = new Security();
        $security3->setExpenseRatio(0.7);
        $securityAssignment3 = new SecurityAssignment();
        $securityAssignment3->setSecurity($security3);
        $securityAssignment3->setSubclass($subclassMock1);
        $entity3->setSubclass($subclassMock1);
        $entity3->setPercent(100);
        $entity3->setSecurityAssignment($securityAssignment3);
        $entity3->setIsQualified(true);

        $model = new CeModel('Model');
        $model->addModelEntity($entity1);
        $model->addModelEntity($entity2);
        $model->addModelEntity($entity3);
        $model->setLowMarketReturn(0.6);
        $model->setGenerousMarketReturn(1.3);
        $model->setForecast(0);

        $this->portfolioInformation = new PortfolioInformation();
        $this->portfolioInformation->setModel($model);
    }

    public function testSetIsQualified()
    {
        $portfolioInformation = new PortfolioInformation();
        $this->assertFalse($portfolioInformation->getIsQualifiedModel());

        $portfolioInformation->setIsQualifiedModel(true);
        $this->assertTrue($portfolioInformation->getIsQualifiedModel());
    }

    public function testGetNonQualifiedModelEntities()
    {
        $entities = $this->portfolioInformation->getNonQualifiedModelEntities();

        $this->assertSame(2, count($entities));
    }

    public function testGetQualifiedModelEntities()
    {
        $entities = $this->portfolioInformation->getQualifiedModelEntities();
        $this->assertSame(1, count($entities));
    }

    public function testGetModelEntities()
    {
        $this->assertSame(2, count($this->portfolioInformation->getModelEntities()));

        $this->portfolioInformation->setIsQualifiedModel(true);
        $this->assertSame(1, count($this->portfolioInformation->getModelEntities()));
    }

    public function testGetModelEntitiesAsJson()
    {
        $entities = json_decode($this->portfolioInformation->getModelEntitiesAsJson());
        $percent = 0;

        foreach ($entities as $entity) {
            $percent += $entity->data;
        }

        $this->assertSame(100, $percent, 'Sum of non qualified entities subclasses percent must be 100%.');

        $this->portfolioInformation->setIsQualifiedModel(true);
        $entities = json_decode($this->portfolioInformation->getModelEntitiesAsJson());
        $percent = 0;

        foreach ($entities as $entity) {
            $percent += $entity->data;
        }

        $this->assertSame(100, $percent, 'Sun of qualified entities subclasses percent must be 100%.');
    }

    public function testGetFundExpenses()
    {
        $this->portfolioInformation->setIsQualifiedModel(false);
        $this->assertSame(0.42, $this->portfolioInformation->getFundExpenses());

        $this->portfolioInformation->setIsQualifiedModel(true);
        $this->assertSame(0.7, $this->portfolioInformation->getFundExpenses());
    }

    public function testGetInvestmentMarket()
    {
        $investmentMarket = ((40 * 0.5) + (60 * 0.7)) / 100; // 0.62
        $this->assertSame(
            $investmentMarket,
            $this->portfolioInformation->getInvestmentMarket(),
            'Invalid investment market for non qualified entities.'
        );

        $this->portfolioInformation->setIsQualifiedModel(true);
        $investmentMarket = (100 * 0.5) / 100; // 0.5
        $this->assertSame(
            $investmentMarket,
            $this->portfolioInformation->getInvestmentMarket(),
            'Invalid investment market for qualified entities.'
        );
    }

    public function testGetGenerousInvestmentMarket()
    {
        $modelGenerousMarketReturn = $this->portfolioInformation->getModel()->getGenerousMarketReturn();

        $generousInvestmentMarket = round(0.62 * ($modelGenerousMarketReturn ? $modelGenerousMarketReturn : 1.2), 2);
        $this->assertSame(
            $generousInvestmentMarket,
            $this->portfolioInformation->getGenerousInvestmentMarket(),
            'Invalid generous investment market for non qualified entities.'
        );

        $this->portfolioInformation->setIsQualifiedModel(true);
        $generousInvestmentMarket = round(0.5 * ($modelGenerousMarketReturn ? $modelGenerousMarketReturn : 1.2), 2);
        $this->assertSame(
            $generousInvestmentMarket,
            $this->portfolioInformation->getGenerousInvestmentMarket(),
            'Invalid generous investment market for qualified entities.'
        );
    }

    public function testGetAverageInvestmentMarket()
    {
        $averageInvestmentMarket = round($this->portfolioInformation->getInvestmentMarket() * 1, 2);
        $this->assertSame($averageInvestmentMarket, $this->portfolioInformation->getAverageInvestmentMarket());

        $this->portfolioInformation->setIsQualifiedModel(true);
        $averageInvestmentMarket = round($this->portfolioInformation->getInvestmentMarket() * 1, 2);
        $this->assertSame($averageInvestmentMarket, $this->portfolioInformation->getAverageInvestmentMarket());
    }

    public function testGetLowInvestmentMarket()
    {
        $modelLowMarketReturn = $this->portfolioInformation->getModel()->getLowMarketReturn();

        $lowInvestmentMarket = round($this->portfolioInformation->getInvestmentMarket() * ($modelLowMarketReturn ? $modelLowMarketReturn : 0.8), 2);
        $this->assertSame(
            $lowInvestmentMarket,
            $this->portfolioInformation->getLowInvestmentMarket(),
            'Invalid low investment market for non qualified entities.'
        );

        $this->portfolioInformation->setIsQualifiedModel(true);
        $lowInvestmentMarket = round($this->portfolioInformation->getInvestmentMarket() * ($modelLowMarketReturn ? $modelLowMarketReturn : 0.8), 2);
        $this->assertSame(
            $lowInvestmentMarket,
            $this->portfolioInformation->getLowInvestmentMarket(),
            'Invalid low investment market qualified entities.'
        );
    }

    public function testGetForecast()
    {
        $forecast = $this->portfolioInformation->getModel()->getForecast();
        $this->assertSame($forecast, $this->portfolioInformation->getForecast());
    }
}
