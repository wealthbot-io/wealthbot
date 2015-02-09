<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.01.13
 * Time: 13:15
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\AccountGroupType;
use Wealthbot\ClientBundle\Entity\AccountType;

class LoadAccountTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    private $groups = array(
        AccountGroup::GROUP_FINANCIAL_INSTITUTION,
        AccountGroup::GROUP_DEPOSIT_MONEY,
        AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT,
        AccountGroup::GROUP_EMPLOYER_RETIREMENT
    );

    private $types = array(
        array(
            'name' => '401(b)',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => '401(k)',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT, AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => '401(k) Roth',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT, AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => '457',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => '403(b)',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT, AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT),
        ),
        array(
            'name' => 'Defined Benefit Plan',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => 'Employee Stock Option Plan',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => 'Employee Stock Ownership Plan (ESOPs)',
            'groups' => array()
        ),
        array(
            'name' => 'Inactive Retirement Account',
            'groups' => array()
        ),
        array(
            'name' => 'Personal Account',
            'groups' => array(AccountGroup::GROUP_FINANCIAL_INSTITUTION, AccountGroup::GROUP_DEPOSIT_MONEY)
        ),
        array(
            'name' => 'Joint Account',
            'groups' => array(AccountGroup::GROUP_FINANCIAL_INSTITUTION, AccountGroup::GROUP_DEPOSIT_MONEY)
        ),
        array(
            'name' => 'Money Purchase Plan',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => 'Profit Sharing Plan',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => 'Roth IRA',
            'groups' => array(AccountGroup::GROUP_FINANCIAL_INSTITUTION, AccountGroup::GROUP_DEPOSIT_MONEY)
        ),
        array(
            'name' => 'SAR-SEP',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => 'SEP',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => 'SEP IRA',
            'groups' => array(AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => 'SIMPLE IRA',
            'groups' => array(AccountGroup::GROUP_EMPLOYER_RETIREMENT, AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)
        ),
        array(
            'name' => 'Traditional IRA',
            'groups' => array(AccountGroup::GROUP_FINANCIAL_INSTITUTION, AccountGroup::GROUP_DEPOSIT_MONEY)
        ),
        array(
            'name' => 'Rollover IRA',
            'groups' => array(AccountGroup::GROUP_FINANCIAL_INSTITUTION)
        )
    );

    public function load(ObjectManager $manager)
    {
        foreach ($this->groups as $groupName) {
            $groupObject = new AccountGroup();
            $groupObject->setName($groupName);

            $manager->persist($groupObject);
            $this->addReference('client-account-group-' . $groupName, $groupObject);
        }

        foreach ($this->types as $key => $item) {
            $typeObject = new AccountType();
            $typeObject->setName($item['name']);

            $manager->persist($typeObject);
            $this->addReference('client-account-type-' . ($key + 1), $typeObject);

            foreach ($item['groups'] as $groupKey) {
                $groupTypeObject = new AccountGroupType();
                $groupTypeObject->setGroup($this->getReference('client-account-group-' . $groupKey));
                $groupTypeObject->setType($this->getReference('client-account-type-' . ($key + 1)));

                $manager->persist($groupTypeObject);
                $this->addReference('client-account-group-type-' . $groupKey . '-' . ($key + 1), $groupTypeObject);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}