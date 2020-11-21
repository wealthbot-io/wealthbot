<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\Security;
use App\Entity\SecurityAssignment;
use App\Entity\SecurityType;
use App\Entity\AccountGroup;
use App\Entity\AccountGroupType;
use App\Entity\AccountOutsideFund;
use App\Entity\ClientAccount;
use App\Entity\ClientAccountOwner;
use App\Entity\ClientSettings;
use App\Entity\SystemAccount;
use App\Manager\RiskToleranceManager;
use App\Entity\Profile;
use App\Entity\User;

class LoadClientData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    private $accounts = [
        [
            'consolidator_key' => null,
            'group_key' => AccountGroup::GROUP_DEPOSIT_MONEY,
            'type_key' => 10,
            'financial_institution' => null,
            'value' => 20000,
            'monthly_contributions' => null,
            'monthly_distributions' => null,
            'sas_cash' => 1500,
        ],
        [
            'consolidator_key' => 1,
            'group_key' => AccountGroup::GROUP_FINANCIAL_INSTITUTION,
            'type_key' => 10,
            'financial_institution' => 'Transfer account 1',
            'value' => 12000,
            'monthly_contributions' => 1000,
            'monthly_distributions' => 1250,
            'sas_cash' => 2000,
        ],
        [
            'consolidator_key' => null,
            'group_key' => AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT,
            'type_key' => 3,
            'financial_institution' => 'Rollover account 1',
            'value' => 15000,
            'monthly_contributions' => 2500,
            'monthly_distributions' => 500,
            'sas_cash' => 3000,
        ],
        [
            'consolidator_key' => null,
            'group_key' => AccountGroup::GROUP_EMPLOYER_RETIREMENT,
            'type_key' => 5,
            'financial_institution' => 'Vanguard (Test)',
            'value' => 65000,
            'monthly_contributions' => 150,
            'monthly_distributions' => 50,
            'sas_cash' => 5000,
            'funds' => [
                ['name' => 'My Fund 1', 'symbol' => 'MF1', 'type' => 'EQ', 'exp_ratio' => 0.34],
                ['name' => 'My Fund 2', 'symbol' => 'MF2', 'type' => 'EQ', 'exp_ratio' => 0.62],
            ],
        ],
    ];

    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        /** @var \App\Entity\User $riaUser */
        $riaUser = $this->getReference('user-ria');

        $clientUser = $this->createUser($riaUser);
        $manager->persist($clientUser);

        $this->addReference('user-client', $clientUser);

        $this->createClientAccounts($manager, $clientUser);
        $this->createQuestionnaireAnswers($manager, $clientUser);

        $manager->flush();
    }

    public function getOrder()
    {
        return 7;
    }

    private function createUser(User $riaUser)
    {
        $clientUser = new User();
        $clientUser->setUsername('client');
        $clientUser->setEmail('client@example.com');
        $clientUser->setPlainPassword('client');
        $clientUser->setEnabled(true);
        $clientUser->setRoles(['ROLE_CLIENT']);

        $clientUserProfile = new \App\Entity\Profile();
        $clientUserProfile->setUser($clientUser);
        $clientUserProfile->setFirstName('ClientFirst');
        $clientUserProfile->setLastName('ClientLast');
        $clientUserProfile->setMiddleName('C');
        $clientUserProfile->setRegistrationStep(3);
        $clientUserProfile->setRia($riaUser);
        $clientUserProfile->setStatusProspect();

        $clientUser->setProfile($clientUserProfile);

        $clientSettings = new \App\Entity\ClientSettings();
        $clientUser->setClientSettings($clientSettings);
        $clientSettings->setClient($clientUser);

        return $clientUser;
    }

    private function createQuestionnaireAnswers(ObjectManager $manager, User $clientUser)
    {
        $clientProfile = $clientUser->getProfile();

        $answers = [];
        for ($i = 1; $i <= 4; ++$i) {
            $answers[] = [
                'question' => $this->getReference('ria-risk-question-'.$i),
                'data' => $this->getReference('ria-risk-answer-'.$i.'-1'),
            ];
        }

        $riskToleranceManager = new \App\Manager\RiskToleranceManager($clientUser, $manager, $answers);
        $riskToleranceManager->saveUserAnswers();

        $suggestedPortfolio = $riskToleranceManager->getSuggestedPortfolio();

        //$clientProfile->setSuggestedPortfolio($suggestedPortfolio);
        $clientPortfolioManager = $this->container->get('wealthbot_client.client_portfolio.manager');
        $clientPortfolioManager->proposePortfolio($clientUser, $suggestedPortfolio);

        $manager->persist($clientProfile);
    }

    private function createClientAccounts(ObjectManager $manager, User $clientUser)
    {
        $lastAccount = null;

        foreach ($this->accounts as $index => $item) {
            /** @var \App\Entity\AccountGroupType $groupType */
            $groupType = $this->getReference('client-account-group-type-'.$item['group_key'].'-'.$item['type_key']);

            $account = new \App\Entity\ClientAccount();
            $account->setGroupType($groupType);
            $account->setClient($clientUser);
            $account->setFinancialInstitution($item['financial_institution']);
            $account->setValue($item['value']);
            $account->setMonthlyContributions($item['monthly_contributions']);
            $account->setMonthlyDistributions($item['monthly_distributions']);
            $account->setSasCash($item['sas_cash']);

            $accountOwner = new \App\Entity\ClientAccountOwner();
            $accountOwner->setAccount($account);
            $accountOwner->setClient($clientUser);
            $accountOwner->setOwnerType(ClientAccountOwner::OWNER_TYPE_SELF);

            if (isset($item['consolidator_key']) && null !== $item['consolidator_key']) {
                /** @var ClientAccount $consolidator */
                $consolidator = $this->getReference('user-client-account-'.$item['consolidator_key']);
                $account->setConsolidator($consolidator);
            }

            if (isset($item['funds']) && AccountGroup::GROUP_EMPLOYER_RETIREMENT === $item['group_key']) {
                foreach ($item['funds'] as $fundItem) {
                    //                    ToDo: CE-402: check that code is not needed more
                    $outsideFund = $manager->getRepository('\App\Entity\Security')->findOneBySymbol($fundItem['symbol']);
                    if (!$outsideFund) {
                        /** @var SecurityType $securityType */
                        $securityType = $this->getReference('security-type-' . $fundItem['type']);

                        $outsideFund = new Security();
                        $outsideFund->setName($fundItem['name']);
                        $outsideFund->setSymbol($fundItem['symbol']);
                        $outsideFund->setSecurityType($securityType);
                        $outsideFund->setExpenseRatio($fundItem['exp_ratio']);
                    }

                    $securityAssignment = new SecurityAssignment();
                    $securityAssignment->setSecurity($outsideFund);
                    //   $securityAssignment->setRia($clientUser->getRia());  Deprecated
                    $securityAssignment->setIsPreferred(false);

                    $accountOutsideFund = new AccountOutsideFund();
                    $accountOutsideFund->setAccount($account);
                    $accountOutsideFund->setSecurityAssignment($securityAssignment);
                    $accountOutsideFund->setIsPreferred(false);
                    $account->addAccountOutsideFund($accountOutsideFund);

                    //$manager->persist($accountOutsideFund);
                }
            }

            $manager->persist($account);
            $manager->persist($accountOwner);

            $this->addReference('user-client-account-'.($index + 1), $account);

            if (!$account->isRetirementType()) {
                $lastAccount = $account;
            }
        }

        if ($lastAccount) {
            $systemAccount = new SystemAccount();
            $systemAccount->setClient($clientUser);
            $systemAccount->setClientAccount($lastAccount);
            $systemAccount->setAccountNumber('916985328');
            $systemAccount->setAccountDescription($lastAccount->getOwnersAsString().' '.$lastAccount->getTypeName());
            $systemAccount->setType($lastAccount->getSystemType());
            $systemAccount->setSource(SystemAccount::SOURCE_SAMPLE);

            $clientUser->addSystemAccount($systemAccount);
            $this->addReference('system-account', $systemAccount);
            $manager->persist($systemAccount);
        }
    }
}
