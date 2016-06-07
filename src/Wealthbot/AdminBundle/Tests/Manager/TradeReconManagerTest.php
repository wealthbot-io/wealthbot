<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 01.05.14
 * Time: 10:57.
 */

namespace Wealthbot\AdminBundle\Tests\Manager;

use Wealthbot\AdminBundle\Manager\TradeReconManager;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;

class TradeReconManagerTest extends ExtendedWebTestCase
{
    /* @var TradeReconManager $tradeReconManager */
    protected $tradeReconManager;

    protected $ria,
        $testResult,
        $emptyArray = [],
        $wasRebalancerDiffIsSetData = [
            0 => [
                'ria' => 'Mortal Kombat Advisors',
                'custodian' => 'TD Ameritrade',
                'last_name' => 'Kang',
                'first_name' => 'Liu',
                'acct_number' => '480888811',
                'symbol' => 'Vanguard Interm-Tm Corp Bd Idx ETF',
                'submitted_action' => 'buy',
                'executed_action' => '',
                'submitted_amount' => 0.0,
                'executed_amount' => '',
                'error' => true,

                ],
        ],
        $transactionNotExecutedData = [

            ],
        $correctWorkData = [
            0 => [
                'ria' => 'Wealthbot.io - RIA',
                'custodian' => 'TD Ameritrade',
                'last_name' => 'Johnny',
                'first_name' => 'Cage',
                'acct_number' => '744888385',
                'symbol' => 'SPDR Dow Jones Intl Real Estate',
                'submitted_action' => '',
                'executed_action' => 'Buy',
                'submitted_amount' => '',
                'executed_amount' => 3600.12,
                'error' => true,
            ],
        ],
        $notPlannedTransactionData = [
            0 => [
                    'ria' => 'Wealthbot.io - RIA',
                    'custodian' => 'TD Ameritrade',
                    'last_name' => 'Johnny',
                    'first_name' => 'Cage',
                    'acct_number' => '744888385',
                    'symbol' => 'SPDR Dow Jones Intl Real Estate',
                    'submitted_action' => '',
                    'executed_action' => 'Buy',
                    'submitted_amount' => '',
                    'executed_amount' => 390.49,
                    'error' => true,
                ],
            ]
    ;

    public function setUp()
    {
        parent::setUp();
        $this->ria = $this->authenticateUser('johnny@wealthbot.io', ['ROLE_RIA'])->getRia();
        $this->tradeReconManager = $this->container->get('wealthbot_admin.trade_recon.manager');
    }

    /**
     * Test result should be an empty array because there is no any data for this date.
     */
    public function testEmptyData()
    {
        $date = '2013-02-11';
        $this->testResult = $this->tradeReconManager->getValues(new \DateTime($date), new \DateTime($date), $this->ria, '');
        $this->assertSame($this->emptyArray, $this->testResult, 'Test for empty data failed');
    }

    /**
     * This test imitates situation when transaction was planned and executed, but result of transaction for some reason significantly differs from expected.
     * Result of such operation should contain the following fields and values:.
     *
     * 'submitted_action' => not empty string ('buy' or 'sell'),
     * 'executed_action' => not empty string ('Buy' or 'Sell'),
     * 'submitted_amount' => float number > 0,
     * 'executed_amount' => float number > 0,
     * 'error' => true
     */
    public function testWasRebalancerDiffIsSet()
    {
        $date = '2013-02-13';
        $this->testResult = $this->tradeReconManager->getValues(new \DateTime($date), new \DateTime($date), $this->ria, 'Kang, Liu');
        $this->assertSame($this->wasRebalancerDiffIsSetData, $this->testResult, 'Test for Rebalancer Diff failed', 0.001);
    }

    /**
     * This test imitates transaction when operation was planned in Rebalancer, but for some reason it was not executed.
     * Result of such operation should contain the following fields and values:.
     *
     * 'submitted_action' => not empty string ('buy' or 'sell'),
     * 'executed_action' => '',
     * 'submitted_amount' => float number > 0,
     * 'executed_amount' => '',
     * 'error' => true
     */
    public function testNotExecutedTransaction()
    {
        $date = '2013-02-14';
        $this->testResult = $this->tradeReconManager->getValues(new \DateTime($date), new \DateTime($date), $this->ria, 'Kang, Liu');
        $this->assertSame($this->transactionNotExecutedData, $this->testResult, 'Test for not executed transaction failed');
    }

    /**
     * This test imitates situation when everything goes OK: transaction was planned and executed, and result of transaction is very similar to what is expected.
     * Result of such operation should contain the following fields and values:.
     *
     * 'submitted_action' => not empty string ('buy' or 'sell'),
     * 'executed_action' => not empty string ('Buy' or 'Sell'),
     * 'submitted_amount' => float number > 0,
     * 'executed_amount' => float number > 0,
     * 'error' => false
     */
    public function testCorrectWork()
    {
        $date = '2013-02-16';
        $this->testResult = $this->tradeReconManager->getValues(new \DateTime($date), new \DateTime($date), $this->ria, '');
        $this->assertSame($this->correctWorkData, $this->testResult, 'Test for correct work failed', 0.001);
    }

    /**
     * This test imitates situation when site gets an unexpected transaction from Custodian: this transaction was not planned by Rebalancer.
     * Result of such operation should contain the following fields and values:.
     *
     * 'submitted_action' => '',
     * 'executed_action' => not empty string ('Buy' or 'Sell'),
     * 'submitted_amount' => '',
     * 'executed_amount' => float number > 0,
     * 'error' => true
     */
    public function testNotPlannedTransaction()
    {
        $date = '2013-03-22';
        $this->testResult = $this->tradeReconManager->getValues(new \DateTime($date), new \DateTime($date), $this->ria, 'Kang, Liu');
        $this->assertSame($this->notPlannedTransactionData, $this->testResult, 'Test for empty data failed', 0.001);
    }
}
