<?php

namespace Test\Model\WealthbotRebalancer;


use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\AccountCollection;
use Model\WealthbotRebalancer\Subclass;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class AccountCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  AccountCollection */
    private $collection;

    public function setUp()
    {
        $data = array(
            array(
                'id' => 1,
                'type' => Account::TYPE_PERSONAL_INVESTMENT,
                'securities' => array(
                    array('id' => 1),
                    array('id' => 2),
                    array('id' => 3),
                    array('id' => 4)
                )
            ),
            array(
                'id' => 2,
                'type' => Account::TYPE_ROTH_IRA,
                'securities' => array(
                    array('id' => 1),
                    array('id' => 2),
                    array('id' => 3),
                    array('id' => 4)
                )
            ),
            array(
                'id' => 15,
                'type' => Account::TYPE_ROTH_IRA,
                'securities' => array(
                    array('id' => 1),
                    array('id' => 2),
                    array('id' => 3),
                    array('id' => 4)
                )
            ),
            array(
                'id' => 3,
                'type' => Account::TYPE_JOINT_INVESTMENT,
                'securities' => array(
                    array('id' => 1),
                    array('id' => 2),
                    array('id' => 3),
                    array('id' => 4)
                )
            ),
            array(
                'id' => 4,
                'type' => Account::TYPE_TRADITIONAL_IRA,
                'securities' => array(
                    array('id' => 1),
                    array('id' => 2),
                    array('id' => 3),
                    array('id' => 4)
                )
            )
        );

        $this->collection = $this->getMock('Model\WealthbotRebalancer\AccountCollection', null);
        foreach ($data as $values) {
            $account = $this->getMock('Model\WealthbotRebalancer\Account', null);
            $account->loadFromArray($values);

            $this->collection->add($account);
        }
    }

    public function testGetAccountForBuySubclass()
    {
        /** @var Subclass $subclass */
        $subclass = $this->getMock('Model\WealthbotRebalancer\Subclass', null);
        $subclass->setAccountType(Subclass::ACCOUNT_TYPE_ROTH_IRA);

        $security = $this->getMock('Model\WealthbotRebalancer\Security', null);
        $security->setId(1);

        $subclass->setSecurity($security);

        $account = $this->collection->getAccountForBuySubclass($subclass);
        $this->assertEquals(2, $account->getId());

        $this->collection->offsetUnset(2);
        $account = $this->collection->getAccountForBuySubclass($subclass);
        $this->assertEquals(15, $account->getId());

        $account->getSecurities()->offsetUnset(1);
        $account = $this->collection->getAccountForBuySubclass($subclass);
        $this->assertEquals(15, $account->getId());

        $this->collection->offsetUnset(15);
        $account = $this->collection->getAccountForBuySubclass($subclass);
        $this->assertEquals(4, $account->getId());
    }

    public function testGetAccountForSellSubclass()
    {
        /** @var Subclass $subclass */
        $subclass = $this->getMock('Model\WealthbotRebalancer\Subclass', null);
        $subclass->setAccountType(Subclass::ACCOUNT_TYPE_TAXABLE);

        $security = $this->getMock('Model\WealthbotRebalancer\Security', null);
        $security->setId(1);

        $subclass->setSecurity($security);

        $account = $this->collection->getAccountForSellSubclass($subclass);
        $this->assertEquals(2, $account->getId());

        $this->collection->offsetUnset(2);
        $account = $this->collection->getAccountForSellSubclass($subclass, true);
        $this->assertEquals(15, $account->getId());

        $account->getSecurities()->offsetUnset(1);
        $account = $this->collection->getAccountForSellSubclass($subclass);
        $this->assertEquals(4, $account->getId());

        $this->collection->offsetUnset(4);
        $account = $this->collection->getAccountForSellSubclass($subclass);
        $this->assertEquals(1, $account->getId());
    }

    public function testGetAccountForBuySubclassTraditionalIRA()
    {
        /** @var Subclass $subclass */
        $subclass = $this->getMock('Model\WealthbotRebalancer\Subclass', null);
        $subclass->setAccountType(Subclass::ACCOUNT_TYPE_TRADITIONAL_IRA);

        $security = $this->getMock('Model\WealthbotRebalancer\Security', null);
        $security->setId(2);

        $subclass->setSecurity($security);

        $account = $this->collection->getAccountForBuySubclass($subclass);
        $this->assertEquals(4, $account->getId());
    }
}
 