<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\ArrayCollection;
use Model\WealthbotRebalancer\QueueItem;
use Model\WealthbotRebalancer\Repository\AccountRepository;
use Model\WealthbotRebalancer\Repository\JobRepository;
use Model\WealthbotRebalancer\Repository\RebalancerActionRepository;
use Model\WealthbotRebalancer\Repository\RebalancerQueueRepository;
use Model\WealthbotRebalancer\Repository\RiaRepository;
use Model\WealthbotRebalancer\Repository\SecurityRepository;
use Model\WealthbotRebalancer\Repository\SubclassRepository;
use Model\WealthbotRebalancer\TradeData;
use Test\Suit\ExtendedTestCase;

class RebalancerQueueRepositoryTest extends ExtendedTestCase
{
    /** @var RebalancerQueueRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new RebalancerQueueRepository();
    }

    public function testFindById()
    {
        $securityRepository = new SecurityRepository();
        $expectedSecurity = $securityRepository->findOneBySymbol('RWX');

        $accountRepository = new AccountRepository();
        $expectedAccount = $accountRepository->findOneByAccountNumber('916985328');

        $riaRepo = new RiaRepository();
        $ria = $riaRepo->findOneBy(array('email' => 'johnny@wealthbot.io'));

        $jobRepo = new JobRepository();
        $expectedJob = $jobRepo->findOneBy(array('user_id' => $ria->getId()));

        $first = $this->repository->findFirst();
        $rebalancerQueue = $this->repository->findById($first->getId());

        $this->assertEquals($expectedSecurity->getId(), $rebalancerQueue->getSecurity()->getId());
        $this->assertEquals($expectedAccount->getId(), $rebalancerQueue->getAccount()->getId());
        $this->assertEquals(QueueItem::STATUS_SELL, $rebalancerQueue->getStatus());
        $this->assertEquals(36, $rebalancerQueue->getQuantity());
        $this->assertEquals(0, $rebalancerQueue->getAmount());
        $this->assertNotNull($rebalancerQueue->getLot()->getId());
    }

    public function testSave()
    {
        $securityRepository = new SecurityRepository();
        $security = $securityRepository->findOneBySymbol('VTI');

        $accountRepository = new AccountRepository();
        $account = $accountRepository->findOneByAccountNumber('916985328');

        $subclassRepo = new SubclassRepository();
        $subclass = $subclassRepo->findFirst();

        $riaRepo = new RiaRepository();
        $ria = $riaRepo->findOneBy(array('email' => 'johnny@wealthbot.io'));

        $jobRepo = new JobRepository();
        $job = $jobRepo->findOneBy(array('user_id' => $ria->getId()));

        $rebalancerActionRepo = new RebalancerActionRepository();
        $rebalancerActions = $rebalancerActionRepo->bindForJob($job);
        $rebalancerAction = $rebalancerActions->first();

        $rebalancerQueue = new QueueItem();
        $rebalancerQueue->setSecurity($security);
        $rebalancerQueue->setAccount($account);
        $rebalancerQueue->setAmount(100.1);
        $rebalancerQueue->setQuantity(10);
        $rebalancerQueue->setStatus(QueueItem::STATUS_BUY);
        $rebalancerQueue->setRebalancerActionId($rebalancerAction->getId());
        $rebalancerQueue->setSubclass($subclass);

        $this->repository->save($rebalancerQueue);

        $last = $this->repository->findById($this->repository->getLastInsertId());

        $this->assertNotNull($last->getId());
        $this->assertEquals($security->getId(), $last->getSecurity()->getId());
        $this->assertEquals($account->getId(), $last->getAccount()->getId());
        $this->assertEquals(100.1, $last->getAmount());
        $this->assertEquals(10, $last->getQuantity());
        $this->assertEquals(QueueItem::STATUS_BUY, $last->getStatus());

        $newSecurity = $securityRepository->findOneBySymbol('RWX');
        $newAccount = $accountRepository->findOneByAccountNumber('480888811');

        $last->setSecurity($newSecurity);
        $last->setAccount($newAccount);
        $last->setAmount(456.3);
        $last->setQuantity(78);
        $last->setStatus(QueueItem::STATUS_SELL);

        $this->repository->save($last);

        $updatedLast = $this->repository->findById($last->getId());

        $this->assertEquals($last->getId(), $updatedLast->getId());
        $this->assertEquals(456.3, $updatedLast->getAmount());
        $this->assertEquals(78, $updatedLast->getQuantity());

        $this->repository->delete($updatedLast);
    }

    public function testDelete()
    {
        $securityRepository = new SecurityRepository();
        $security = $securityRepository->findOneBySymbol('VTI');

        $accountRepository = new AccountRepository();
        $account = $accountRepository->findOneByAccountNumber('916985328');

        $subclassRepo = new SubclassRepository();
        $subclass = $subclassRepo->findFirst();

        $riaRepo = new RiaRepository();
        $ria = $riaRepo->findOneBy(array('email' => 'johnny@wealthbot.io'));

        $jobRepo = new JobRepository();
        $job = $jobRepo->findOneBy(array('user_id' => $ria->getId()));

        $rebalancerActionRepo = new RebalancerActionRepository();
        $rebalancerActions = $rebalancerActionRepo->bindForJob($job);
        $rebalancerAction = $rebalancerActions->first();

        $rebalancerQueue = new QueueItem();
        $rebalancerQueue->setSecurity($security);
        $rebalancerQueue->setAccount($account);
        $rebalancerQueue->setAmount(100.1);
        $rebalancerQueue->setRebalancerActionId($rebalancerAction->getId());
        $rebalancerQueue->setQuantity(10);
        $rebalancerQueue->setStatus(QueueItem::STATUS_BUY);
        $rebalancerQueue->setSubclass($subclass);

        $this->repository->save($rebalancerQueue);

        $last = $this->repository->findById($this->repository->getLastInsertId());

        $this->repository->delete($last);

        $deletedLast = $this->repository->findById($last->getId());

        $this->assertNull($deletedLast);
    }


    public function testGetTradeDataCollectionForJob()
    {
        $riaRepo = new RiaRepository();
        $ria = $riaRepo->findOneBy(array(
            'email' => 'johnny@wealthbot.io'
        ));

        $jobRepo = new JobRepository();
        $job = $jobRepo->findOneBy(array(
            'user_id' => $ria->getId()
        ));

        $tradeDataCollection = $this->repository->getTradeDataCollectionForJob($job);
        $this->assertCount(3, $tradeDataCollection);

        $securityRepo = new SecurityRepository();
        $accountRepo = new AccountRepository();

        $tradeDataExpectedArray = array(
            array(
                'job_id' => $job->getId(),
                'security_id' => $securityRepo->findOneBySymbol('RWX')->getId(),
                'account_id' => $accountRepo->findOneByAccountNumber('916985328')->getId(),
                'account_number' => '916985328',
                'account_type' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
                'security_type' => TradeData::SECURITY_TYPE_EQUITY,
                'action' => TradeData::ACTION_SELL,
                'quantity_type' => TradeData::QUANTITY_TYPE_ALL_SHARES,
                'quantity' => '56',
                'symbol' => 'RWX',
            ),
            array(
                'job_id' => $job->getId(),
                'security_id' => $securityRepo->findOneBySymbol('VCIT')->getId(),
                'account_id' => $accountRepo->findOneByAccountNumber('480888811')->getId(),
                'account_number' => '480888811',
                'account_type' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
                'security_type' => TradeData::SECURITY_TYPE_EQUITY,
                'action' => TradeData::ACTION_BUY,
                'quantity_type' => TradeData::QUANTITY_TYPE_SHARES,
                'quantity' => '12',
                'symbol' => 'VCIT'
            ),
            array(
                'job_id' => $job->getId(),
                'security_id' => $securityRepo->findOneBySymbol('BND')->getId(),
                'account_id' => $accountRepo->findOneByAccountNumber('122223334')->getId(),
                'account_number' => '122223334',
                'account_type' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
                'security_type' => TradeData::SECURITY_TYPE_EQUITY,
                'action' => TradeData::ACTION_BUY,
                'quantity_type' => TradeData::QUANTITY_TYPE_SHARES,
                'quantity' => '1',
                'symbol' => 'BND'
            ),
            array(
                'job_id' => '59',
                'security_id' => $securityRepo->findOneBySymbol('VGIT')->getId(),
                'account_id' => $accountRepo->findOneByAccountNumber('916985328')->getId(),
                'account_number' => '916985328',
                'account_type' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
                'security_type' => TradeData::SECURITY_TYPE_EQUITY,
                'action' => TradeData::ACTION_SELL,
                'quantity_type' => TradeData::QUANTITY_TYPE_ALL_SHARES,
                'quantity' => '10',
                'symbol' => 'VGIT'
            )
        );

        $tradeDataCollectionExpected = new ArrayCollection();

        foreach ($tradeDataExpectedArray as $key => $tradeDataExpected) {
            $tradeData = new TradeData();
            $tradeData->loadFromArray($tradeDataExpected);

            $tradeDataCollectionExpected->add($tradeData, $key);
        }

        foreach ($tradeDataCollection as $tradeData) {
            $tradeData->setId(null);

            $tradeDataExpected = $tradeDataCollectionExpected->current();
            $this->assertEquals($tradeData, $tradeDataExpected);

            $tradeDataCollectionExpected->next();
        }
    }

    public function testFindVSPForTradeData()
    {
        $riaRepo = new RiaRepository();
        $ria = $riaRepo->findOneBy(array(
            'email' => 'raiden@wealthbot.io'
        ));

        $jobRepo = new JobRepository();
        $job = $jobRepo->findOneBy(array(
            'user_id' => $ria->getId()
        ));

        $accountRepo = new AccountRepository();
        $securityRepo = new SecurityRepository();

        $tradeData = new TradeData();
        $tradeData->setJobId($job->getId());
        $tradeData->setAccountId($accountRepo->findOneByAccountNumber('916985328')->getId());
        $tradeData->setSecurityId($securityRepo->findOneBySymbol('RWX')->getId());

        $vsps = $this->repository->findVSPForTradeData($tradeData);

        $vspsExpected = array(
            array(
                'purchase' => 'VSP',
                'purchase_date' => '02132013',
                'quantity' => 36
            ),
            array(
                'purchase' => 'VSP',
                'purchase_date' => '02162013',
                'quantity' => 20
            )
        );

        $this->assertEquals($vspsExpected, $vsps);
    }
}
