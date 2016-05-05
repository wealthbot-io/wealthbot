<?php

namespace Wealthbot\ClientBundle\Tests\Manager;

use Wealthbot\ClientBundle\Manager\ClientAllocationValuesManager;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;

class ClientAllocationValuesManagerTest extends ExtendedWebTestCase
{
    /** @var ClientAllocationValuesManager */
    protected $allocationValuesManager;

    /** @var  User */
    protected $client;

    protected $actualData = [
         [
            'label' => 'Subclass 1',
            'price' => 61.58,
            'amount' => '1000',
            'color' => '#cfe932',
            'data' => 9.397,
        ],
         [
            'label' => 'Large',
            'price' => 61.58,
            'amount' => '2000',
            'color' => '#f46b8e',
            'data' => 18.795067046703,
            'subclass_id' => 12,
        ],
        [
            'label' => 'International REITS',
            'price' => 33.41,
            'amount' => '7641.09',
            'color' => '#bd25a8',
            'data' => 71.807399429946,
            'subclass_id' => 18,
        ],
    ];

    protected $targetData = [
        0 => [
            'label' => 'Large',
            'data' => 4.8,
            'color' => '#f46b8e',
        ],
        1 => [
            'label' => 'Large Value',
            'data' => 7.2,
            'color' => '#0b4e0f',
        ],
        2 => [
            'label' => 'Small',
            'data' => 4.8,
            'color' => '#eb8c55',
        ],
        3 => [
            'label' => 'Small Value',
            'data' => 7.2,
            'color' => '#9e2f48',
        ],
        4 => [
            'label' => 'Commodities',
            'data' => 3.0,
            'color' => '#2b0c53',
        ],
        5 => [
            'label' => 'REITS',
            'data' => 4.5,
            'color' => '#e6fb98',
        ],
        6 => [
            'label' => 'International REITS',
            'data' => 4.5,
            'color' => '#bd25a8',
        ],
        7 => [
            'label' => 'Large',
            'data' => 4.5,
            'color' => '#bf9175',
        ],
        8 => [
            'label' => 'Large Value',
            'data' => 5.4,
            'color' => '#ad1929',
        ],
        9 => [
            'label' => 'Small',
            'data' => 4.5,
            'color' => '#7895e8',
        ],
        10 => [
            'label' => 'Small Value',
            'data' => 5.4,
            'color' => '#20dde4',
        ],
        11 => [
            'label' => 'Emerging Markets',
            'data' => 4.2,
            'color' => '#9166d5',
        ],
        12 => [
            'label' => 'Intermediate',
            'data' => 40.0,
            'color' => '#4dc8a1',
        ],
    ];

    protected $tableData = [
        [
            'subclassTitle' => 'Subclass 1',
            'targetPercent' => 0,
            'targetValue' => 0,
            'currentPercent' => 9.3975335233515,
            'color' => '#cfe932',
            'currentValue' => '1000',
            'dollarVariance' => '1000',
            'percentVariance' => 9.3975335233515,
        ],
        [
            'subclassTitle' => 'Large',
            'currentPercent' => 18.795067046703,
            'currentValue' => '2000',
            'dollarVariance' => 1489.22768,
            'percentVariance' => 13.995067046703,
            'targetPercent' => 4.8,
            'targetValue' => 510.77232,
            'color' => '#f46b8e',
        ],
         [
            'subclassTitle' => 'International REITS',
            'currentPercent' => 71.807399429946,
            'currentValue' => '7641.09',
            'dollarVariance' => 7162.24095,
            'percentVariance' => 67.307399429946,
            'targetPercent' => 4.5,
            'targetValue' => 478.84905,
            'color' => '#bd25a8',
        ],
        [
            'subclassTitle' => 'Large Value',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -766.15848,
            'percentVariance' => -7.2,
            'targetPercent' => 7.2,
            'targetValue' => 766.15848,
        ],
        [
            'subclassTitle' => 'Small',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -510.77232,
            'percentVariance' => -4.8,
            'targetPercent' => 4.8,
            'targetValue' => 510.77232,
        ],
        [
            'subclassTitle' => 'Small Value',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -766.15848,
            'percentVariance' => -7.2,
            'targetPercent' => 7.2,
            'targetValue' => 766.15848,
        ],
        [
            'subclassTitle' => 'Commodities',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -319.2327,
            'percentVariance' => -3.0,
            'targetPercent' => 3.0,
            'targetValue' => 319.2327,
        ],
        [
            'subclassTitle' => 'REITS',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -478.84905,
            'percentVariance' => -4.5,
            'targetPercent' => 4.5,
            'targetValue' => 478.84905,
        ],
        [
            'subclassTitle' => 'Large',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -478.84905,
            'percentVariance' => -4.5,
            'targetPercent' => 4.5,
            'targetValue' => 478.84905,
        ],
        [
            'subclassTitle' => 'Large Value',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -574.61886,
            'percentVariance' => -5.4,
            'targetPercent' => 5.4,
            'targetValue' => 574.61886,
        ],
        [
            'subclassTitle' => 'Small',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -478.84905,
            'percentVariance' => -4.5,
            'targetPercent' => 4.5,
            'targetValue' => 478.84905,
        ],
        [
            'subclassTitle' => 'Small Value',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -574.61886,
            'percentVariance' => -5.4,
            'targetPercent' => 5.4,
            'targetValue' => 574.61886,
        ],
        [
            'subclassTitle' => 'Emerging Markets',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -446.92578,
            'percentVariance' => -4.2,
            'targetPercent' => 4.2,
            'targetValue' => 446.92578,
        ],
        [
            'subclassTitle' => 'Intermediate',
            'currentPercent' => 0,
            'currentValue' => 0,
            'dollarVariance' => -4256.436,
            'percentVariance' => -40.0,
            'targetPercent' => 40.0,
            'targetValue' => 4256.436,
        ],
    ];

    protected $lastRow = [
        'targetPercent' => 100.0,
        'targetValue' => 10641.09,
        'currentPercent' => 100.0,
        'currentValue' => 10641.09,
    ];

    protected $totalAmount = 10641.09;

    public function setUp()
    {
        parent::setUp();
        $this->allocationValuesManager = $this->container->get('wealthbot_client.client_allocation_values.manager');
    }

    public function testGetValues()
    {
        $this->client = $this->authenticateUser('liu@wealthbot.io', ['ROLE_CLIENT']);
        $result = $this->allocationValuesManager->getValues($this->client);

        unset($result['actualData'][0]['subclass_id']);

        foreach ($result['targetData'] as $key => $targetData) {
            unset($result['targetData'][$key]['subclass_id']);
        }

        $this->assertSame($this->actualData, $result['actualData'], 'Incorrect actual data.', 0.001);
        $this->assertSame($this->targetData, $result['targetData'], 'Incorrect target data.', 0.001);
        $this->assertSame($this->tableData, array_values($result['tableData']), 'Incorrect table data.', 0.001);
        $this->assertSame($this->lastRow, $result['lastRow'], 'Incorrect data in summary table row.', 0.001);
        $this->assertSame($this->totalAmount, $result['totalAmount'], 'Incorrect total portfolio amount.');
    }
}
