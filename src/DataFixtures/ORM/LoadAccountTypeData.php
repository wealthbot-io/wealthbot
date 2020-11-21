<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.01.13
 * Time: 13:15
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\AccountGroup;
use App\Entity\AccountGroupType;
use App\Entity\AccountType;

class LoadAccountTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    private $groups = [
        AccountGroup::GROUP_FINANCIAL_INSTITUTION,
        AccountGroup::GROUP_DEPOSIT_MONEY,
        AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT,
        AccountGroup::GROUP_EMPLOYER_RETIREMENT,
    ];

    private $types = [
        [
            'name' => '401(b)',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => '401(k)',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT, AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => '401(k) Roth',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT, AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => '457',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => '403(b)',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT, AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'Defined Benefit Plan',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'Employee Stock Option Plan',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'Employee Stock Ownership Plan (ESOPs)',
            'groups' => [],
        ],
        [
            'name' => 'Inactive Retirement Account',
            'groups' => [],
        ],
        [
            'name' => 'Personal Account',
            'groups' => [AccountGroup::GROUP_FINANCIAL_INSTITUTION, AccountGroup::GROUP_DEPOSIT_MONEY],
        ],
        [
            'name' => 'Joint Account',
            'groups' => [AccountGroup::GROUP_FINANCIAL_INSTITUTION, AccountGroup::GROUP_DEPOSIT_MONEY],
        ],
        [
            'name' => 'Money Purchase Plan',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'Profit Sharing Plan',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'Roth IRA',
            'groups' => [AccountGroup::GROUP_FINANCIAL_INSTITUTION, AccountGroup::GROUP_DEPOSIT_MONEY],
        ],
        [
            'name' => 'SAR-SEP',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'SEP',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'SEP IRA',
            'groups' => [AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'SIMPLE IRA',
            'groups' => [AccountGroup::GROUP_EMPLOYER_RETIREMENT, AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT],
        ],
        [
            'name' => 'Traditional IRA',
            'groups' => [AccountGroup::GROUP_FINANCIAL_INSTITUTION, AccountGroup::GROUP_DEPOSIT_MONEY],
        ],
        [
            'name' => 'Rollover IRA',
            'groups' => [AccountGroup::GROUP_FINANCIAL_INSTITUTION],
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach ($this->groups as $groupName) {
            $groupObject = new AccountGroup();
            $groupObject->setName($groupName);

            $manager->persist($groupObject);
            $this->addReference('client-account-group-'.$groupName, $groupObject);
        }

        foreach ($this->types as $key => $item) {
            $typeObject = new AccountType();
            $typeObject->setName($item['name']);

            $manager->persist($typeObject);
            $this->addReference('client-account-type-'.($key + 1), $typeObject);

            foreach ($item['groups'] as $groupKey) {
                $groupTypeObject = new AccountGroupType();
                $groupTypeObject->setGroup($this->getReference('client-account-group-'.$groupKey));
                $groupTypeObject->setType($this->getReference('client-account-type-'.($key + 1)));

                $manager->persist($groupTypeObject);
                $this->addReference('client-account-group-type-'.$groupKey.'-'.($key + 1), $groupTypeObject);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
