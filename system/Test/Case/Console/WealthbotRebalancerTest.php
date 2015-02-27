<?php

namespace Test\Console;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Console\WealthbotRebalancer;
use Model\WealthbotRebalancer\AccountCollection;
use Model\WealthbotRebalancer\ArrayCollection;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\LotCollection;
use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\RebalancerAction;
use Model\WealthbotRebalancer\QueueItem;
use Model\WealthbotRebalancer\Repository\AccountRepository;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\JobRepository;
use Model\WealthbotRebalancer\Repository\LotRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Model\WealthbotRebalancer\Lot;
use Model\WealthbotRebalancer\Position;
use Model\WealthbotRebalancer\Repository\RebalancerActionRepository;
use Model\WealthbotRebalancer\Repository\SecurityRepository;
use Model\WealthbotRebalancer\Repository\SecurityTransactionRepository;
use Model\WealthbotRebalancer\Repository\SubclassRepository;
use Model\WealthbotRebalancer\Ria;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\SecurityCollection;
use Model\WealthbotRebalancer\SecurityTransaction;
use Model\WealthbotRebalancer\Subclass;
use Model\WealthbotRebalancer\SubclassCollection;
use Model\WealthbotRebalancer\TradeData;
use Test\Util\PHPUnitUtil;

class WealthbotRebalancerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  WealthbotRebalancer */
    private $rebalancer;

    public function setUp()
    {
        $self = $this;

        $this->rebalancer = $this->getMockWealthbotRebalancer(array('getJob', 'getClient', 'getRepository', 'getRebalancerQueue'));

        $this->rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array(
                'id' => 1,
                'name' => Job::JOB_NAME_REBALANCER,
                'rebalance_type' => Job::REBALANCE_TYPE_FULL,
                'is_error' => false,
            ))));


        $this->rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($self) {
                $method = 'getMock' . $repositoryName . 'Repository';
                if (method_exists($self, $method)) {
                    return $self->$method();
                }

                return null;
            }));

        $this->rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($this->getMockClient(array('id' => 1))));

        $this->rebalancer->expects($this->any())
            ->method('getRebalancerQueue')
            ->will($this->returnValue($this->getMockRebalancerQueue()));
    }

    public function testGetClient()
    {
        $this->assertEquals(1, $this->rebalancer->getClient()->getId());

        $rebalancer = new WealthbotRebalancer();
        $this->assertNull($rebalancer->getClient());
    }

    public function testGetBusinessCalendarManager()
    {
        $rebalancer = new WealthbotRebalancer();

        $this->assertNotNull($rebalancer->getBusinessCalendarManager());
        $this->assertInstanceOf('\Manager\BusinessCalendar', $rebalancer->getBusinessCalendarManager());
    }

    public function testGetJob()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'startForRebalancerAction'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) {
                if ($repositoryName === 'RebalancerAction') {
                    $rebalancerActionRepo = $this->getMockRebalancerActionRepository(array('bindForJob'));
                    $rebalancerActionRepo->expects($this->any())
                        ->method('bindForJob')
                        ->will($this->returnValue(array()));

                    return $rebalancerActionRepo;
                }

                return null;
            }));

        $this->assertNull(PHPUnitUtil::callMethod($rebalancer, 'getJob'));

        $job = $this->getMockJob(array(
            'id' => 1,
            'name' => Job::JOB_NAME_REBALANCER,
            'rebalancer_type' => Job::REBALANCE_TYPE_FULL
        ));

        $rebalancer->startForJob($job);
        $this->assertEquals($job, PHPUnitUtil::callMethod($rebalancer, 'getJob'));
    }

    public function testGetRebalancerAction()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'startForClient'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) {
                if ($repositoryName === 'Client') {
                    $clientRepo = $this->getMockClientRepository(array('getClientByRebalancerAction'));
                    $clientRepo->expects($this->any())
                        ->method('getClientByRebalancerAction')
                        ->will($this->returnValue($this->getMockClient(array('id' => 12))));

                    return $clientRepo;

                } elseif ($repositoryName === 'Account') {
                    $clientRepo = $this->getMockAccountRepository(array('getAccountsByRebalancerAction'));
                    $clientRepo->expects($this->any())
                        ->method('getAccountsByRebalancerAction')
                        ->will($this->returnValue($this->getMockAccountCollection()));

                    return $clientRepo;
                }

                return null;
            }));

        $rebalancerAction = $this->getMockRebalancerAction(array('status' => Job::REBALANCE_TYPE_FULL_AND_TLH));

        $rebalancer->startForRebalancerAction($rebalancerAction);

        $this->assertEquals($rebalancerAction, $rebalancer->getRebalancerAction());
    }

    public function testAddRepositoryAndGetRepository()
    {
        $rebalancer = $this->getMockWealthbotRebalancer();
        $this->assertNull($rebalancer->getRepository('Client'));

        $rebalancer->addRepository(new ClientRepository());
        $repository = $rebalancer->getRepository('Client');

        $this->assertNotNull($repository);
        $this->assertInstanceOf('Model\WealthbotRebalancer\Repository\ClientRepository', $repository);
    }

    public function testStart()
    {
        $jobCollection = $this->getMockJobCollection(array(array(),array()));

        $rebalancer = $this->getMockWealthbotRebalancer(array('startForJob', 'getRepository'));

        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($jobCollection) {
                if ($repositoryName === 'Job') {
                    $jobRepo = $this->getMockJobRepository(array('findAllCurrentRebalancerJob'));
                    $jobRepo->expects($this->any())
                        ->method('findAllCurrentRebalancerJob')
                        ->will($this->returnValue($jobCollection));

                    return $jobRepo;
                }

                return null;
            }));

        $rebalancer->expects($this->exactly(count($jobCollection)))
            ->method('startForJob');

        $rebalancer->start();
    }

    public function testStartForJob()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('startForRebalancerAction', 'getRepository'));

        $rebalancerActionCollection = $this->getMockRebalancerActionCollection(array(
            array(
                'id' => rand(1, 100),
                'job' => array('id' => 1),
                'account_id' => rand(1, 100),
                'portfolio_id' => rand(1, 100),
                'client' => array('id' => 1)
            ),
            array(
                'id' => rand(1, 100),
                'job' => array('id' => 1),
                'account_id' => rand(1, 100),
                'portfolio_id' => rand(1, 100),
                'client' => array('id' => 1)
            )
        ));

        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($rebalancerActionCollection) {
                if ($repositoryName === 'RebalancerAction') {
                    $rebalancerActionRepo = $this->getMockRebalancerActionRepository(array('bindForJob'));
                    $rebalancerActionRepo->expects($this->any())
                        ->method('bindForJob')
                        ->will($this->returnValue($rebalancerActionCollection));

                    return $rebalancerActionRepo;
                }

                return null;
            }));

        $rebalancer->expects($this->exactly(count($rebalancerActionCollection)))->method('startForRebalancerAction');

        $rebalancer->startForJob($this->getMockJob());
    }

    public function testStartForRebalancerAction()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'startForClient'));

        $mockClient = $this->getMockClient();

        $clientRepo = $this->getMockClientRepository(array('getClientByRebalancerAction'));
        $clientRepo->expects($this->any())
            ->method('getClientByRebalancerAction')
            ->will($this->returnValue($mockClient));

        $accountCollection = new AccountCollection();
        $accountCollection->add($this->getMockAccount());

        $accountRepo = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\AccountRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('getAccountsByRebalancerAction'))
            ->getMock();
        $accountRepo->expects($this->any())
            ->method('getAccountsByRebalancerAction')
            ->will($this->returnValue($accountCollection));

        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($clientRepo, $accountRepo) {
                if ($repositoryName === 'Client') {
                    return $clientRepo;
                } elseif ($repositoryName === 'Account') {
                    return $accountRepo;
                }

                return null;
            }));

        $rebalancerAction = new RebalancerAction();

        $rebalancer->expects($this->once())->method('startForClient');

        $rebalancer->startForRebalancerAction($rebalancerAction);

        $this->assertEquals($mockClient->getAccounts(), $accountCollection);
    }

    public function testStartForRebalancerActionWithoutAccounts()
    {
        $client = $this->getMockClient();

        $clientRepo = $this->getMockClientRepository(array('getClientByRebalancerAction'));
        $clientRepo->expects($this->any())
            ->method('getClientByRebalancerAction')
            ->will($this->returnValue($client));

        $accountRepo = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\AccountRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('getAccountsByRebalancerAction'))
            ->getMock();
        $accountRepo->expects($this->any())->method('getAccountsByRebalancerAction')->will($this->returnValue(new AccountCollection()));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'startForClient'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($clientRepo, $accountRepo) {
                if ($repositoryName === 'Client') {
                    return $clientRepo;
                } elseif ($repositoryName === 'Account') {
                    return $accountRepo;
                }

                return null;
            }));

        $rebalancer->startForRebalancerAction(new RebalancerAction());

        $this->assertCount(0, $client->getAccounts());
    }

    public function testStartForClient()
    {
        $client = $this->getMockClient(array(
            'id' => 77,
            'accounts' => array(
                array('id' => 86),
                array('id' => 87)
            )
        ));

        $ria = $this->getMockRia(array('id' => 51));
        $riaCompanyInformation = $this->getMockRiaCompanyInformarion(array('id' => 92));

        $riaRepository = $this->getMock('Model\WealthbotRebalancer\Repository\RiaRepository', array('findOneByClient'));
        $riaRepository->expects($this->once())
            ->method('findOneByClient')
            ->will($this->returnValue($ria));

        $riaCompanyInformationRepository = $this->getMock('Model\WealthbotRebalancer\Repository\RiaCompanyInformationRepository', array('findOneByRia'));
        $riaCompanyInformationRepository->expects($this->once())
            ->method('findOneByRia')
            ->will($this->returnValue($riaCompanyInformation));


        $businessCalendar = $this->getMock('\Manager\BusinessCalendar');
        $businessCalendar->expects($this->any())
            ->method('addBusinessDays')
            ->will($this->returnValue(new \DateTime()));

        $rebalancer = $this->getMockWealthbotRebalancer(array(
                'getRepository',
                'prepareAccount',
                'getBusinessCalendarManager',
                'rebalancingTrigger',
                'rebalance'
            ));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($riaRepository, $riaCompanyInformationRepository) {
                switch ($repositoryName) {
                    case 'Ria':
                        return $riaRepository;
                    case 'RiaCompanyInformation':
                        return $riaCompanyInformationRepository;
                    default:
                        return $this->getMock("Model\\WealthbotRebalancer\\Repository\\{$repositoryName}Repository");
                }
            }));
        $rebalancer->expects($this->any())
            ->method('getBusinessCalendarManager')
            ->will($this->returnValue($businessCalendar));
        $rebalancer->expects($this->once())->method('rebalance');

        $rebalancer->startForClient($client);

        $this->assertEquals($client, $rebalancer->getClient());
        $this->assertEquals($ria, $rebalancer->getClient()->getRia());
    }

    public function testRebalancingTriggerStatusAnalyzed()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('checkCashNeeds'));

        $account = $this->getMockAccount(array('status' => Account::STATUS_ANALYZED));

        $account->setStatus(Account::STATUS_ANALYZED);
        $rebalancer->expects($this->once())->method('checkCashNeeds');
        $rebalancer->rebalancingTrigger($account);
    }

    public function testRebalancingTriggerStatusInitialRebalanceComplete()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('checkCashNeeds'));

        $account = $this->getMockAccount(array('status' => Account::STATUS_INIT_REBALANCE_COMPLETE));

        $rebalancer->expects($this->once())->method('checkCashNeeds');
        $rebalancer->rebalancingTrigger($account);
    }

    public function testRebalancingTriggerStatusInitRebalance()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('liquidateAccountIfNeed'));

        $account = $this->getMockAccount(array('status' => Account::STATUS_INIT_REBALANCE));

        $rebalancer->expects($this->once())->method('liquidateAccountIfNeed');
        $rebalancer->rebalancingTrigger($account);
    }

    public function testRebalancingTriggerStatusRebalanced()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('checkCashNeeds'));

        $account = $this->getMockAccount(array('status' => Account::STATUS_REBALANCED));

        $rebalancer->expects($this->once())->method('checkCashNeeds');
        $rebalancer->rebalancingTrigger($account);
    }

    public function testRebalancingTriggerStatusRegistered()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('checkCashNeeds', 'liquidateAccountIfNeed'));

        $account = $this->getMockAccount(array('status' => Account::STATUS_REGISTERED));

        $rebalancer->expects($this->never())->method('checkCashNeeds');
        $rebalancer->expects($this->never())->method('liquidateAccountIfNeed');
        $rebalancer->rebalancingTrigger($account);
    }

    public function testRebalancingTriggerStatusActive()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('checkCashNeeds', 'liquidateAccountIfNeed'));

        $account = $this->getMockAccount(array('status' => Account::STATUS_ACTIVE));

        $rebalancer->expects($this->never())->method('checkCashNeeds');
        $rebalancer->expects($this->never())->method('liquidateAccountIfNeed');
        $rebalancer->rebalancingTrigger($account);
    }


    public function testRebalancingTriggerStatusWaitingActivation()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('checkCashNeeds', 'liquidateAccountIfNeed'));

        $account = $this->getMockAccount(array('status' => Account::STATUS_WAITING_ACTIVATION));

        $rebalancer->expects($this->never())->method('checkCashNeeds');
        $rebalancer->expects($this->never())->method('liquidateAccountIfNeed');
        $rebalancer->rebalancingTrigger($account);
    }

    public function testRebalancingTriggerStatusClose()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('checkCashNeeds', 'liquidateAccountIfNeed'));

        $account = $this->getMockAccount(array('status' => Account::STATUS_CLOSED));

        $rebalancer->expects($this->never())->method('checkCashNeeds');
        $rebalancer->expects($this->never())->method('liquidateAccountIfNeed');
        $rebalancer->rebalancingTrigger($account);
    }

    public function testAccountRebalance()
    {
        $client = $this->getMockClient(array(
            'id' => 11,
            'accounts' => array(
                array('id' => 53),
                array('id' => 54),
                array('id' => 55)
            )
        ));

        $subclassCollection = $this->getMockSubclassCollection(array(array('id' => 13)), null);
        $subclassCollection1 = $this->getMockSubclassCollection();


        $rebalancer = $this->getMockWealthbotRebalancer(array('getClient', 'prepareCollection', 'operateSubclasses', 'checkTlh', 'processTlh'));

        $rebalancer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($client));

        $rebalancer->expects($this->exactly($client->getAccounts()->count()))
            ->method('prepareCollection')
            ->will($this->returnValue($subclassCollection));

        $rebalancer->expects($this->exactly($client->getAccounts()->count()))
            ->method('operateSubclasses')
            ->will($this->returnValue($this->getMockSubclassCollection(array())));

        $rebalancer->expects($this->exactly($subclassCollection->count() * $client->getAccounts()->count()))
            ->method('checkTlh')
            ->will($this->returnValue(true));

//        $rebalancer->expects($this->exactly($subclassCollection->count() * $client->getAccounts()->count()))
//            ->method('processTlh');

        $rebalancer->accountRebalance();
    }

    public function testHouseholdRebalance()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('getClient', 'prepareCollection', 'operateSubclasses'));

        $rebalancer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($this->getMockClient()));

        $rebalancer->expects($this->once())
            ->method('prepareCollection')
            ->will($this->returnValue($this->getMockSubclassCollection()));

        $rebalancer->expects($this->once())
            ->method('operateSubclasses')
            ->will($this->returnValue($this->getMockSubclassCollection()));

        $rebalancer->householdRebalance();
    }

    public function testPrepareAccountWithSecurities()
    {
        $today = new \DateTime();

        $businessCalendar = $this->getMock('Manager\BusinessCalendar', array('addBusinessDays'));
        $businessCalendar->expects($this->once())
            ->method('addBusinessDays')
            ->will($this->returnValue($today->modify('+4 day')));

        $distributionRepository = $this->getMock('Model\WealthbotRebalancer\Repository\DistributionRepository', array(
            'findScheduledDistribution',
            'findOneTimeDistribution'
        ));
        $distributionRepository->expects($this->once())
            ->method('findScheduledDistribution')
            ->will($this->returnValue(500));
        $distributionRepository->expects($this->once())
            ->method('findOneTimeDistribution')
            ->will($this->returnValue(1000));

        $accountRepository = $this->getMock('Model\WealthbotRebalancer\Repository\AccountRepository', array(
           'getAccountValues'
        ));
        $accountRepository->expects($this->once())
            ->method('getAccountValues')
            ->will($this->returnValue(array(
                'total_cash_in_account' => 10000,
                'sas_cash' => 500,
                'billing_cash' => 3000,
                'cash_buffer' => 2000
            )));

        $repository = $this->getMockWealthbotRebalancer(array('getBusinessCalendarManager', 'getRepository'));
        $repository->expects($this->once())
            ->method('getBusinessCalendarManager')
            ->will($this->returnValue($businessCalendar));
        $repository->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($distributionRepository, $accountRepository) {
                if ($repositoryName === 'Distribution') {
                    return $distributionRepository;
                } elseif ($repositoryName === 'Account') {
                    return $accountRepository;
                }

                return null;
            }));

        $account = $this->getMockAccount(array(
            'id' => 1,
            'securities' => array(
                array('id' => 1, 'name' => 'Sec1', 'symbol' => 's1'),
                array('id' => 2, 'name' => 'Sec2', 'symbol' => 's2')
            )
        ));

        $repository->prepareAccount($account);

        $this->assertEquals(500, $account->getScheduledDistribution());
        $this->assertEquals(1000, $account->getOneTimeDistribution());
        $this->assertEquals(10000, $account->getTotalCash());
        $this->assertEquals(500, $account->getSasCash());
        $this->assertEquals(3000, $account->getBillingCash());
        $this->assertEquals(2000, $account->getCashBuffer());
        $this->assertCount(2, $account->getSecurities());
    }

    public function testPrepareAccountWithoutSecurities()
    {
        $today = new \DateTime();

        $businessCalendar = $this->getMock('Manager\BusinessCalendar', array('addBusinessDays'));
        $businessCalendar->expects($this->once())
            ->method('addBusinessDays')
            ->will($this->returnValue($today->modify('+4 day')));

        $distributionRepository = $this->getMock('Model\WealthbotRebalancer\Repository\DistributionRepository', array(
            'findScheduledDistribution',
            'findOneTimeDistribution'
        ));
        $distributionRepository->expects($this->once())->method('findScheduledDistribution');
        $distributionRepository->expects($this->once())->method('findOneTimeDistribution');

        $accountRepository = $this->getMock('Model\WealthbotRebalancer\Repository\AccountRepository', array(
            'getAccountValues'
        ));
        $accountRepository->expects($this->once())->method('getAccountValues');

        $securities = $this->getMockSecurityCollection(array(
            array('id' => 5, 'name' => 'Sec5', 'symbol' => 's5'),
            array('id' => 6, 'name' => 'Sec6', 'symbol' => 's6'),
            array('id' => 7, 'name' => 'Sec7', 'symbol' => 's7')
        ));

        $securityRepository = $this->getMockSecurityRepository(array('findSecuritiesByAccount'));
        $securityRepository->expects($this->once())
            ->method('findSecuritiesByAccount')
            ->will($this->returnValue($securities));

        $repository = $this->getMockWealthbotRebalancer(array('getBusinessCalendarManager', 'getRepository'));
        $repository->expects($this->once())
            ->method('getBusinessCalendarManager')
            ->will($this->returnValue($businessCalendar));
        $repository->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use (
                    $distributionRepository,
                    $accountRepository,
                    $securityRepository
                ) {
                    if ($repositoryName === 'Distribution') {
                        return $distributionRepository;
                    } elseif ($repositoryName === 'Account') {
                        return $accountRepository;
                    } elseif ($repositoryName === 'Security') {
                        return $securityRepository;
                    }

                    return null;
                }
            ));

        $account = $this->getMockAccount(array('id' => 1));
        $this->assertCount(0, $account->getSecurities());

        $repository->prepareAccount($account);

        $accountSecurities = $account->getSecurities();
        $this->assertCount(3, $accountSecurities);

        $this->assertEquals('Sec5', $accountSecurities->get(5)->getName());
        $this->assertEquals('s5', $accountSecurities->get(5)->getSymbol());
        $this->assertEquals('Sec6', $accountSecurities->get(6)->getName());
        $this->assertEquals('s6', $accountSecurities->get(6)->getSymbol());
        $this->assertEquals('Sec7', $accountSecurities->get(7)->getName());
        $this->assertEquals('s7', $accountSecurities->get(7)->getSymbol());
    }

    public function testPrepareCollection()
    {
        $portfolioRepoMock = $this->getMockPortfolioRepository(array('findPortfolioByClient', 'loadPortfolioValues'));
        $portfolioRepoMock->expects($this->any())
            ->method('findPortfolioByClient')
            ->will($this->returnValue($this->getMockPortfolio()));

        $securityRepoMock = $this->getMockSecurityRepository(array('findSecuritiesByAccount', 'findSecuritiesByPortfolio'));
        $securityRepoMock->expects($this->any())
            ->method('findSecuritiesByPortfolio')
            ->will($this->returnValue($this->getMockSecurityCollection(array(array(), array()))));

        $subclassRepoMock = $this->getMockSubclassRepository(array('bindAllocations'));
        $subclassRepoMock->expects($this->any())
            ->method('bindAllocations')
            ->will($this->returnValue($this->getMockSubclassCollection()));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use (
                    $portfolioRepoMock,
                    $securityRepoMock,
                    $subclassRepoMock
                ) {
                    if ($repositoryName === 'Portfolio') {
                        return $portfolioRepoMock;
                    } elseif ($repositoryName === 'Security') {
                        return $securityRepoMock;
                    } elseif ($subclassRepoMock) {
                        return $subclassRepoMock;
                    }

                    return null;
                }
            ));

        $collection = $rebalancer->prepareCollection($this->getMockClient());
        $this->assertInstanceOf('Model\WealthbotRebalancer\SubclassCollection', $collection);
    }

    public function testPrepareCollectionPortfolioNotFoundException()
    {
        $mockClient = $this->getMockClient();

        $portfolioRepoMock = $this->getMockPortfolioRepository(array('findPortfolioByClient', 'loadPortfolioValues'));
        $portfolioRepoMock->expects($this->any())
            ->method('findPortfolioByClient')
            ->will($this->returnValue(false));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($portfolioRepoMock) {
                if ($repositoryName === 'Portfolio') {
                    return $portfolioRepoMock;
                }

                return null;
            }));

        $this->setExpectedException('Exception', 'Portfolio for client '.$mockClient->getId().' does not exist');
        $rebalancer->prepareCollection($mockClient);
    }

    public function testPrepareCollectionEmptySecuritiesException()
    {
        $mockPortfolio = $this->getMockPortfolio();

        $portfolioRepoMock = $this->getMockPortfolioRepository(array('findPortfolioByClient', 'loadPortfolioValues'));
        $portfolioRepoMock->expects($this->any())
            ->method('findPortfolioByClient')
            ->will($this->returnValue($mockPortfolio));

        $securityRepoMock = $this->getMockSecurityRepository(array('findSecuritiesByAccount', 'findSecuritiesByPortfolio'));
        $securityRepoMock->expects($this->any())
            ->method('findSecuritiesByPortfolio')
            ->will($this->returnValue($this->getMockSecurityCollection()));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($portfolioRepoMock, $securityRepoMock) {
                if ($repositoryName === 'Portfolio') {
                    return $portfolioRepoMock;
                } elseif ($repositoryName === 'Security') {
                    return $securityRepoMock;
                }

                return null;
            }));

        $this->setExpectedException('Exception', 'Securities for portfolio '.$mockPortfolio->getId().' does not exist');
        $rebalancer->prepareCollection($this->getMockClient());
    }

    public function testLiquidateAccountNoNeed()
    {
        $account = $this->getMockAccount(array('is_ready_to_rebalance' => false));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getJob'));

        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array(
                'id' => 1,
                'name' => Job::JOB_NAME_REBALANCER,
                'rebalance_type' => Job::REBALANCE_TYPE_FULL,
                'is_error' => false,
                'ria' => array(
                    'ria_company_information' => array(
                        'use_transaction_fees' => false
                    )
                )
            ))));

        $rebalancer->liquidateAccountIfNeed($account);

        $this->assertFalse($account->getIsReadyToRebalance());
    }

    public function testLiquidateAccountIfNeed()
    {
        $noPreferredBuySecurities = array(
            array(
                'isPreferredBuy' => false,
                'symbol' => Security::SYMBOL_CASH,
                'price' => 20.5,
                'qty' => 10.1,
                'amount' => 207.05
            ),
            array(
                'isPreferredBuy' => true,
                'symbol' => Security::SYMBOL_IDA12,
                'price' => 13.1,
                'qty' => 24,
                'amount' => 314.4
            )
        );

        $preferredBuySecurities = array(
            array(
                'isPreferredBuy' => true,
                'symbol' => Security::SYMBOL_CASH,
                'price' => 20.5,
                'qty' => 10.1,
                'amount' => 207.05
            ),
            array(
                'isPreferredBuy' => true,
                'name' => 'SSgA Emerging Markets Fund',
                'symbol' => Security::SYMBOL_IDA12,
                'price' => 13.1,
                'qty' => 24,
                'amount' => 314.4
            )
        );

        $notCashSecurities = array(
            array(
                'isPreferredBuy' => true,
                'symbol' => 'VTI',
                'price' => 20.5,
                'qty' => 10.1
            ),
            array(
                'isPreferredBuy' => true,
                'symbol' => 'SSEMX',
                'price' => 13.1,
                'qty' => 24
            )
        );

        $rebalancer = $this->getMockWealthbotRebalancer(array('getJob', 'getRebalancerQueue', 'getRebalancerAction'));

        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array(
                'id' => 1,
                'name' => Job::JOB_NAME_REBALANCER,
                'rebalance_type' => Job::REBALANCE_TYPE_FULL,
                'is_error' => false,
                'ria' => array(
                    'ria_company_information' => array(
                        'use_transaction_fees' => true
                    )
                )
            ))));

        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($this->getMockRebalancerAction(array(
                'id' => 2,
            ))));

        $rebalancer->expects($this->any())
            ->method('getRebalancerQueue')
            ->will($this->returnValue($this->getMockRebalancerQueue()));

        //liquidateAccountIfNeed
        // test non-preferred securities
        $account = $this->getMockAccount(array('is_ready_to_rebalance' => false));
        $account->setSecurities($this->getMockSecurityCollection($noPreferredBuySecurities));
        $this->assertFalse($account->getIsReadyToRebalance());
        $this->assertFalse($account->getIsReadyToRebalance());

        $rebalancer->liquidateAccountIfNeed($account);
        $nonPreferredSecurities = $account->findNoPreferredBuySecurities();
        $this->assertTrue($account->getIsReadyToRebalance());
        $this->assertCount(1, $nonPreferredSecurities);
        $this->assertEquals(0, $nonPreferredSecurities->first()->getQty());

        // test preferred securities with cash type
        $account = $this->getMockAccount();
        $account->setSecurities($this->getMockSecurityCollection($preferredBuySecurities));
        $this->assertFalse($account->getIsReadyToRebalance());

        $rebalancer->liquidateAccountIfNeed($account);
        $nonPreferredSecurities = $account->findNoPreferredBuySecurities();
        $this->assertTrue($account->getIsReadyToRebalance());
        $this->assertCount(0, $nonPreferredSecurities);

        // test preferred security with not cash type
        $account = $this->getMockAccount();
        $account->setSecurities($this->getMockSecurityCollection($notCashSecurities));
        $this->assertFalse($account->getIsReadyToRebalance());

        $rebalancer->liquidateAccountIfNeed($account);
        $this->assertFalse($account->getIsReadyToRebalance());
    }

    public function testCheckCashNeeds()
    {
        $account = $this->getMockAccount(array(
            'isReadyToRebalance' => false,
            'scheduledDistribution' => 15000,
            'oneTimeDistribution' => 3450,
            'cashBuffer' => 2300,
            'sasCash' => 3000,
            'billingCash' => 3400,
            'totalCash' => 25000,
        ));

        $this->rebalancer->checkCashNeeds($account);
        $this->assertTrue($account->getIsReadyToRebalance());

        $account = $this->getMockAccount(array(
            'isReadyToRebalance' => false,
            'scheduledDistribution' => 15000,
            'oneTimeDistribution' => 3450,
            'cashBuffer' => 2300,
            'sasCash' => 3000,
            'billingCash' => 3400,
            'totalCash' => 30000,
        ));

        $this->rebalancer->checkCashNeeds($account);
        $this->assertFalse($account->getIsReadyToRebalance());
    }

    public function testSellSubclass()
    {
        $account = $this->getMockAccount();
        $subclass = $this->getMockSubclass();
        $security = $this->getMockSecurity(array('amount' => 12070, 'qty' => 80, 'id' => 2));
        $muniSecurity = $this->getMockSecurity(array('amount' => 120, 'qty' => 2, 'id' => 1));

        $security->setSubclass($subclass);
        $subclass->setSecurity($security);
        $subclass->setMuniSecurity($muniSecurity);

        $lots = $this->getMockLotCollection(array(
            array('quantity' => 2, 'amount' => 120, 'is_muni' => true, 'initial' => array('id' => 1, 'date' => '2012-02-02')),
            array('quantity' => 10, 'amount' => 1570, 'is_muni' => false, 'initial' => array('id' => 1, 'date' => '2012-02-02')),
            array('quantity' => 20, 'amount' => 3000, 'is_muni' => false, 'initial' => array('id' => 1, 'date' => '2012-02-02')),
            array('quantity' => 50, 'amount' => 7500, 'is_muni' => false, 'initial' => array('id' => 1, 'date' => '2012-02-02')),
        ));

        $lotRepository = $this->getMockLotRepository(array('findOrderedLots'));
        $lotRepository->expects($this->any())
            ->method('findOrderedLots')
            ->will($this->returnValue($lots));

        $securityTransactionRepository = $this->getMockSecurityTransactionRepository(array('findOneByPortfolioAndSecurity'));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getJob', 'checkShortTermRedemption', 'checkTransaction', 'getClient', 'getRebalancerAction'), false);
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($lotRepository, $securityTransactionRepository) {
                if ($repositoryName === 'Lot') {
                    return $lotRepository;
                } elseif ($repositoryName === 'SecurityTransaction') {
                    return $securityTransactionRepository;
                }

                return null;
            }));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($this->getMockRebalancerAction(array('id' => 2))));
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob()));
        $rebalancer->expects($this->any())
            ->method('checkShortTermRedemption')
            ->will($this->returnValue(true));
        $rebalancer->expects($this->any())
            ->method('checkTransaction')
            ->will($this->returnValue(true));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($this->getMockClient(array('portfolio' => array()))));


        $this->assertEquals(12190, $rebalancer->sellSubclass($subclass, $account, 15000, true));
        $this->assertEquals(12070, $subclass->getSecurity()->getAmount());
        $this->assertEquals(120, $subclass->getMuniSecurity()->getAmount());
        $this->assertCount(0, $rebalancer->getRebalancerQueue());

        $this->assertEquals(12190, $rebalancer->sellSubclass($subclass, $account, 15000, true));
        $this->assertEquals(12070, $subclass->getSecurity()->getAmount());
        $this->assertEquals(120, $subclass->getMuniSecurity()->getAmount());

        $this->assertEquals(10090, $rebalancer->sellSubclass($subclass, $account, 10000, false));
        $this->assertEquals(2100, $subclass->getSecurity()->getAmount());
        $this->assertEquals(0, $subclass->getMuniSecurity()->getAmount());
        $this->assertGreaterThan(0, count($rebalancer->getRebalancerQueue()));
    }

    public function testBuySecurity()
    {
        $security = $this->getMockSecurity(array(
            'price' => 150,
            'qty' => 10,
            'amount' => 1500,
            'symbol' => 'VTI'
        ));

        $account = $this->getMockAccount(array(
            'client' => array(
                'portfolio' => array(
                    'id' => 1
                ),
                'ria' => array()
            ),
            'isReadyToRebalance' => true,
            'scheduledDistribution' => 15000,
            'oneTimeDistribution' => 3450,
            'cashBuffer' => 2300,
            'sasCash' => 3000,
            'billingCash' => 3400,
            'totalCash' => 30000,
        ));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('getJob', 'getRebalancerAction'), false);
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob()));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($this->getMockRebalancerAction(array('id' => 2))));

        $this->assertEquals(3450, $rebalancer->buySecurity($security, $account, 3450, true));
        $this->assertEquals(1500, $security->getAmount());
        $this->assertCount(0, $rebalancer->getRebalancerQueue());

        $this->assertEquals(3450, $rebalancer->buySecurity($security, $account, 3450, false));
        $this->assertEquals(4950, $security->getAmount());
        $this->assertGreaterThan(0, count($rebalancer->getRebalancerQueue()));
    }

    public function testBuySecurityZeroAmountException()
    {
        $security = $this->getMockSecurity(array(
            'price' => 150,
            'qty' => 10,
            'amount' => 1500
        ));

        $account = $this->getMockAccount(array('client' => array('ria' => array())));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('getJob'));
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array('id' => 1))));


        $this->setExpectedException(
            'Exception',
            sprintf(
                'Buying security error: cannot buy security with id: %s, qty: %s, amount: %s.',
                $security->getId(),
                0,
                0
            )
        );

        $this->assertEquals(0, $rebalancer->buySecurity($security, $account, 0, false));
        $this->assertCount(0, $rebalancer->getRebalancerQueue());
    }

    public function testBuySecurityLessThanZeroAmountException()
    {
        $security = $this->getMockSecurity(array(
            'price' => 150,
            'qty' => 10,
            'amount' => 1500
        ));

        $account = $this->getMockAccount(array('client' => array('ria' => array())));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('getJob'));
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array('id' => 1))));


        $this->setExpectedException(
            'Exception',
            sprintf(
                'Buying security error: cannot buy security with id: %s, qty: %s, amount: %s.',
                $security->getId(),
                -2,
                -300
            )
        );

        $this->assertEquals(0, $rebalancer->buySecurity($security, $account, -300, false));
        $this->assertCount(0, $rebalancer->getRebalancerQueue());
    }

    public function testBuySubclassSecurity()
    {
        $security = $this->getMockSecurity(array(
            'price' => 150,
            'qty' => 10,
            'amount' => 1500
        ));

        $subclass = $this->getMockSubclass();
        $subclass->setSecurity($security);
        $account = $this->getMockAccount(array('client' => array('ria' => array())));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('isBuyMuni', 'getJob', 'getRebalancerQueue', 'getRebalancerAction'));
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array('id' => 1))));
        $rebalancer->expects($this->any())
            ->method('getRebalancerQueue')
            ->will($this->returnValue($this->getMockRebalancerQueue()));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($this->getMockRebalancerAction(array('id' => 2))));

        $rebalancer->expects($this->exactly(2))
            ->method('isBuyMuni')
            ->will($this->returnValue(false));

        $securityAmount = $security->getAmount();

        $result = $rebalancer->buySubclass($subclass, $account, 1550);
        $this->assertEquals($securityAmount, $result);
        $this->assertEquals(($securityAmount + $result), $security->getAmount());

        $security->setAmount(1500);

        $result = $rebalancer->buySubclass($subclass, $account, 1200);
        $this->assertEquals(1200, $result);
        $this->assertEquals(($securityAmount + 1200), $subclass->getSecurity()->getAmount());
    }

    public function testBuySubclassMuniSecurity()
    {
        $muniSecurity = $this->getMockSecurity(array(
                'price' => 150,
                'qty' => 10,
                'amount' => 1500
            ));

        $subclass = $this->getMockSubclass();
        $subclass->setMuniSecurity($muniSecurity);
        $account = $this->getMockAccount(array('client' => array('ria' => array())));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('isBuyMuni', 'getJob', 'getRebalancerQueue', 'getRebalancerAction'));
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array('id' => 1))));
        $rebalancer->expects($this->any())
            ->method('getRebalancerQueue')
            ->will($this->returnValue($this->getMockRebalancerQueue()));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($this->getMockRebalancerAction(array('id' => 2))));

        $rebalancer->expects($this->exactly(2))
            ->method('isBuyMuni')
            ->will($this->returnValue(true));

        $securityAmount = $muniSecurity->getAmount();

        $result = $rebalancer->buySubclass($subclass, $account, 1550);
        $this->assertEquals($securityAmount, $result);
        $this->assertEquals(($securityAmount + $result), $muniSecurity->getAmount());

        $muniSecurity->setAmount(1500);

        $result = $rebalancer->buySubclass($subclass, $account, 1200);
        $this->assertEquals(1200, $result);
        $this->assertEquals(($securityAmount + 1200), $subclass->getMuniSecurity()->getAmount());
    }

    public function testBuyRequiredCash()
    {
        $securityCollection1 = new SecurityCollection();
        $securityCollection2 = new SecurityCollection();

        $subclass1 = $this->getMockSubclass(array(
            'id' => 1,
            'current_allocation' => 5,
            'target_allocation' => 20
        ));

        $security1 = $this->getMockSecurity(array(
            'id' => 1,
            'amount' => 1000,
            'qty' => 100,
            'name' => 'Security1',
            'symbol' => 'Sec1'
        ));

        $subclass2 = $this->getMockSubclass(array(
            'id' => 2,
            'current_allocation' => 25,
            'target_allocation' => 45
        ));

        $security2 = $this->getMockSecurity(array(
            'id' => 2,
            'amount' => 5000,
            'qty' => 10,
            'name' => 'Security2',
            'symbol' => 'Sec2'
        ));

        $subclass3 = $this->getMockSubclass(array(
            'id' => 3,
            'current_allocation' => 70,
            'target_allocation' => 35
        ));

        $security3 = $this->getMockSecurity(array(
            'id' => 5,
            'amount' => 14000,
            'qty' => 20,
            'name' => 'Security3',
            'symbol' => 'Sec3'
        ));

        $security1->setSubclass($subclass1);
        $subclass1->setSecurity($security1);

        $security2->setSubclass($subclass2);
        $subclass2->setSecurity($security2);

        $security3->setSubclass($subclass3);
        $subclass3->setSecurity($security3);

        $securityCollection1->add($security1);
        $securityCollection1->add($security2);

        $securityCollection2->add($security3);

        $lotsData = array(
            array(
                'id'       => 1,
                'is_muni'  => false,
                'quantity' => 100,
                'amount'   => 1000,
                'status'   => Lot::LOT_IS_OPEN,
                'security' => $security1,
                'date'     => '2012-01-01',
                'initial'  => array('id' => 1, 'date' => '2012-01-01')
            ),
            array(
                'id'       => 2,
                'is_muni'  => false,
                'quantity' => 10,
                'amount'   => 5000,
                'status'   => Lot::LOT_IS_OPEN,
                'security' => $security2,
                'date'     => '2012-01-01',
                'initial'  => array('id' => 1, 'date' => '2012-01-01')
            ),
            array(
                'id'       => 3,
                'is_muni'  => false,
                'quantity' => 20,
                'amount'   => 14000,
                'status'   => Lot::LOT_IS_OPEN,
                'security' => $security3,
                'date'     => '2012-01-01',
                'initial'  => array('id' => 1, 'date' => '2012-01-01')
            )
        );

        $lots = [];
        foreach ($lotsData as $key => $data) {
            /** @var Lot $lot */
            $lot = $this->getMock('Model\WealthbotRebalancer\Lot', null);
            $lot->loadFromArray($data);
            $lots[$key + 1] = $lot;
        }

        $accountsData = array(
            array(
                'id' => 1,
                'is_ready_to_rebalance' => true,
                'total_cash' => 100000,
                'cash_buffer' => 2000,
                'sas_cash' => 1000,
                'billing_cash' => 500,
                'scheduled_distribution' => 2300,
                'one_time_distribution' => 0,
                'client' => array(
                    'portfolio' => array(
                            'id' => 1
                    ),
                    'ria' => array(
                        'ria_company_information' => array(
                            'use_transaction_fees' => true,
                            'transaction_min_amount' => 400,
                            'transaction_min_amount_percent' => 1.5
                        )
                    )
                )
            ),
            array(
                'id' => 2,
                'is_ready_to_rebalance' => true,
                'total_cash' => 150000,
                'cash_buffer' => 1000,
                'sas_cash' => 300,
                'billing_cash' => 100,
                'scheduled_distribution' => 0,
                'one_time_distribution' => 2400,
                'client' => array(
                    'portfolio' => array(
                        'id' => 1
                    ),
                    'ria' => array(
                        'ria_company_information' => array(
                            'use_transaction_fees' => true,
                            'transaction_min_amount' => 400,
                            'transaction_min_amount_percent' => 1.5
                        )
                    )
                )
            )
        );


        $accountCollection = new AccountCollection();
        foreach ($accountsData as $key => $data) {
            /** @var Account $account */
            $account = $this->getMock('Model\WealthbotRebalancer\Account', null);
            $account->loadFromArray($data);

            $securityCollectionVar = 'securityCollection' . ($key + 1);
            $account->setSecurities($$securityCollectionVar);

            $accountCollection->add($account);
        }

        $client = $this->getMockClient(array('portfolio' => array('id' => 1)));
        $client->setAccounts($accountCollection);

        $lotRepository = $this->getMockLotRepository(array('findOrderedLots'));
        $lotRepository->expects($this->any())
            ->method('findOrderedLots')
            ->will($this->returnCallback(function (Security $security) use ($lots) {
                $lotCollection = new LotCollection();
                $lotCollection->add($lots[$security->getSubclass()->getId()]);
                return $lotCollection;
            }));

        $securityTransactionRepository = $this->getMockSecurityTransactionRepository(array('findOneByPortfolioAndSecurity'));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getJob', 'getClient', 'checkShortTermRedemption', 'checkTransaction', 'getRebalancerAction'), false);
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($lotRepository, $securityTransactionRepository) {
                if ($repositoryName === 'Lot') {
                    return $lotRepository;
                } elseif ($repositoryName === 'SecurityTransaction') {
                    return $securityTransactionRepository;
                }

                return null;
            }));
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array('id' => 1))));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($client));
        $rebalancer->expects($this->any())
            ->method('checkShortTermRedemption')
            ->will($this->returnValue(true));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($this->getMockRebalancerAction(array('id' => 2))));

        $rebalancer->buyRequiredCash();

        $queue = $rebalancer->getRebalancerQueue();
        $this->assertCount(3, $queue);

        $item1 = $queue[0];
        $this->assertEquals('Sec1', $item1->getSecurity()->getSymbol());
        $this->assertEquals(100, $item1->getQuantity());
        $this->assertEquals(0, $item1->getSecurity()->getQty());

        $item2 = $queue[1];
        $this->assertEquals('Sec2', $item2->getSecurity()->getSymbol());
        $this->assertEquals(8, $item2->getQuantity());
        $this->assertEquals(2, $item2->getSecurity()->getQty());

        $item3 = $queue[2];
        $this->assertEquals('Sec3', $item3->getSecurity()->getSymbol());
        $this->assertEquals(4, $item3->getQuantity());
        $this->assertEquals(16, $item3->getSecurity()->getQty());

        $this->assertEquals(6000, $accountCollection->get(1)->getTotalCash());
        $this->assertEquals(4300, $accountCollection->get(2)->getTotalCash());
    }

    public function testSellSubclassHousehold()
    {
        $client = $this->getMockClient(array(
            'accounts' => array(
                array(
                    'subclass' => array(
                        'id' => 12,
                        'security' => array(
                            'id' => 5
                        )
                    ),
                    'securities' => array(
                        array('id' => 5, 'amount' => 200),
                        array('id' => 50, 'amount' => 200)
                    ),
                    'type' => Account::TYPE_ROTH_IRA
                ),
                array(
                    'subclass' => array(
                        'id' => 13,
                        'security' => array(
                            'id' => 60
                        )
                    ),
                    'securities' => array(
                        array('id' => 5, 'amount' => 400),
                        array('id' => 60, 'amount' => 400)
                    ),
                    'type' => Account::TYPE_TRADITIONAL_IRA
                ),
                array(
                    'subclass' => array(
                        'id' => 14,
                        'security' => array(
                            'id' => 60
                        )
                    ),
                    'securities' => array(
                        array('id' => 5, 'amount' => 600),
                        array('id' => 60, 'amount' => 600)
                    ),
                    'type' => Account::TYPE_TRADITIONAL_IRA
                )
            )
        ));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('getClient', 'sellSubclass'));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($client));
        $rebalancer->expects($this->exactly(3))
            ->method('sellSubclass')
            ->will($this->returnCallback(function ($subclass, Account $account, $requiredCash, $dryRun) {
                $amount = 0;
                foreach ($account->getSecurities() as $security) {
                    $amount += $security->getAmount();
                }

                return $amount;
            }));

        $result = $rebalancer->sellSubclassHousehold($this->getMockSubclass(array('security' => array('id' => 5))), 1000);

        $this->assertEquals(1200, $result);
    }

    public function testBuySubclassHousehold()
    {
        $client = $this->getMockClient(array(
            'accounts' => array(
                array(
                    'subclass' => array(
                        'id' => 12,
                        'security' => array(
                            'id' => 5
                        )
                    ),
                    'securities' => array(
                        array('id' => 5, 'amount' => 200),
                        array('id' => 50, 'amount' => 200)
                    ),
                    'type' => Account::TYPE_ROTH_IRA,
                    'cash_for_buy' => 200
                ),
                array(
                    'subclass' => array(
                        'id' => 13,
                        'security' => array(
                            'id' => 60
                        )
                    ),
                    'securities' => array(
                        array('id' => 5, 'amount' => 100),
                        array('id' => 60, 'amount' => 100)
                    ),
                    'type' => Account::TYPE_TRADITIONAL_IRA,
                    'cash_for_buy' => 400
                ),
                array(
                    'subclass' => array(
                        'id' => 14,
                        'security' => array(
                            'id' => 60
                        )
                    ),
                    'securities' => array(
                        array('id' => 5, 'amount' => 600),
                        array('id' => 60, 'amount' => 600)
                    ),
                    'type' => Account::TYPE_TRADITIONAL_IRA,
                    'cash_for_buy' => 250
                )
            )
        ));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('getClient', 'buySubclass'));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($client));

        $rebalancer->expects($this->exactly(3))
            ->method('buySubclass')
            ->will($this->returnCallback(function ($subclass, Account $account, $cash, $dryRun) {
                $amount = 0;
                foreach ($account->getSecurities() as $security) {
                    $amount += $security->getAmount();
                }

                return $amount;
            }));

        $result = $rebalancer->buySubclassHousehold($this->getMockSubclass(array('security' => array('id' => 5))), 500);

        $this->assertEquals(600, $result);
    }

    public function testGetBuyAndSellLimit()
    {
        $account = $this->getMockAccount(array(
            'id' => 1
        ));

        $subclassesData = array(
            array(
                'id' => 1,
                'target_allocation' => 30,
                'current_allocation' => 25,
                'security' => array(
                    'id' => 1,
                    'amount' => 2500
                )
            ),
            array(
                'id' => 2,
                'target_allocation' => 10,
                'current_allocation' => 15,
                'security' => array(
                    'id' => 2,
                    'amount' => 1500
                )
            ),
            array(
                'id' => 3,
                'target_allocation' => 25,
                'current_allocation' => 35,
                'security' => array(
                    'id' => 3,
                    'amount' => 3500
                )
            ),
            array(
                'id' => 4,
                'target_allocation' => 15,
                'current_allocation' => 10,
                'security' => array(
                    'id' => 4,
                    'amount' => 1000
                )
            ),
            array(
                'id' => 5,
                'target_allocation' => 20,
                'current_allocation' => 15,
                'security' => array(
                    'id' => 5,
                    'amount' => 1500
                )
            )
        );

        $subclassCollection = new SubclassCollection();
        foreach ($subclassesData as $data) {
            $subclass = $this->getMockSubclass($data);
            $subclassCollection->add($subclass);
        }

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('sellSubclass', 'buySubclass'));
        $rebalancer->expects($this->exactly(2))
            ->method('sellSubclass')
            ->will($this->returnCallback(function ($subclass, $account, $amount) {
                return $amount;
            }));
        $rebalancer->expects($this->exactly(3))
            ->method('buySubclass')
            ->will($this->returnCallback(function ($subclass, $account, $amount) {
                return $amount;
            }));
        $this->assertEquals(2100, $rebalancer->getBuyAndSellLimit($subclassCollection, $account));
    }

    public function testGetBuyAndSellLimitHousehold()
    {
        $subclassesData = array(
            array(
                'id' => 1,
                'target_allocation' => 30,
                'current_allocation' => 25,
                'security' => array(
                    'id' => 1,
                    'amount' => 2500
                )
            ),
            array(
                'id' => 2,
                'target_allocation' => 10,
                'current_allocation' => 15,
                'security' => array(
                    'id' => 2,
                    'amount' => 1500
                )
            ),
            array(
                'id' => 3,
                'target_allocation' => 25,
                'current_allocation' => 35,
                'security' => array(
                    'id' => 3,
                    'amount' => 3500
                )
            ),
            array(
                'id' => 4,
                'target_allocation' => 15,
                'current_allocation' => 10,
                'security' => array(
                    'id' => 4,
                    'amount' => 1000
                )
            ),
            array(
                'id' => 5,
                'target_allocation' => 20,
                'current_allocation' => 15,
                'security' => array(
                    'id' => 5,
                    'amount' => 1500
                )
            )
        );

        $subclassCollection = new SubclassCollection();
        foreach ($subclassesData as $data) {
            $subclass = $this->getMockSubclass($data);
            $subclassCollection->add($subclass);
        }

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('sellSubclassHousehold', 'buySubclassHousehold'));
        $rebalancer->expects($this->exactly(2))
            ->method('sellSubclassHousehold')
            ->will($this->returnCallback(function ($subclass, $amount, $dryRun) {
                return $amount;
            }));
        $rebalancer->expects($this->exactly(3))
            ->method('buySubclassHousehold')
            ->will($this->returnCallback(function ($subclass, $amount, $dryRun) {
                return $amount;
            }));
        $this->assertEquals(2100, $rebalancer->getBuyAndSellLimit($subclassCollection));
    }

    public function testGetBuyAndSellLimitNotOutOfBalance()
    {
        $subclassesData = array(
            array(
                'id' => 1,
                'target_allocation' => 30,
                'current_allocation' => 30,
                'security' => array(
                    'id' => 1,
                    'amount' => 3000
                )
            ),
            array(
                'id' => 2,
                'target_allocation' => 10,
                'current_allocation' => 10,
                'security' => array(
                    'id' => 2,
                    'amount' => 1000
                )
            ),
            array(
                'id' => 3,
                'target_allocation' => 25,
                'current_allocation' => 25,
                'security' => array(
                    'id' => 3,
                    'amount' => 2500
                )
            ),
            array(
                'id' => 4,
                'target_allocation' => 15,
                'current_allocation' => 15,
                'security' => array(
                    'id' => 4,
                    'amount' => 1500
                )
            ),
            array(
                'id' => 5,
                'target_allocation' => 20,
                'current_allocation' => 20,
                'security' => array(
                    'id' => 5,
                    'amount' => 2000
                )
            )
        );

        $subclassCollection = new SubclassCollection();
        foreach ($subclassesData as $data) {
            $subclass = $this->getMockSubclass($data);
            $subclassCollection->add($subclass);
        }

        $this->assertEquals(0, $this->rebalancer->getBuyAndSellLimit($subclassCollection));
    }

    public function testRebalanceWithRequiredCashType()
    {
        $client = $this->getMockClient(array('id' => 1, 'account_managed' => Client::ACCOUNT_MANAGED_ACCOUNT));
        $job = $this->getMockJob(array('id' => 5, 'rebalance_type' => Job::REBALANCE_TYPE_REQUIRED_CASH));
        $rebalancer = $this->getMockWealthbotRebalancer(array('buyRequiredCash', 'getClient', 'getJob'));

        $rebalancer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($client));

        $rebalancer->expects($this->once())
            ->method('getJob')
            ->will($this->returnValue($job));

        $rebalancer->expects($this->once())->method('buyRequiredCash');
        $rebalancer->expects($this->never())->method('accountRebalance');
        $rebalancer->expects($this->never())->method('householdRebalance');

        $rebalancer->rebalance();
    }

    public function testRebalanceForAccountLevel()
    {
        $client = $this->getMockClient(array('id' => 1, 'account_managed' => Client::ACCOUNT_MANAGED_ACCOUNT));
        $job = $this->getMockJob(array('id' => 5, 'rebalance_type' => Job::REBALANCE_TYPE_FULL));
        $rebalancer = $this->getMockWealthbotRebalancer(array('buyRequiredCash', 'accountRebalance', 'getClient', 'getJob'));

        $rebalancer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($client));

        $rebalancer->expects($this->once())
            ->method('getJob')
            ->will($this->returnValue($job));

        $rebalancer->expects($this->once())->method('buyRequiredCash');
        $rebalancer->expects($this->once())->method('accountRebalance');
        $rebalancer->expects($this->never())->method('householdRebalance');

        $rebalancer->rebalance();
    }

    public function testRebalanceForHouseholdLevel()
    {
        $client = $this->getMockClient(array('id' => 1, 'account_managed' => Client::ACCOUNT_MANAGED_HOUSEHOLD));
        $job = $this->getMockJob(array('id' => 5, 'rebalance_type' => Job::REBALANCE_TYPE_FULL));
        $rebalancer = $this->getMockWealthbotRebalancer(array('buyRequiredCash', 'householdRebalance', 'getClient', 'getJob'));

        $rebalancer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($client));

        $rebalancer->expects($this->once())
            ->method('getJob')
            ->will($this->returnValue($job));

        $rebalancer->expects($this->once())->method('buyRequiredCash');
        $rebalancer->expects($this->once())->method('householdRebalance');
        $rebalancer->expects($this->never())->method('accountRebalance');

        $rebalancer->rebalance();
    }

    public function testRebalanceException()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('getClient'));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue(null));

        $this->setExpectedException('RuntimeException', 'Client is not defined.');

        $rebalancer->rebalance();
    }

    public function testIsBuyMuni()
    {
        $account = $this->getMockAccount(array(
            'client' => array(
                'ria' => array('is_use_municipal_bond' => false)
            )
        ));

        $subclass = $this->getMockSubclass(array(
            'muniSecurity' => array()
        ));

        $this->assertFalse($this->rebalancer->isBuyMuni($account, $subclass));

        $account = $this->getMockAccount(array(
            'type' => Account::TYPE_ROTH_IRA,
            'client' => array(
                'ria' => array(
                    'is_use_municipal_bond' => true
                )
            )
        ));
        $this->assertFalse($this->rebalancer->isBuyMuni($account, $subclass));

        $account = $this->getMockAccount(array(
            'type' => Account::TYPE_PERSONAL_INVESTMENT,
            'client' => array(
                'tax_bracket' => 0.9,
                'ria' => array(
                    'client_tax_bracket' => 1.1,
                    'is_use_municipal_bond' => true
                )
            )
        ));
        $this->assertFalse($this->rebalancer->isBuyMuni($account, $subclass));

        $account = $this->getMockAccount(array(
            'type' => Account::TYPE_PERSONAL_INVESTMENT,
            'client' => array(
                'tax_bracket' => 0.9,
                'ria' => array(
                    'client_tax_bracket' => 0.9,
                    'is_use_municipal_bond' => true
                )
            )
        ));
        $this->assertTrue($this->rebalancer->isBuyMuni($account, $subclass));

        $account = $this->getMockAccount(array(
            'type' => Account::TYPE_PERSONAL_INVESTMENT,
            'client' => array(
                'tax_bracket' => 0.9,
                'ria' => array(
                    'client_tax_bracket' => 0.2,
                    'is_use_municipal_bond' => true
                )
            )
        ));
        $this->assertTrue($this->rebalancer->isBuyMuni($account, $subclass));
    }

    public function testCheckShortTermRedemption()
    {
        $client = $this->getMockClient(array(
            'portfolio' => array(
                'id' => 1
            )
        ));

        $security = $this->getMockSecurity(array(
            'id' => 5,
            'subclass' => array(
                'id' => 3
            )
        ));

        $date = new \DateTime();
        $lot = $this->getMockLot(array(
            'id' => 13,
            'date' => $date->modify('-6 day')
        ));

        $securityTransaction = $this->getMockSecurityTransaction(array(
            'redemption_fee' => 100,
            'redemption_penalty_interval' => 20
        ));

        $repository = $this->getMockSecurityTransactionRepository(array('findOneByPortfolioAndSecurity'));
        $repository->expects($this->any())
            ->method('findOneByPortfolioAndSecurity')
            ->will($this->returnValue($securityTransaction));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getClient'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($repository) {
                if ($repositoryName === 'SecurityTransaction') {
                    return $repository;
                }

                return null;
            }));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($client));

        $this->assertTrue($rebalancer->checkShortTermRedemption($security, $lot));

        $securityTransaction->setRedemptionFee(0);
        $this->assertFalse($rebalancer->checkShortTermRedemption($security, $lot));

        $securityTransaction->setRedemptionFee(100);
        $securityTransaction->setRedemptionPenaltyInterval(5);
        $this->assertFalse($rebalancer->checkShortTermRedemption($security, $lot));

        $lot->setDate(new \DateTime('2014-04-08'));
        $this->assertFalse($rebalancer->checkShortTermRedemption($security, $lot));
    }

    public function testSellLotException()
    {
        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer();

        $securityDoesNotSold = $this->getMockSecurity(array(), array('isCanBeSold'));
        $securityDoesNotSold->expects($this->any())
            ->method('isCanBeSold')
            ->will($this->returnValue(false));

        $this->setExpectedException('Exception');

        $rebalancer->sellLot(
            $this->getMockLot(),
            $securityDoesNotSold,
            $this->getMockAccount(),
            1000
        );
    }

    public function testSellLot()
    {
        /** @var WealthbotRebalancer $rebalancer */
         $rebalancer = $this->getMockWealthbotRebalancer(array('getJob', 'getRebalancerAction', 'checkTransaction'), false);
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob(array('id' => 4))));
        $rebalancer->expects($this->any())
            ->method('checkTransaction')
            ->will($this->returnValue(true));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($this->getMockRebalancerAction(array('id' => 2))));

        $account = $this->getMockAccount(array('id' => 6));

        $security = $this->getMockSecurity(array(
            'amount' => 4000,
            'qty' => 200
        ), array('isCanBeSold'));
        $security->expects($this->any())
            ->method('isCanBeSold')
            ->will($this->returnValue(true));

        //-------------------------------------------------//
        $result = $rebalancer->sellLot(
            $this->getMockLot(array('amount' => 500)),
            $security,
            $account,
            1000,
            true
        );
        $this->assertEquals(500, $result);
        //-------------------------------------------------//
        $result = $rebalancer->sellLot(
            $this->getMockLot(array('amount' => 2000, 'quantity' => 100)),
            $security,
            $account,
            1000
        );

        $this->assertEquals(1000, $result);

        $rebalancerQueue = $rebalancer->getRebalancerQueue();
        $this->assertCount(1, $rebalancerQueue);

        /** @var QueueItem $queueItem */
        $queueItem = $rebalancerQueue->first();
        $this->assertEquals(1000, $queueItem->getLot()->getAmount());
        $this->assertEquals(50, $queueItem->getLot()->getQuantity());
        $this->assertEquals(150, $queueItem->getSecurity()->getQty());
        $this->assertEquals(3000, $queueItem->getSecurity()->getAmount());
        $this->assertEquals(6, $queueItem->getAccount()->getId());
        $this->assertEquals(50, $queueItem->getQuantity());
        $this->assertEquals(1000, $queueItem->getAmount());
        $this->assertEquals(QueueItem::STATUS_SELL, $queueItem->getStatus());
    }

    public function testCheckTlh()
    {
        $account = $this->getMockAccount(array(), array('isTaxable'));
        $account->expects($this->any())
            ->method('isTaxable')
            ->will($this->returnValue(true));

        $client = $this->getMockClient(array(
            'ria' => array('is_tlh_enabled' => true, 'min_tlh' => 200, 'min_tlh_percent' => 5),
            'stop_tlh_value' => 50
        ), array('canUseTlh'));
        $client->expects($this->any())
            ->method('canUseTlh')
            ->will($this->returnValue(true));

        $account->setClient($client);

        $subclass = $this->getMockSubclass(array(
            'tax_loss_harvesting' => array('amount' => 0),
            'security' => array('amount' => 7600)
        ));

        $lotRepository = $this->getMockLotRepository(array('findLotsByAccountAndSecurity', 'getClientLossesSumForYear'));
        $lotRepository->expects($this->any())
            ->method('findLotsByAccountAndSecurity')
            ->will($this->returnValue($this->getMockLotCollection(array(
                array('realized_gain_or_loss' => 500, 'cost_basis' => 8000,'amount' => 1000),
                array('realized_gain_or_loss' => -100, 'cost_basis' => 13000, 'amount' => 2000),
                array('realized_gain_or_loss' => -500, 'cost_basis' => 3000, 'amount' => 600),
                array('realized_gain_or_loss' => -500, 'cost_basis' => 30000, 'amount' => 4000),
            ))));
        $lotRepository->expects($this->any())
            ->method('getClientLossesSumForYear')
            ->will($this->returnValue(100));

        $clientRepository = $this->getMockClientRepository(array('loadStopTlhValue'));

        /** @var WealthbotRebalancer $rebalancer */
        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'buySecurity', 'sellLot'), false);
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($lotRepository, $clientRepository)  {
                switch ($repositoryName) {
                    case 'Lot':
                        return $lotRepository;
                    case 'Client':
                        return $clientRepository;
                    default:
                        return null;
                }
            }));
        $rebalancer->expects($this->any())
            ->method('buySecurity')
            ->will($this->returnCallback(function (Security $security, Account $account, $amount) {
                $security->setAmount($security->getAmount() + $amount);
                return $amount;
            }));
        $rebalancer->expects($this->any())
            ->method('sellLot')
            ->will($this->returnCallback(function (Lot $lot, Security $security, Account $account, $amount) {
                $security->setAmount($security->getAmount() - $amount);
                return $amount;
            }));

        $rebalancer->checkTlh($account, $subclass);

        $this->assertEquals(4000, $subclass->getTaxLossHarvesting()->getAmount());
        $this->assertEquals(3600, $subclass->getSecurity()->getAmount());
    }

    public function testOperateSubclassesWithoutTrades()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array('getBuyAndSellLimit'));
        $rebalancer->expects($this->any())
            ->method('getBuyAndSellLimit')
            ->will($this->returnValue(0));

        $account = $this->getMockAccount();
        $subclassCollection = $this->getMockSubclassCollection(array(
            array('target_allocation' => 40, 'security' => array('amount' => 30)),
            array('target_allocation' => 60, 'security' => array('amount' => 70))
        ));

        $result = $rebalancer->operateSubclasses($subclassCollection, $account);

        $this->assertCount(0, $result);
    }


    public function testOperateSubclasses()
    {
        $rebalancer = $this->getMockWealthbotRebalancer(array(
            'getBuyAndSellLimit',
            'sellSubclass',
            'sellSubclassHousehold',
            'buySubclass',
            'buySubclassHousehold'
        ));
        $rebalancer->expects($this->any())
            ->method('getBuyAndSellLimit')
            ->will($this->returnValue(100));

        $subclassCollection = $this->getMockSubclassCollection(array(
            array('target_allocation' => 40, 'security' => array('amount' => 30)),
            array('target_allocation' => 60, 'security' => array('amount' => 70))
        ));

        $rebalancer->expects($this->exactly(1))
            ->method('buySubclassHousehold');
        $rebalancer->expects($this->exactly(1))
            ->method('sellSubclassHousehold');
        $result = $rebalancer->operateSubclasses($subclassCollection);
        $this->assertCount(2, $result);

        $rebalancer->expects($this->exactly(1))
            ->method('buySubclass');
        $rebalancer->expects($this->exactly(1))
            ->method('sellSubclass');
        $result = $rebalancer->operateSubclasses($subclassCollection, $this->getMockAccount());
        $this->assertCount(2, $result);
    }

    public function testGenerateTradeFile()
    {
        $tradeDataCollection = $this->getMockTradeDataCollection(array(
            array(
                'job_id' => 1,
                'security_id' => 1,
                'account_id' => 1,
                'account_number' => '916985328',
                'account_type' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
                'security_type' => TradeData::SECURITY_TYPE_EQUITY,
                'action' => TradeData::ACTION_SELL,
                'quantity_type' => TradeData::QUANTITY_TYPE_SHARES,
                'quantity' => '43',
                'symbol' => 'RWX',
                'vsps' => array(
                    array(
                        'purchase' => 'VSP',
                        'purchase_date' => '02132013',
                        'quantity' => 23
                    ),
                    array(
                        'purchase' => 'VSP',
                        'purchase_date' => '02162013',
                        'quantity' => 20
                    )
                )
            ),
            array(
                'job_id' => 1,
                'security_id' => 2,
                'account_id' => 2,
                'account_number' => '480888811',
                'account_type' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
                'security_type' => TradeData::SECURITY_TYPE_EQUITY,
                'action' => TradeData::ACTION_BUY,
                'quantity_type' => TradeData::QUANTITY_TYPE_SHARES,
                'quantity' => '12',
                'symbol' => 'VCIT'
            ),
            array(
                'job_id' => 1,
                'security_id' => 3,
                'account_id' => 3,
                'account_number' => '122223334',
                'account_type' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
                'security_type' => TradeData::SECURITY_TYPE_EQUITY,
                'action' => TradeData::ACTION_BUY,
                'quantity_type' => TradeData::QUANTITY_TYPE_SHARES,
                'quantity' => '1',
                'symbol' => 'BND',
            ),
            array(
                'job_id' => 1,
                'security_id' => 4,
                'account_id' => 1,
                'account_number' => '916985328',
                'account_type' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
                'security_type' => Tradedata::SECURITY_TYPE_EQUITY,
                'action' => TradeData::ACTION_SELL,
                'quantity_type' => TradeData::QUANTITY_TYPE_ALL_SHARES,
                'quantity' => 10,
                'symbol' => 'VGIT'
            )
        ));

        $rebalancerQueueRepository = $this->getMockRebalancerQueueRepository(array('getTradeDataCollectionForJob'));
        $rebalancerQueueRepository->expects($this->any())
            ->method('getTradeDataCollectionForJob')
            ->will($this->returnValue($tradeDataCollection));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getJob'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($rebalancerQueueRepository) {
                if ($repositoryName === 'RebalancerQueue') {
                    return $rebalancerQueueRepository;
                }
                return null;
            }));
        $rebalancer->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($this->getMockJob()));


        $dir = __DIR__.'/../../../outgoing_files/trade_files/';
        $expectedFilesCount = count(scandir($dir))+1;

        $rebalancer->generateTradeFile();

        $files = scandir($dir);

        $this->assertCount($expectedFilesCount, $files);

        $file = new \SplFileObject($dir.end($files), 'r');

        $fileData = array();
        while ($data = $file->fgetcsv()) {
            $fileData[] = $data;
        }

        $i = 0;
        /** @var TradeData $tradeData */
        foreach ($tradeDataCollection as $tradeData) {
            $tradeArray = array_values($tradeData->toArrayForTradeFile());
            $this->assertEquals($tradeArray, $fileData[$i]);

            $i++;

            if ($tradeData->getAction() === TradeData::ACTION_SELL) {
                foreach ($tradeData->getVsps() as $vsp) {
                    $this->assertEquals(array_values($vsp), $fileData[$i]);
                    $i++;
                }
            }
        }
    }

    public function testSetRebalancerActionStatus()
    {
        $rebalancerAction = $this->getMockRebalancerAction(array('status' => Job::REBALANCE_TYPE_FULL_AND_TLH));

        $rebalancerActionRepository = $this->getMockRebalancerActionRepository(array('saveStatus'));
        $rebalancerActionRepository->expects($this->once())
            ->method('saveStatus')
            ->will($this->returnCallback(function (RebalancerAction $rebalancerAction) {
                return $rebalancerAction->getStatus();
            }));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getRebalancerAction', 'getClient', 'getRebalancerQueue'));

        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($rebalancerActionRepository) {
                if ($repositoryName === 'RebalancerAction') {
                    return $rebalancerActionRepository;
                }
                return null;
            }));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($this->getMockClient(array(
                'account_managed' => Client::ACCOUNT_MANAGED_HOUSEHOLD
            ))));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($rebalancerAction));
        $rebalancer->expects($this->any())
            ->method('getRebalancerQueue')
            ->will($this->returnValue($this->getMockRebalancerQueue(array(array(), array()))));

        $this->assertEquals($rebalancerAction->getStatus(), $rebalancer->setRebalanceActionStatus());
    }

    public function testSetRebalancerActionStatusAccountNotInitial()
    {
        $rebalancerAction = $this->getMockRebalancerAction(array('status' => Job::REBALANCE_TYPE_FULL_AND_TLH));


        $rebalancerActionRepository = $this->getMockRebalancerActionRepository(array('saveStatus'));
        $rebalancerActionRepository->expects($this->once())
            ->method('saveStatus')
            ->will($this->returnCallback(function (RebalancerAction $rebalancerAction) {
                return $rebalancerAction->getStatus();
            }));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getRebalancerAction', 'getClient', 'getRebalancerQueue'));

        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($rebalancerActionRepository) {
                if ($repositoryName === 'RebalancerAction') {
                    return $rebalancerActionRepository;
                }
                return null;
            }));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($this->getMockClient(array(
                'account_managed' => Client::ACCOUNT_MANAGED_ACCOUNT,
                'accounts' => array(array('status' => Account::STATUS_CLOSED))
            ))));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($rebalancerAction));
        $rebalancer->expects($this->any())
            ->method('getRebalancerQueue')
            ->will($this->returnValue($this->getMockRebalancerQueue(array(array(), array()))));

        $this->assertEquals($rebalancerAction->getStatus(), $rebalancer->setRebalanceActionStatus());
    }

    public function testSetRebalancerActionStatusInitial()
    {
        $rebalancerAction = $this->getMockRebalancerAction(array('status' => Job::REBALANCE_TYPE_FULL_AND_TLH));

        $rebalancerActionRepository = $this->getMockRebalancerActionRepository(array('saveStatus'));
        $rebalancerActionRepository->expects($this->once())
            ->method('saveStatus')
            ->will($this->returnCallback(function (RebalancerAction $rebalancerAction) {
                return $rebalancerAction->getStatus();
            }));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getRebalancerAction', 'getClient', 'getRebalancerQueue'));

        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($rebalancerActionRepository) {
                if ($repositoryName === 'RebalancerAction') {
                    return $rebalancerActionRepository;
                }
                return null;
            }));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($this->getMockClient(array(
                'account_managed' => Client::ACCOUNT_MANAGED_ACCOUNT,
                'accounts' => array(array('status' => Account::STATUS_INIT_REBALANCE))
            ))));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($rebalancerAction));
        $rebalancer->expects($this->any())
            ->method('getRebalancerQueue')
            ->will($this->returnValue($this->getMockRebalancerQueue(array(array(), array()))));

        $this->assertEquals(JOB::REBALANCE_TYPE_INITIAL, $rebalancer->setRebalanceActionStatus());
    }

    public function testSetRebalancerActionStatusNoActions()
    {
        $rebalancerAction = $this->getMockRebalancerAction(array('status' => Job::REBALANCE_TYPE_FULL_AND_TLH));

        $rebalancerActionRepository = $this->getMockRebalancerActionRepository(array('saveStatus'));
        $rebalancerActionRepository->expects($this->once())
            ->method('saveStatus')
            ->will($this->returnCallback(function (RebalancerAction $rebalancerAction) {
                return $rebalancerAction->getStatus();
            }));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getRebalancerAction', 'getClient', 'getRebalancerQueue'));

        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($rebalancerActionRepository) {
                if ($repositoryName === 'RebalancerAction') {
                    return $rebalancerActionRepository;
                }
                return null;
            }));
        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($this->getMockClient(array(
                'account_managed' => Client::ACCOUNT_MANAGED_ACCOUNT,
                'accounts' => array(array('status' => Account::STATUS_INIT_REBALANCE))
            ))));
        $rebalancer->expects($this->any())
            ->method('getRebalancerAction')
            ->will($this->returnValue($rebalancerAction));
        $rebalancer->expects($this->any())
            ->method('getRebalancerQueue')
            ->will($this->returnValue($this->getMockRebalancerQueue(array())));

        $this->assertEquals(JOB::REBALANCE_TYPE_NO_ACTIONS, $rebalancer->setRebalanceActionStatus());
    }

    public function testCheckTransactionNoFees()
    {

        $rebalancer = $this->getMockWealthbotRebalancer();
        $account = $this->getMockAccount(array(
            'client' => array(
                'ria' => array(
                    'ria_company_information' => array(
                        'use_transaction_fees' => false
                    )
                )
            )
        ));
        $this->assertTrue($rebalancer->checkTransaction($account, 0));
    }

    public function testCheckTransactionMinAmount()
    {
        $rebalancer = $this->getMockWealthbotRebalancer();
        $account = $this->getMockAccount(array(
            'client' => array(
                'ria' => array(
                    'ria_company_information' => array(
                        'use_transaction_fees' => true,
                        'transaction_min_amount' => 500
                    )
                )
            )
        ));
        $this->assertTrue($rebalancer->checkTransaction($account, 600));
    }

    public function testCheckTransactionMinAmountPercent()
    {
        $rebalancer = $this->getMockWealthbotRebalancer();
        $account = $this->getMockAccount(array(
            'totalCash' => 20000,
            'client' => array(
                'ria' => array(
                    'ria_company_information' => array(
                        'use_transaction_fees' => true,
                        'transaction_min_amount' => 400,
                        'transaction_min_amount_percent' => 1.5
                    )
                )
            )
        ));
        $this->assertTrue($rebalancer->checkTransaction($account, 399));
    }

    public function testCheckTransactionBuySellMins()
    {

        $account = $this->getMockAccount(array(
            'totalCash' => 20000,
            'client' => array(
                'portfolio' => array(
                    'id' => 1
                ),
                'ria' => array(
                    'ria_company_information' => array(
                        'use_transaction_fees' => true,
                        'transaction_min_amount' => 400,
                        'transaction_min_amount_percent' => 1.5
                    )
                )
            )
        ));

        $client = $this->getMockClient(array(
            'portfolio' => array(
                'id' => 1
            )
        ));

        $security = $this->getMockSecurity(array(
            'id' => 5,
            'subclass' => array(
                'id' => 3
            )
        ));

        $date = new \DateTime();
        $lot = $this->getMockLot(array(
            'id' => 13,
            'date' => $date->modify('-6 day')
        ));

        $securityTransaction = $this->getMockSecurityTransaction(array(
            'minimum_buy' => 1000,
            'minimum_sell' => 2000
        ));

        $repository = $this->getMockSecurityTransactionRepository(array('findOneByPortfolioAndSecurity'));
        $repository->expects($this->any())
            ->method('findOneByPortfolioAndSecurity')
            ->will($this->returnValue($securityTransaction));

        $rebalancer = $this->getMockWealthbotRebalancer(array('getRepository', 'getClient'));
        $rebalancer->expects($this->any())
            ->method('getRepository')
            ->will($this->returnCallback(function ($repositoryName) use ($repository) {
                if ($repositoryName === 'SecurityTransaction') {
                    return $repository;
                }

                return null;
        }));

        $rebalancer->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($client));


        $this->assertTrue($rebalancer->checkTransaction($account, 5000, $security, 'buy'));
        $this->assertTrue($rebalancer->checkTransaction($account, 5000, $security, 'sell'));
        $this->assertFalse($rebalancer->checkTransaction($account, 100, $security, 'sell'));
    }


    public function testCheckTransactionZeroTotal()
    {
        $account = $this->getMockAccount(array(
            'totalCash' => 0,
            'client' => array(
                'ria' => array('id' => 1)
                )
        ));
        $rebalancer = $this->getMockWealthbotRebalancer();

        $this->assertFalse($rebalancer->checkTransaction($account, 1000));
    }


    public function testWashSale()
    {
        $rebalancer = $this->getMockWealthbotRebalancer();
        $date = new \DateTime();
        $lot = $this->getMockLot(array(
            'id' => 13,
            'date' => $date->modify('-3 day'),
            'was_closed' => true,
            'is_loss' => true,
        ));

        $this->assertTrue($rebalancer->washSale($lot));
    }

    public function testCheckShortTermGain()
    {
        $rebalancer = $this->getMockWealthbotRebalancer();
        $date = new \DateTime();
        $lot = $this->getMockLot(array(
            'id' => 13,
            'date' => $date->modify('-100 day'),
            'was_closed' => false,
            'initial' => array('id' => 1, 'date' => $date->modify('-100 day')),
            'initial_lot_id' => 1,
        ));

        $this->assertTrue($rebalancer->checkShortTermGain($lot));
    }

//    public function setRebalanceActionStatus()
//    {
//        $client = $this->getClient();
//        $rebalancerAction = $this->getRebalancerAction();
//        $job = $this->getJob();
//        /** @var ArrayCollection $rebalancerQueue */
//        $rebalancerQueue = $this->getRebalancerQueue();
//
//        if ($this->getClient()->isAccountLevelRebalancer()) {
//            /** @var Account $account */
//            $account = $client->getAccounts()->first();
//            if (Account::STATUS_INIT_REBALANCE === $account->getStatus()) {
//                $rebalancerAction->setStatus(Job::REBALANCE_TYPE_INITIAL);
//            }
//        }
//
//        if ($rebalancerQueue->isEmpty()) {
//            $rebalancerAction->setStatus(Job::REBALANCE_TYPE_NO_ACTIONS);
//        }
//
//        $this->getRepository('RebalancerAction')->saveStatus($rebalancerAction);
//    }

    /**
     * @param array $methods
     * @return PortfolioRepository
     */
    private function getMockPortfolioRepository(array $methods = array())
    {
        /** @var PortfolioRepository $repositoryMock */
        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\PortfolioRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repositoryMock;
    }

    /**
     * @param array $methods
     * @return SecurityRepository
     */
    private function getMockSecurityRepository(array $methods = array())
    {
        /** @var SubclassRepository $repositoryMock */
        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\SecurityRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repositoryMock;
    }

    /**
     * @param array $methods
     * @return SubclassRepository
     */
    private function getMockSubclassRepository(array $methods = array())
    {
        /** @var SubclassRepository $repositoryMock */
        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\SubclassRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repositoryMock;
    }

    /**
     * @param array $methods
     * @return RebalancerActionRepository
     */
    private function getMockRebalancerActionRepository(array $methods = array())
    {
        /** @var RebalancerActionRepository $repositoryMock */
        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\RebalancerActionRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repositoryMock;
    }

    /**
     * @param array $methods
     * @return ClientRepository
     */
    private function getMockClientRepository(array $methods = array())
    {
        /** @var ClientRepository $repositoryMock */
        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\ClientRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repositoryMock;
    }

    /**
     * @param array $methods
     * @return JobRepository
     */
    private function getMockJobRepository(array $methods = array())
    {
        /** @var JobRepository $repositoryMock */
        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\JobRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repositoryMock;
    }

    /**
     * @param array $methods
     * @return RiaCompanyInformationRepository
     */
    private function getMockRiaCompanyInformationRepository(array $methods = array())
    {
        /** @var RiaCompanyInformationRepository $repositoryMock */
        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\RiaCompanyInformationRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repositoryMock;
    }

    /**
     * @param array $data
     * @return ArrayCollection
     */
    private function getMockJobCollection(array $data = array())
    {
        $jobCollection = new ArrayCollection();

        foreach ($data as $item) {
            $job = $this->getMockJob($item);
            $jobCollection->add($job);

        }

        return $jobCollection;
    }

    /**
     * @param array $data
     * @return ArrayCollection
     */
    private function getMockRebalancerActionCollection(array $data = array())
    {
        $rebalancerActionCollection = new ArrayCollection();

        foreach ($data as $item) {
            $rebalancerAction = $this->getMockRebalancerAction($item);
            $rebalancerActionCollection->add($rebalancerAction);
        }

        return $rebalancerActionCollection;
    }

    /**
     * @param array $data
     * @return RebalancerAction
     */
    private function getMockRebalancerAction(array $data = array())
    {
        /** @var RebalancerAction $rebalancerAction */
        $rebalancerAction = $this->getMock('Model\WealthbotRebalancer\RebalancerAction', null);
        $rebalancerAction->loadFromArray($data);

        return $rebalancerAction;
    }

    /**
     * @param array $data
     * @param array $methods
     * @return SubclassCollection
     */
    private function getMockSubclassCollection(array $data = null, array $methods = null)
    {
        if (null !== $data) {
            $subclassCollectionData = $data;
        } else {
            $subclassCollectionData = array(
                array(
                    'id' => 5,
                    'current_allocation' => 60,
                    'target_allocation' => 70,
                    'tolerance_band' => 14
                ),
                array(
                    'id' => 6,
                    'current_allocation' => 40,
                    'target_allocation' => 30,
                    'tolerance_band' => 7
                )
            );
        }

        /** @var SubclassCollection $subclassCollection */
        $subclassCollection = $this->getMock('Model\WealthbotRebalancer\SubclassCollection', $methods);

        foreach ($subclassCollectionData as $values) {
            $subclass = $this->getMockSubclass($values);
            $subclassCollection->add($subclass);
        }

        return $subclassCollection;
    }

//    private function getMockAssetClassRepository()
//    {
//        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\AssetClassRepository')
//            ->disableOriginalConstructor()
//            ->setMethods(array('bindAllocations'))
//            ->getMock();
//
//        $repositoryMock->expects($this->any())
//            ->method('bindAllocations')
//            ->will($this->returnValue($this->getMockAssetClassCollection(array(
//                array(
//                    'id' => 1,
//                    'current_allocation' => 33,
//                    'target_allocation' => 40,
//                    'tolerance_band' => 12
//                ),
//                array(
//                    'id' => 2,
//                    'current_allocation' => 77,
//                    'target_allocation' => 60,
//                    'tolerance_band' => 40
//                )
//            ))));
//
//        return $repositoryMock;
//    }
//
    private function getMockRebalancerQueueRepository(array $methods = array())
    {
        $repositoryMock = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\RebalancerQueueRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repositoryMock;
    }

    /**
     * @param array $data
     * @return ArrayCollection
     */
    private function getMockRebalancerQueue(array $data = array())
    {
        $rebalancerQueue = $this->getMock('Model\WealthbotRebalancer\RebalancerQueue', null);

        foreach ($data as $item) {
            $queueItem = $this->getMockQueueItem($item);
            $rebalancerQueue->add($queueItem);
        }

        return $rebalancerQueue;
    }

    /**
     * @param array $data
     * @return QueueItem
     */
    private function getMockQueueItem(array $data = array())
    {
        /** @var QueueItem $rebalancerQueue */
        $rebalancerQueue = $this->getMock('Model\WealthbotRebalancer\QueueItem', null);
        $rebalancerQueue->loadFromArray($data);

        return $rebalancerQueue;
    }


//
//    /**
//     * @param array $data
//     * @return AssetClassCollection
//     */
//    private function getMockAssetClassCollection(array $data = array())
//    {
//        $assetClassCollection = new AssetClassCollection();
//
//        foreach ($data as $item) {
//            $assetClass = $this->getMockAssetClass($item);
//            $assetClassCollection->add($assetClass);
//        }
//
//        return $assetClassCollection;
//    }

//    /**
//     * @param array $data
//     * @return AssetClass
//     */
//    private function getMockAssetClass(array $data = array())
//    {
//        /** @var AssetClass $assetClass */
//        $assetClass = $this->getMock('Model\WealthbotRebalancer\AssetClass', null);
//        $assetClass->loadFromArray($data);
//
//        return $assetClass;
//    }

    /**
     * @param array $data
     * @return Portfolio
     */
    private function getMockPortfolio(array $data = array())
    {
        /** @var Portfolio $portfolio */
        $portfolio = $this->getMock('Model\WealthbotRebalancer\Portfolio', null);
        $portfolio->loadFromArray($data);

        return $portfolio;
    }

    /**
     * Get collection of securities mock
     *
     * @param array $data
     * @return SecurityCollection
     */
    private function getMockSecurityCollection(array $data = array())
    {
        $collection = new SecurityCollection();

        foreach ($data as $item) {
            $security = $this->getMockSecurity($item);
            $collection->add($security);
        }

        return $collection;
    }

    /**
     * @param array $data
     * @return Account
     */
    private function getMockAccount(array $data = array(), array $methods = null)
    {
        /** @var Account $accountMock */
        $accountMock = $this->getMock('Model\WealthbotRebalancer\Account', $methods);
        $accountMock->loadFromArray($data);

        return $accountMock;
    }

    /**
     * @param array $data
     * @return Ria
     */
    private function getMockRia(array $data = array())
    {
        /** @var Ria $riaMock */
        $riaMock = $this->getMock('Model\WealthbotRebalancer\Ria', null);
        $riaMock->loadFromArray($data);

        return $riaMock;
    }

    /**
     * @param array $data
     * @return RiaCompanyInformation
     */
    private function getMockRiaCompanyInformarion(array $data = array())
    {
        /** @var RiaCompanyInformation $riaCompanyInformationMock */
        $riaCompanyInformationMock = $this->getMock('Model\WealthbotRebalancer\RiaCompanyInformation', null);
        $riaCompanyInformationMock->loadFromArray($data);

        return $riaCompanyInformationMock;
    }

    /**
     * @param array $data
     * @return ArrayCollection
     */
    private function getMockTradeDataCollection(array $data = array())
    {
        $tradeDataCollection = new ArrayCollection();

        foreach ($data as $item) {
            $tradeData = $this->getMockTradeData($item);
            $tradeDataCollection->add($tradeData);
        }

        return $tradeDataCollection;
    }

    /**
     * @param array $data
     * @return TradeData
     */
    private function getMockTradeData(array $data = array())
    {
        /** @var TradeData $tradeData */
        $tradeData = $this->getMock('Model\WealthbotRebalancer\TradeData', null);
        $tradeData->loadFromArray($data);

        return $tradeData;
    }

    /**
     * @param array $data
     * @return Client
     */
    private function getMockClient(array $data = array(), array $methods = null)
    {
        /** @var Client $clientMock */
        $clientMock = $this->getMock('Model\WealthbotRebalancer\Client', $methods);
        $clientMock->loadFromArray($data);

        return $clientMock;
    }

    /**
     * Get lot mock
     *
     * @param array $data
     * @return Lot
     */
    private function getMockLot(array $data = array())
    {
        /** @var Lot $lotMock */
        $lotMock = $this->getMock('Model\WealthbotRebalancer\Lot', null);
        $lotMock->loadFromArray($data);

        return $lotMock;
    }

    /**
     * Get lot collection mock
     *
     * @param array $data
     * @return LotCollection
     */
    private function getMockLotCollection(array $data = array())
    {
        /** @var LotCollection $lotCollectionMock */
        $lotCollectionMock = $this->getMock('Model\WealthbotRebalancer\LotCollection', null);

        foreach ($data as $item) {
            $lotMock = $this->getMockLot($item);
            $lotCollectionMock->add($lotMock);
        }

        return $lotCollectionMock;
    }

    /**
     * Get account collection mock
     *
     * @param array $data
     * @return AccountCollection
     */
    private function getMockAccountCollection(array $data = array())
    {
        /** @var AccountCollection $collectionMock */
        $collectionMock = $this->getMock('Model\WealthbotRebalancer\AccountCollection', null);

        foreach ($data as $item) {
            $lotMock = $this->getMockAccount($item);
            $collectionMock->add($lotMock);
        }

        return $collectionMock;
    }

    /**
     * @param array $data
     * @return Position
     */
    public function getMockPosition(array $data = array())
    {
        /** @var Position $positionMock */
        $positionMock = $this->getMock('Model\WealthbotRebalancer\Position', null);
        $positionMock->loadFromArray($data);

        return $positionMock;
    }

    /**
     * @param array $data
     * @return Job
     */
    private function getMockJob(array $data = array())
    {
        /** @var Job $jobMock */
        $jobMock = $this->getMock('Model\WealthbotRebalancer\Job', null);
        $jobMock->loadFromArray($data);

        return $jobMock;
    }

    /**
     * @param array $data
     * @return Security
     */
    private function getMockSecurity(array $data = array(), $methods = null)
    {
        /** @var Security $securityMock */
        $securityMock = $this->getMock('Model\WealthbotRebalancer\Security', $methods);
        $securityMock->loadFromArray($data);

        return $securityMock;
    }

    /**
     * @param array $data
     * @return Subclass
     */
    private function getMockSubclass(array $data = array())
    {
        /** @var Security $subclassMock */
        $subclassMock = $this->getMock('Model\WealthbotRebalancer\Subclass', null);
        $subclassMock->loadFromArray($data);

        return $subclassMock;
    }

    /**
     * @param array $data
     * @return SecurityTransaction
     */
    private function getMockSecurityTransaction(array $data = array())
    {
        $securityTransactionMock = $this->getMock('Model\WealthbotRebalancer\SecurityTransaction', null);
        $securityTransactionMock->loadFromArray($data);

        return $securityTransactionMock;
    }

    /**
     * @param array|null $methods
     * @param bool $disableConstructor
     * @return WealthbotRebalancer
     */
    private function getMockWealthbotRebalancer(array $methods = null, $disableConstructor = true)
    {
        /** @var WealthbotRebalancer $rebalancerMock */
        $builder = $this->getMockBuilder('Console\WealthbotRebalancer');

        if ($disableConstructor) {
            $builder->disableOriginalConstructor();
        }

        $rebalancerMock = $builder->setMethods($methods)->getMock();

        $logger = $this->getMockBuilder('Lib\Logger\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $rebalancerMock->logger = $logger;

        return $rebalancerMock;
    }

    /**
     * @param array $methods
     * @return LotRepository
     */
    private function getMockRiaRepository(array $methods = array())
    {
        /** @var LotRepository $mockRepository */
        $mockRepository = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\RiaRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $mockRepository;
    }

    /**
     * @param array $methods
     * @return LotRepository
     */
    private function getMockLotRepository(array $methods = array())
    {
        /** @var LotRepository $mockRepository */
        $mockRepository = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\LotRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $mockRepository;
    }

    /**
     * @param array $methods
     * @return AccountRepository
     */
    private function getMockAccountRepository(array $methods = array())
    {
        /** @var AccountRepository $mockRepository */
        $mockRepository = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\AccountRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $mockRepository;
    }

    /**
     * @param array $methods
     * @return SecurityTransactionRepository
     */
    private function getMockSecurityTransactionRepository(array $methods = array())
    {
        /** @var LotRepository $mockRepository */
        $mockRepository = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\SecurityTransactionRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $mockRepository;
    }

}
