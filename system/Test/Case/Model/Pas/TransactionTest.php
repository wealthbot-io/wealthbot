<?php

namespace Test\Model\Pas;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testCreate()
    {
        $transaction = $this->getMockBuilder('Pas\Transaction')
            ->disableOriginalConstructor()
            ->setMethods(array('getSecurity', 'getClosingMethod', 'getTransactionType', 'getRepository'))
            ->getMock()
        ;

        $securityModel = $this->getModelMock('Security', array('id' => 13));
        $closingMethodModel = $this->getModelMock('ClosingMethod', array('id' => 6));
        $transactionTypeModel = $this->getModelMock('TransactionType', array('id' => 1));
        $accountModel = $this->getModelMock('SystemClientAccount', array('id' => 4));

        $transaction->expects($this->once())->method('getSecurity')->will($this->returnValue($securityModel));
        $transaction->expects($this->once())->method('getClosingMethod')->will($this->returnValue($closingMethodModel));
        $transaction->expects($this->once())->method('getTransactionType')->will($this->returnValue($transactionTypeModel));

        $transaction
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repository) {
                echo $repository;
                return $repository === 'Transaction' ? new \Model\Pas\Repository\TransactionRepository() : null;
            }));

        $result = $transaction->create($accountModel, array(
            'advisor_code'      => 'ABC',
            'account_number'    => 916985328,
            'transaction_code'  => 'BUY',
            'symbol'            => 'RWX',
            'security_code'     => 'EQ',
            'tx_date'           => '2011-12-05',
            'net_amount'        => 4968.23,
            'qty'               => 146,
            'gross_amount'      => '',
            'settle_date'       => '2011-12-08',
            'notes'             => '|SPDR DJ WILSHRE INTL REAL ESTATE BUY TRADE 146 @34.029|TRBY',
            'closing_method'    => '',
            'accrued_interest'  => ''
        ));

        $this->assertNotNull($result);
    }

    public function testUpdateFeeCollected()
    {
        $accountModel = $this->getModelMock('SystemClientAccount', array('id' => 4));
        $transactionModel = $this->getModelMock('Transaction', array(
            'account_number'    => 744888386,
            'transaction_code'  => 'MFEE',
            'symbol'            => 'DISVX',
            'security_code'     => 'MU',
            'tx_date'           => '2013-04-02',
            'net_amount'        => 977.99,
            'qty'               => 0
        ));

        $transaction = $this
            ->getMockBuilder('Pas\Transaction')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository'))
            ->getMock()
        ;

        $transaction
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repository) {
                return $repository === 'BillItem' ? new \Model\Pas\Repository\BillItemRepository() : null;
            }));

        $this->assertTrue((bool) $transaction->updateFeeCollected($accountModel, $transactionModel));
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