<?php

namespace Test\Model\Pas;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\Pas\Repository\LotRepository;
use Wealthbot\ClientBundle\Entity\Lot as WealthbotLot;

class LotTest extends \PHPUnit_Framework_TestCase
{
    protected $repository;

    public function setUp()
    {
        $this->repository = new LotRepository();
    }

    public function testCreateLot()
    {
        $collection = array(
            array(
                'transaction_code'  => 'BUY',
                'symbol'            => 'BSV',
                'tx_date'           => '2014-04-03',
                'net_amount'        => 4072,
                'gross_amount'      => '',
                'qty'               => 50,
            ),
            array(
                'transaction_code'  => 'SELL',
                'symbol'            => 'BSV',
                'tx_date'           => '2014-04-04',
                'net_amount'        => 4072,
                'gross_amount'      => '',
                'qty'               => 44,
            ),
            array(
                'transaction_code'  => 'SELL',
                'symbol'            => 'BSV',
                'tx_date'           => '2014-04-05',
                'net_amount'        => 490,
                'gross_amount'      => '',
                'qty'               => 6,
            )
        );

        $securityModel = $this->getModelMock('Security', array('id' => 18));
        $accountModel = $this->getModelMock('SystemClientAccount', array('id' => 1));

        $lot = $this->getMockBuilder('Pas\Lot')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository'))
            ->getMock()
        ;

        foreach ($collection as $data) {
            $lot->process($accountModel, $securityModel, $data);
        }

        $parameters = [
            'date_to' => '2014-04-05',
            'date_from' => '2014-04-03'
        ];

        $this->assertCount(4, $this->repository->findAllBy($parameters));
    }

    public function testPartialLots()
    {
        $results = [];

        $parameters = [
            'date_to' => '2014-04-05',
            'date_from' => '2014-04-03'
        ];

        foreach ($this->repository->findAllBy($parameters) as $lot) {
            $results[] = array(
                'quantity' => $lot->getQuantity(),
                'amount'   => $lot->getAmount(),
                'status'   => $lot->getStatus()
            );
        }

        $this->assertEquals(array(
            array(
                'quantity' => '50.0',
                'amount'   => '4072.0',
                'status'   => WealthbotLot::LOT_DIVIDED
            ),
            array(
                'quantity' => 44,
                'amount'   => 4072,
                'status'   => WealthbotLot::LOT_CLOSED
            ),
            array(
                'quantity' => '6.0',
                'amount'   => 488.64,
                'status'   => WealthbotLot::LOT_INITIAL
            ),
        ), $results);
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