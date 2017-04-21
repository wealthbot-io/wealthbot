<?php

namespace Test\Model\Pas;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\Pas\Repository\AccountTwrValueRepository as AccountTwrValueRepo;
use Model\Pas\Repository\AccountTwrPeriodRepository as AccountTwrPeriodRepo;
use Model\Pas\Repository\PortfolioTwrValueRepository as PortfolioTwrValueRepo;
use Model\Pas\Repository\PortfolioTwrPeriodRepository as PortfolioTwrPeriodRepo;
use Model\Pas\Repository\ClientAccountValueRepository as ClientAccountValueRepo;
use Model\Pas\Repository\TransactionRepository as TransactionRepo;
use \Model\Pas\ClientAccountValue as ClientAccountValueModel;
use \Model\Pas\Transaction as TransactionModel;
use Wealthbot\ClientBundle\Model\SystemAccount;

class TwrCalculatorTest extends \PHPUnit_Framework_TestCase
{
    protected $curDate;

    public function setUp()
    {
        $this->curDate = new \DateTime('2014-11-29');

        $accountValues = array(
            array(
                'client_portfolio_id' => 2,
                'system_client_account_id' => 2,
                'total_value' => 101000,
                'date' => '2014-11-27'
            ),
            array(
                'client_portfolio_id' => 2,
                'system_client_account_id' => 2,
                'total_value' => 202000,
                'date' => '2014-11-28'
            ),
            array(
                'client_portfolio_id' => 2,
                'system_client_account_id' => 2,
                'total_value' => 303000,
                'date' => '2014-11-29'
            ),
            array(
                'client_portfolio_id' => 2,
                'system_client_account_id' => 2,
                'total_value' => 404000,
                'date' => '2014-11-30'
            )
        );

        $repository = new ClientAccountValueRepo();
        foreach ($accountValues as $value) {
            $model = new ClientAccountValueModel();
            $model->loadFromArray($value);
            $repository->save($model);
        }

        $transactionValues = array(
            array(
                'account_id'            => 2,
                'transaction_type_id'   => 24, // MFEE 24
                'closing_method_id'     => 6,
                'security_id'           => 11,
                'tx_date'               => '2014-11-28',
                'qty'                   => 30,
                'net_amount'            => 3000,
                'gross_amount'          => 0
            ),
            array(
                'account_id'            => 2,
                'transaction_type_id'   => 41, // DEL 41
                'closing_method_id'     => 6,
                'security_id'           => 482,
                'tx_date'               => '2014-11-28',
                'qty'                   => 40,
                'net_amount'            => 4000,
                'gross_amount'          => 0
            ),
            array(
                'account_id'            => 2,
                'transaction_type_id'   => 42, // REC 42
                'closing_method_id'     => 6,
                'security_id'           => 13,
                'tx_date'               => '2014-11-28',
                'qty'                   => 20,
                'net_amount'            => 2000,
                'gross_amount'          => 0
            ),
            array(
                'account_id'            => 2,
                'transaction_type_id'   => 42, // REC 42
                'closing_method_id'     => 6,
                'security_id'           => 13,
                'tx_date'               => '2014-11-29',
                'qty'                   => 30,
                'net_amount'            => 2000,
                'gross_amount'          => 0
            ),
            array(
                'account_id'            => 2,
                'transaction_type_id'   => 42, // REC 42
                'closing_method_id'     => 6,
                'security_id'           => 13,
                'tx_date'               => '2014-11-30',
                'qty'                   => 31,
                'net_amount'            => 2000,
                'gross_amount'          => 0
            )
        );

        $repository = new TransactionRepo();
        foreach ($transactionValues as $value) {
            $model = new TransactionModel();
            $model->loadFromArray($value);
            $repository->save($model);
        }

        $accounts[6][] = $this->getModelMock('SystemClientAccount', array(
            'id' => 2,
            'client_id' => 6,
            'account_number' => '409888117',
            'closed' => '2014-11-29',
            'status' => SystemAccount::STATUS_CLOSED
        ));

        $calculator = $this->getMockBuilder('Pas\TwrCalculator')
            ->disableOriginalConstructor()
            ->setMethods(array('getAllAccounts'))
            ->getMock();

        $calculator->expects($this->any())->method('getAllAccounts')->will($this->returnValue($accounts));
        $calculator->run('2014-11-29');
        $calculator->run('2014-11-30');
    }

    public function testGetAccountValue()
    {
        $repository = new ClientAccountValueRepo();
        $v = $repository->getBuilder()->from('client_account_values')->where('date = DATE(?)', '2014-11-29')->fetch('total_value');
        $this->assertEquals(303000,  $v);
    }

    public function testGetSumContribution()
    {
        $repository = new TransactionRepo();
        $v = $repository->sumContribution(2, $this->getModifyDate('-1 day'));
        $this->assertEquals(2000,  $v);
    }

    public function testGetSumWithdrawal()
    {
        $repository = new TransactionRepo();

        $v = $repository->sumWithdrawal(2, $this->getModifyDate('-1 day'), TransactionRepo::AMOUNT_TYPE_NET);
        $this->assertEquals(7000,  $v);

        $v = $repository->sumWithdrawal(2, $this->getModifyDate('-1 day'), TransactionRepo::AMOUNT_TYPE_GROSS);
        $this->assertEquals(4000,  $v);
    }

    public function testAccountTwrValues()
    {
        $repository = new AccountTwrValueRepo();
        $v = $repository->getBuilder()->from('account_twr_values')->where('account_number', '409888117')->where('date = DATE(?)', '2014-11-29')->fetch();

        $this->assertEquals(0.52, $this->numberFormat($v['net_value']));
        $this->assertEquals(0.50, $this->numberFormat($v['gross_value']));
    }

    public function testAccountTwrPeriods()
    {
        $repository = new AccountTwrPeriodRepo();
        $v = $repository->getBuilder()->from('account_twr_periods')->where('account_number', '409888117')->fetch();

        $this->assertEquals(52.47, $this->numberFormat($v['net_mtd']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_qtd']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_ytd']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_yr1']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_yr3']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_since_inception']));

        $this->assertEquals(50.99, $this->numberFormat($v['gross_mtd']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_qtd']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_ytd']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_yr1']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_yr3']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_since_inception']));
    }

    public function testPortfolioTwrValues()
    {
        $repository = new PortfolioTwrValueRepo();
        $v = $repository->getBuilder()->from('portfolio_twr_values')->where('client_id', 6)->where('date = DATE(?)', '2014-11-29')->fetch();

        $this->assertEquals(0.52, $this->numberFormat($v['net_value']));
        $this->assertEquals(0.50, $this->numberFormat($v['gross_value']));
    }

    public function testPortfolioTwrPeriods()
    {
        $repository = new PortfolioTwrPeriodRepo();
        $v = $repository->getBuilder()->from('portfolio_twr_periods')->where('client_id', 6)->fetch();

        $this->assertEquals(52.47, $this->numberFormat($v['net_mtd']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_qtd']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_ytd']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_yr1']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_yr3']));
        $this->assertEquals(52.47, $this->numberFormat($v['net_since_inception']));

        $this->assertEquals(50.99, $this->numberFormat($v['gross_mtd']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_qtd']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_ytd']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_yr1']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_yr3']));
        $this->assertEquals(50.99, $this->numberFormat($v['gross_since_inception']));
    }

    public function testAccountClosed()
    {
        $repository = new AccountTwrValueRepo();
        $v = $repository->getBuilder()->from('account_twr_values')->where('account_number', '409888117')->where('date = DATE(?)', '2014-11-30')->fetch();
        $this->assertFalse($v);
    }

    /**
     * @param string $period
     * @return mixed
     */
    protected function getModifyDate($period)
    {
        $date = clone $this->curDate;

        return $date->modify($period);
    }

    protected function numberFormat($value)
    {
        $value = (float) $value;

        return floor(($value * 100)) / 100;
    }

    /**
     * @param string $model
     * @param array $data
     * @return object
     */
    private function getModelMock($model, array $data = array())
    {
        $mock = $this->getMock("Model\\Pas\\{$model}", null);
        $mock->loadFromArray($data);

        return $mock;
    }
}