<?php
namespace Console;

use Lib\Logger\Logger;
use Lib\Logger\Writer\ConsoleWriter;
use Manager\BusinessCalendar;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Lot;
use Model\WealthbotRebalancer\RebalancerAction;
use Model\WealthbotRebalancer\QueueItem;
use Model\WealthbotRebalancer\RebalancerQueue;
use Model\WealthbotRebalancer\Ria;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\SecurityCollection;
use Model\WealthbotRebalancer\Subclass;
use Model\WealthbotRebalancer\SubclassCollection;
use Model\WealthbotRebalancer\TradeData;

use Model\WealthbotRebalancer\Repository\AccountRepository;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\SecurityRepository;
use Model\WealthbotRebalancer\Repository\BaseRepository;
use Model\WealthbotRebalancer\Repository\JobRepository;
use Model\WealthbotRebalancer\Repository\LotRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Model\WealthbotRebalancer\Repository\PositionRepository;
use Model\WealthbotRebalancer\Repository\RebalancerActionRepository;
use Model\WealthbotRebalancer\Repository\RiaRepository;
use Model\WealthbotRebalancer\Repository\RebalancerQueueRepository;
use Model\WealthbotRebalancer\Repository\SecurityTransactionRepository;
use Model\WealthbotRebalancer\Repository\SubclassRepository;
use Model\WealthbotRebalancer\Repository\DistributionRepository;

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

class WealthbotRebalancer {

    const STEP_REBALANCING_TRIGGER = '1';
    const STEP_DETERMINE_CASH_NEED = '2';
    const STEP_ACTIVE_EMPLOYER_CHECK = '3';

    /** @var int */
    private $freeCash;

    /** @var array */
    private $usedLots;

    /** @var BaseRepository[] */
    private $repositories;

    /** @var \Manager\BusinessCalendar */
    private $businessCalendarManager;

    /** @var \Model\WealthbotRebalancer\Client */
    private $client;

    /** @var Job */
    private $job;

    /** @var RebalancerQueue */
    private $rebalancerQueue;

    /** @var RebalancerAction */
    private $rebalancerAction;

//    private static $quaterDays = array(
//        '01-01',
//        '04-01',
//        '07-01',
//        '10-01'
//    );
//
//    private static $semiAnnualDays = array(
//        '01-01',
//        '07-01'
//    );
//
//    private static $annualDays = array(
//        '01-01'
//    );

    public $logger;


    public function __construct()
    {
        $this->repositories = [];
        $this->freeCash = 0;
        $this->usedLots = array();
        $this->businessCalendarManager = new BusinessCalendar();
        $this->rebalancerQueue = new RebalancerQueue();

        $this->initRepositories();

        $this->logger = new Logger(new ConsoleWriter());
    }

    /**
     * Init repositories
     */
    public function initRepositories()
    {
        $this->addRepository(new ClientRepository());
        $this->addRepository(new AccountRepository());
        $this->addRepository(new SecurityRepository());
        $this->addRepository(new DistributionRepository());
        $this->addRepository(new PortfolioRepository());
        $this->addRepository(new SubclassRepository());
        $this->addRepository(new JobRepository());
        $this->addRepository(new LotRepository());
        $this->addRepository(new RebalancerQueueRepository());
        $this->addRepository(new PositionRepository());
        $this->addRepository(new RiaRepository());
        $this->addRepository(new RebalancerActionRepository());
        $this->addRepository(new SecurityTransactionRepository());
    }

    /**
     * Add repository
     *
     * @param BaseRepository $repository
     */
    public function addRepository(BaseRepository $repository)
    {
        $reflect = new \ReflectionClass($repository);
        $name = str_replace('Repository', '', $reflect->getShortName());

        $this->repositories[$name] = $repository;
    }

    /**
     * Get repository
     *
     * @param string $name
     * @return BaseRepository
     */
    public function getRepository($name)
    {
        if (isset($this->repositories[$name])) {
            return $this->repositories[$name];
        }

        return null;
    }

    /**
     * @return \Manager\BusinessCalendar
     */
    public function getBusinessCalendarManager()
    {
        return $this->businessCalendarManager;
    }

    /**
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get job of current rebalancer iteration
     *
     * @return Job
     */
    protected function getJob()
    {
        return $this->job;
    }

    /**
     * Get rebalancer queue
     *
     * @return RebalancerQueue
     */
    public function getRebalancerQueue()
    {
        return $this->rebalancerQueue;
    }

    /**
     * Get Current Rebalancer Action
     *
     * @return RebalancerAction
     */
    public function getRebalancerAction()
    {
        return $this->rebalancerAction;
    }

    public function start()
    {
        /** @var JobRepository $repository */
        $repository = $this->getRepository('Job');
        $jobs = $repository->findAllCurrentRebalancerJob();

        foreach ($jobs as $job) {
            $this->startForJob($job);
        }
    }

    public function fakeStart($jobId)
    {
        /** @var JobRepository $jobRepo */
        $jobRepo = $this->getRepository('Job');
        /** @var Job $job */
        $job = $jobRepo->find($jobId);

        if (!$job) {
            return;
        }

        /** @var ClientRepository $clientRepo */
        $clientRepo = $this->getRepository('Client');

        /** @var RebalancerActionRepository $rebalancerActionRepo */
        $rebalancerActionRepo = $this->getRepository('RebalancerAction');
        $rebalancerActions = $rebalancerActionRepo->bindForJob($job);

        /** @var AccountRepository $accountRepo */
        $accountRepo = $this->getRepository('Account');

        /** @var LotRepository $lotRepository */
        $lotRepository = $this->getRepository('Lot');

        /** @var SecurityRepository $securityRepo */
        $securityRepo = $this->getRepository('Security');

        /** @var PortfolioRepository $portfolioRepo */
        $portfolioRepo = $this->getRepository('Portfolio');

        /** @var RebalancerQueueRepository $rebalancerQueueRepo */
        $rebalancerQueueRepo = $this->getRepository('RebalancerQueue');

        $account744888385 = $accountRepo->findOneByAccountNumber('744888385');
        $account744888386 = $accountRepo->findOneByAccountNumber('744888386');

        $securityRWX = $securityRepo->findOneBySymbol('RWX');
        $securityVTI = $securityRepo->findOneBySymbol('VTI');

        $subclassRepo = new SubclassRepository();

        /** @var RebalancerAction $rebalancerAction */
        foreach ($rebalancerActions as $rebalancerAction) {
            $rebalancerAction->setStatus($job->getRebalanceType());

            $client = $clientRepo->getClientByRebalancerAction($rebalancerAction);
            $portfolio = $portfolioRepo->findPortfolioByClient($client);

            $account744888385->setClient($client);
            $account744888386->setClient($client);

            $subclass = $subclassRepo->findByNameForPortfolio('Large Value', $portfolio);

            $queueItem = new QueueItem();
            $queueItem->setStatus(QueueItem::STATUS_SELL);
            $queueItem->setLot($lotRepository->findLastLotByAccountAndSecurity($account744888385, $securityRWX));
            $queueItem->setAccount($account744888385);
            $queueItem->setAmount(1000);
            $queueItem->setQuantity(50);
            $queueItem->setSecurity($securityRWX);
            $queueItem->setSubclass($subclass);
            $queueItem->setRebalancerActionId($rebalancerAction->getId());

            $rebalancerQueueRepo->save($queueItem);

            $queueItem = new QueueItem();
            $queueItem->setStatus(QueueItem::STATUS_BUY);
            $queueItem->setAccount($account744888386);
            $queueItem->setAmount(700);
            $queueItem->setQuantity(7);
            $queueItem->setSecurity($securityVTI);
            $queueItem->setSubclass($subclass);
            $queueItem->setRebalancerActionId($rebalancerAction->getId());

            $rebalancerQueueRepo->save($queueItem);

            $rebalancerActionRepo->saveStatus($rebalancerAction);
        }

        $jobRepo->finish($job);
    }

    public function startForJob(Job $job)
    {
        $this->job = $job;

        /** @var RebalancerActionRepository $repository */
        $repository = $this->getRepository('RebalancerAction');
        $rebalancerActions = $repository->bindForJob($job);

        foreach ($rebalancerActions as $rebalancerAction) {
            $this->startForRebalancerAction($rebalancerAction);
        }
    }

    public function startForRebalancerAction(RebalancerAction $rebalancerAction)
    {
        $this->rebalancerAction = $rebalancerAction;

        /** @var ClientRepository $clientRepo */
        $clientRepo = $this->getRepository('Client');
        $client = $clientRepo->getClientByRebalancerAction($rebalancerAction);

        /** @var AccountRepository $accountRepo */
        $accountRepo = $this->getRepository('Account');
        $accounts = $accountRepo->getAccountsByRebalancerAction($rebalancerAction);

        if (!$accounts->isEmpty()) {
            $client->setAccounts($accounts);

            $this->startForClient($client);
        }
    }

    public function startForClient(Client $client)
    {
        $this->logger->log("Start rebalance for client: {$client->getId()}.");

        $this->client = $client;

        /** @var Ria $ria */
        $ria = $this->getRepository('Ria')->findOneByClient($client);
        $client->setRia($ria);

        $riaCompanyInformation = $this->getRepository('RiaCompanyInformation')->findOneByRia($ria);
        $ria->setRiaCompanyInformation($riaCompanyInformation);

        //steps 1-3
        foreach ($client->getAccounts() as $account) {
            $this->prepareAccount($account);
            $this->rebalancingTrigger($account);
        }

        $this->rebalance();

        /*//step 4
        //$isAllowed = $this->isRebalAllowed($client);
        $isAllowed = true;

        //step 5 - 7
        if ($isAllowed) {
            $collection = $this->prepareCollection($client);

            if (!$collection || ($collection && count($collection) < 2)) {
                return false;
            }

            $this->startRebalanceCycle($client, $collection);
        }*/
    }

    public function rebalancingTrigger(Account $account)
    {
        $accountStatus = $account->getStatus();
        if ($accountStatus === Account::STATUS_INIT_REBALANCE) {
            $this->logger->logInfo("Init rebalance for account: {$account->getId()}.");
            $this->liquidateAccountIfNeed($account);

        } elseif ($accountStatus === Account::STATUS_INIT_REBALANCE_COMPLETE ||
            $accountStatus === Account::STATUS_REBALANCED ||
            $accountStatus === Account::STATUS_ANALYZED ) {
            $this->checkCashNeeds($account);
        }
    }

    /**
     * Determine if liquidating account is needed
     * 1a step from spec
     *
     * @param Account $account
     * @param Ria $ria
     * @throws \Exception
     */
    public function liquidateAccountIfNeed(Account $account)
    {
        if ($this->getJob()->getRia()->getRiaCompanyInformation()->getUseTransactionFees()) {

            $noPreferredSecurities = $account->findNoPreferredBuySecurities();
            if ($noPreferredSecurities->count()) {

                /** @var Security $noPreferredSecurity */
                foreach ($noPreferredSecurities as $noPreferredSecurity) {
                    $qty = $noPreferredSecurity->getQty();
                    $amount = $noPreferredSecurity->getAmount();

                    if ($noPreferredSecurity->isCanBeSold($qty, $amount)) {

                        $noPreferredSecurity->sell($qty, $amount);

                        $queueItem = new QueueItem();
                        $queueItem->setStatus(QueueItem::STATUS_SELL);
                        $queueItem->setRebalancerActionId($this->getRebalancerAction()->getId());
                        $queueItem->setQuantity($noPreferredSecurity->getQty());
                        $queueItem->setAmount($noPreferredSecurity->getAmount());
                        $queueItem->setSecurity($noPreferredSecurity);
                        $queueItem->setAccount($account);

                        $this->getRebalancerQueue()->addItem($queueItem);
                    }
                }
                $account->setIsReadyToRebalance(true);
            } elseif ($account->isAllSecuritiesEqualCash()) {
                $account->setIsReadyToRebalance(true);
            }
        }
    }

    /**
     * Check cash needs
     * 2a, 2b, 2c steps from spec
     *
     * @param Account $account
     */
    public function checkCashNeeds(Account $account)
    {
        $cashNeeds = $account->calculateCashNeeds();
        if ($account->getTotalCash() <= $cashNeeds) {
            $account->setIsReadyToRebalance(true);
        }
    }

    /**
     * Prepare account before any checking
     *
     * @param Account $account
     */
    public function prepareAccount(Account $account)
    {
        /** @var DistributionRepository $repository */
        $repository = $this->getRepository('Distribution');
        $manager = $this->getBusinessCalendarManager();

        $dateFrom = new \DateTime();
        $dateTo = $manager->addBusinessDays($dateFrom, 4);
        $scheduledDistribution = $repository->findScheduledDistribution($account, $dateFrom, $dateTo);
        $oneTimeDistribution = $repository->findOneTimeDistribution($account);

        $account->setScheduledDistribution($scheduledDistribution);
        $account->setOneTimeDistribution($oneTimeDistribution);

        $this->getRepository('Account')->loadAccountValues($account);

        if ($account->getSecurities()->isEmpty()) {
            /** @var SecurityCollection $securities */
            $securities = $this->getRepository('Security')->findSecuritiesByAccount($account);
            $account->setSecurities($securities);
        }
    }

//    /**
//     * @param Client $client
//     * @return bool
//     */
//    private function isRebalAllowed(Client $client) {
//        $time = new \DateTime();
//
//        switch ($client->getRia()->getRebalancingFrequency()) {
//            case (Ria::REBALANCED_FREQUENCY_QUARTERLY):
//                if (!in_array($time->format('m-d'), self::$quaterDays)) {
//                    return false;
//                }
//                break;
//
//            case (Ria::REBALANCED_FREQUENCY_SEMI_ANNUALLY):
//                if (!in_array($time->format('m-d'), self::$semiAnnualDays)) {
//                    return false;
//                }
//                break;
//
//            case (Ria::REBALANCED_FREQUENCY_ANNUALLY):
//                if (!in_array($time->format('m-d'), self::$annualDays)) {
//                    return false;
//                }
//                break;
//        }
//
//        return true;
//    }

    public function prepareCollection(Client $client, Account $account = null)
    {
        /** @var PortfolioRepository $portfolioRepository */
        $portfolioRepository = $this->getRepository('Portfolio');

        $portfolio = $portfolioRepository->findPortfolioByClient($client);
        if (!$portfolio) {
            throw new \Exception('Portfolio for client '.$client->getId().' does not exist');
        }

        $portfolioRepository->loadPortfolioValues($client);

        /** @var SecurityCollection $portfolioSecurities */
        $portfolioSecurities = $this->getRepository('Security')->findSecuritiesByPortfolio($portfolio);
        if ($portfolioSecurities->isEmpty()) {
            throw new \Exception('Securities for portfolio '.$portfolio->getId().' does not exist');
        }

        $portfolio->setSecurities($portfolioSecurities);

        $client->setPortfolio($portfolio);

        /** @var SubclassRepository $subclassRepo */
        $subclassRepo = $this->getRepository('Subclass');
        $collection = $subclassRepo->bindAllocations($portfolio, $account);

        return $collection;
    }

    public function rebalance()
    {
        $client = $this->getClient();
        if (!$client) {
            $exception = new \RuntimeException('Client is not defined.');
            $this->logger->logError($exception);

            throw $exception;
        }

        $this->buyRequiredCash();

        if (!$this->getJob()->isRequiredCashRebalance()) {
            switch ($client->getAccountManaged()) {
                case Client::ACCOUNT_MANAGED_ACCOUNT:
                    $this->accountRebalance();
                    break;
                case Client::ACCOUNT_MANAGED_HOUSEHOLD:
                    $this->householdRebalance();
                    break;
            }
        }
    }

    /**
     * Account level rebalance
     */
    public function accountRebalance()
    {
        $this->logger->logInfo('Account level rebalance.');

        $client = $this->getClient();

        foreach ($client->getAccounts() as $account) {
            $collection = $this->prepareCollection($client, $account);

            $usedSubclasses = $this->operateSubclasses($collection, $account);
            $notUsedSubclasses = $collection->diff($usedSubclasses);

            foreach ($notUsedSubclasses as $subclass) {
                $this->checkTlh($account, $subclass);
            }
        }
    }

    /**
     * Household level rebalance
     */
    public function householdRebalance()
    {
        $this->logger->logInfo('Household level rebalance.');
        $this->operateSubclasses($this->prepareCollection($this->getClient()));
    }

    /**
     * Get buy and sell limit
     * Returns max amount which can be sold and bought
     *
     * @param SubclassCollection $subclassCollection
     * @param Account $contextAccount
     * @return float
     */
    public function getBuyAndSellLimit(SubclassCollection $subclassCollection, Account $contextAccount = null)
    {
        $sellAmount = 0;
        $buyAmount = 0;

        if ($subclassCollection->isOutOfBalance()) {

            /** @var Subclass $subclass */
            foreach ($subclassCollection as $subclass) {
                if ($subclass->calcOOB() == 0) continue;

                $amount = $subclassCollection->getOnePercentAmount() * abs($subclass->calcOOB());

                if ($subclass->calcOOB() > 0) {
                    if ($contextAccount) {
                        $sellAmount += $this->sellSubclass($subclass, $contextAccount, $amount, true);
                    } else {
                        $sellAmount += $this->sellSubclassHousehold($subclass, $amount, true);
                    }

                } elseif ($subclass->calcOOB() < 0) {
                    if ($contextAccount) {
                        $buyAmount += $this->buySubclass($subclass, $contextAccount, $amount, true);
                    } else {
                        $buyAmount += $this->buySubclassHousehold($subclass, $amount, true);
                    }

                }
            }
        }

        $result = min($sellAmount, $buyAmount);
        $this->logger->logInfo("Limit for buy and sell: {$result}.");

        return $result;
    }

    /**
     * Operate subclasses by OOB
     * Return collection of sold and bought subclasses
     *
     * @param SubclassCollection $subclassCollection
     * @param Account $contextAccount
     * @return SubclassCollection
     */
    public function operateSubclasses(SubclassCollection $subclassCollection, Account $contextAccount = null)
    {
        $subclassCollection->rebuildAllocations();
        $oobSubclasses = $subclassCollection->sortByOob();
        $usedSubclasses = new SubclassCollection();

        $buyAndSellLimit = $this->getBuyAndSellLimit($oobSubclasses, $contextAccount);
        if ($buyAndSellLimit > 0) {
            $sellLimit = $buyAndSellLimit;
            $buyLimit = $buyAndSellLimit;

            /** @var Subclass $subclassForSell */
            $subclassForSell = $oobSubclasses->first();
            while ($subclassForSell && ($subclassForSell->calcOOB() > 0) && ($sellLimit > 0)) {

                if ($contextAccount) {
                    $sold = $this->sellSubclass($subclassForSell, $contextAccount, $sellLimit, false);
                } else {
                    $sold = $this->sellSubclassHousehold($subclassForSell, $sellLimit, false);
                }

                $sellLimit -= $sold;

                $usedSubclasses->add($subclassForSell);

                $subclassForSell = $oobSubclasses->next();
            }

            /** @var Subclass $subclassForBuy */
            $subclassForBuy = $oobSubclasses->last();
            while ($subclassForBuy && ($subclassForBuy->calcOOB() < 0) && ($buyLimit > 0)) {
                $needToBuy = $oobSubclasses->getOnePercentAmount() * abs($subclassForBuy->calcOOB());

                if ($contextAccount) {
                    $bought = $this->buySubclass($subclassForBuy, $contextAccount, $needToBuy, false);
                } else {
                    $bought = $this->buySubclassHousehold($subclassForBuy, $needToBuy, false);
                }

                $buyLimit -= $bought;

                $usedSubclasses->add($subclassForBuy);

                $subclassForBuy = $oobSubclasses->prev();
            }
        }

        return $usedSubclasses;
    }

    /**
     * Sell subclass for household level
     * Return sold amount
     *
     * @param Subclass $subclass
     * @param float $amount
     * @param boolean $dryRun
     * @return float
     * @throws \Exception
     */
    public function sellSubclassHousehold(Subclass $subclass, $amount, $dryRun = false)
    {
        $requiredCash = $amount;
        $amountSum = 0;

        $clientAccounts = $this->getClient()->getAccounts();
        $accountCollection = clone $clientAccounts;

        $account = $accountCollection->getAccountForSellSubclass($subclass);
        while ($requiredCash > 0 && $account) {
            $sold = $this->sellSubclass($subclass, $account, $requiredCash, $dryRun);

            $amountSum += $sold;
            $requiredCash -= $sold;

            $accountCollection->remove($account->getId());
            $account = $accountCollection->getAccountForSellSubclass($subclass);
        }

        return $amountSum;
    }

    /**
     * Sell subclass
     * Return sold amount
     *
     * @param Subclass $subclass
     * @param Account $account
     * @param float $amount
     * @param boolean $dryRun
     * @return float
     * @throws \Exception
     */
    public function sellSubclass(Subclass $subclass, Account $account, $amount, $dryRun = false)
    {
        /** @var LotRepository $lotRepository */
        $lotRepository = $this->getRepository('Lot');

        $requiredCash = $amount;
        $amountSum = 0;

        $lots = $lotRepository->findOrderedLots($subclass->getSecurity(), $account);

        /** @var Lot $lot */
        foreach ($lots as $lot) {
            if ($lot->getIsMuni()) {
                $securityToSell = $subclass->getMuniSecurity();
            } else {
                $securityToSell = $subclass->getSecurity();
            }

            if ($this->checkShortTermRedemption($securityToSell, $lot) && !$this->checkShortTermGain($lot)) {
                $sellAmount = $this->sellLot($lot, $securityToSell, $account, $requiredCash, $dryRun);

                if (!$dryRun && $lot->getAmount() == 0) {
                    $lots->remove($lot->getId());
                }

                $requiredCash -= $sellAmount;
                $amountSum += $sellAmount;

                if ($requiredCash <= 0) {
                    break;
                }
            }
        }

        if ($dryRun) {
            $account->setCashForBuy($account->getCashForBuy() + $amountSum);
        }

        return $amountSum;
    }

    /**
     * Buy subclass for household level
     * Return bought amount
     *
     * @param Subclass $subclass
     * @param float $amount
     * @param boolean $dryRun
     * @return float
     * @throws \Exception
     */
    public function buySubclassHousehold(Subclass $subclass, $amount, $dryRun = false)
    {
        $clientAccounts = $this->getClient()->getAccounts();
        $accountCollection = clone $clientAccounts;

        $cash = $amount;
        $amountSum = 0;

        $account = $accountCollection->getAccountForBuySubclass($subclass);
        while ($cash > 0 && $account && $account->getCashForBuy() > 0) {
            $bought = $this->buySubclass($subclass, $account, min($cash, $account->getCashForBuy()), $dryRun);

            $cash -= $bought;
            $amountSum += $bought;

            $accountCollection->remove($account->getId());
            $account = $accountCollection->getAccountForBuySubclass($subclass);
        }

        return $amountSum;
    }

    /**
     * Buy subclass
     * Return bought amount
     *
     * @param Subclass $subclass
     * @param Account $account
     * @param float $amount
     * @param boolean $dryRun
     * @return float
     * @throws \Exception
     */
    public function buySubclass(Subclass $subclass, Account $account, $amount, $dryRun = false)
    {
        if ($this->isBuyMuni($account, $subclass)) {
            $securityToBuy = $subclass->getMuniSecurity();
        } else {
            $securityToBuy = $subclass->getSecurity();
        }

        return $this->buySecurity($securityToBuy, $account, $amount, $dryRun);
    }

    /**
     * Sell lot
     *
     * @param Lot $lot
     * @param Security $security
     * @param Account $account
     * @param $amount
     * @param bool $dryRun
     * @return float
     * @throws \Exception
     */
    public function sellLot(Lot $lot,  Security $security, Account $account, $amount, $dryRun = false)
    {
        if ($lot->getAmount() < $amount) {
            $sellQuantity = $lot->getQuantity();
            $sellAmount = $lot->getAmount();
        } else {
            $sellQuantity = ceil($amount / $lot->calcPrice());
            $sellAmount = $sellQuantity * $lot->calcPrice();
        }

        if (!$security->isCanBeSold($sellQuantity, $sellAmount)) {
            $exception = new \Exception(sprintf(
                'Selling security error: cannot sell security with id: %s, qty: %s, amount: %s. %s|%s',
                $security->getId(),
                $sellQuantity,
                $sellAmount,
                $security->getQty(),
                $security->getAmount()
            ));

            $this->logger->logError($exception);
            throw $exception;
        }

        if (!$this->checkTransaction($account, $amount, $security, 'sell')) {
            $exception = new \Exception(
                "Cannot sell: {$security->getId()} . Transaction check fails. (Amount:{$amount})"
            );

            $this->logger->logError($exception);
            throw $exception;
        }

        if (!$dryRun) {
            $security->sell($sellQuantity, $sellAmount);

            $queueItem = new QueueItem();
            $queueItem->setRebalancerActionId($this->getRebalancerAction()->getId());
            $queueItem->setSecurity($security);
            $queueItem->setAccount($account);
            $queueItem->setLot($lot);
            $queueItem->setQuantity($sellQuantity);
            $queueItem->setAmount($sellAmount);
            $queueItem->setStatus(QueueItem::STATUS_SELL);

            $this->getRebalancerQueue()->addItem($queueItem);

            $lot->sell($sellQuantity);
        }

        return $sellAmount;
    }

    /**
     * Buy security
     *
     * @param Security $security
     * @param Account $account
     * @param $amount
     * @param bool $dryRun
     * @return float|int
     * @throws \Exception
     */
    public function buySecurity(Security $security, Account $account, $amount, $dryRun = false)
    {
        $price = $security->getPrice();
        $buyQuantity = ($price > 0) ? floor($amount / $price) : 0;
        $buyAmount = $buyQuantity * $price;

        if (!$security->isCanBePurchased($buyQuantity, $buyAmount)) {
            $exception = new \Exception(sprintf(
                'Buying security error: cannot buy security with id: %s, qty: %s, amount: %s.',
                $security->getId(),
                $buyQuantity,
                $buyAmount
            ));

            $this->logger->logError($exception);
            throw $exception;
        }

        if (!$this->checkTransaction($account, $amount, $security, 'buy')) {
            $exception = new \Exception(
                 "Cannot buy: {$security->getId()} . Transaction check fails. (Amount:{$amount})"
            );

            $this->logger->logError($exception);
            throw $exception;
        }

        if (!$dryRun) {
            $this->logger->logInfo("Buy security {$security->getId()} qty: {$buyQuantity}; amount: {$buyAmount}");

            $security->buy($buyQuantity, $buyAmount);

            $queueItem = new QueueItem();
            $queueItem->setRebalancerActionId($this->getRebalancerAction()->getId());
            $queueItem->setSecurity($security);
            $queueItem->setAccount($account);
            $queueItem->setQuantity($buyQuantity);
            $queueItem->setAmount($buyAmount);
            $queueItem->setStatus(QueueItem::STATUS_BUY);

            $this->getRebalancerQueue()->addItem($queueItem);
        }

        return $buyAmount;
    }

    public function isBuyMuni(Account $account, Subclass $subclass)
    {
        $ria = $account->getClient()->getRia();

        if (!$ria->getIsUseMunicipalBond() || !$account->isTaxable() || !$subclass->hasMuniFund()) {
            return false;
        }

        $client = $account->getClient();
        if ($client->getTaxBracket() < $client->getRia()->getClientTaxBracket()) {
            return false;
        }

        return true;
    }

    /**
     * By required cash for client accounts
     */
    public function buyRequiredCash()
    {
        $client = $this->getClient();

        /** @var Account $account */
        foreach ($client->getAccounts() as $account) {
            $cashNeeds = $account->getTotalCashNeeds();

            if ($account->getIsReadyToRebalance() && $cashNeeds > 0) {
                $subclasses = new SubclassCollection();

                /** @var Security $security */
                foreach ($account->getSecurities() as $security) {
                    $subclasses->add($security->getSubclass());
                }

                $oobSubclasses = $subclasses->sortByOob();
                $subclassToSell = $oobSubclasses->first();
                $needToBuy = $cashNeeds;

                while ($needToBuy > 0 && $subclassToSell) {

                    $sellAmount = $this->sellSubclass($subclassToSell, $account, $needToBuy, false);
                    $needToBuy -= $sellAmount;
                    $account->setTotalCash($sellAmount + $account->getTotalCash());

                    $subclassToSell = $oobSubclasses->next();
                }
            }

            $account->updateCashForBuy();
        }
    }

    /**
     * Check Tax Loss Harvesting
     * 6.C step in spec
     *
     * @param Account $account
     * @param Subclass $subclass
     * @return boolean
     */
    public function checkTlh(Account $account, Subclass $subclass)
    {
        $client = $account->getClient();
        $ria = $client->getRia();

        /** @var LotRepository $lotRepository */
        $lotRepository = $this->getRepository('Lot');
        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->getRepository('Client');

        $clientRepository->loadStopTlhValue($client);

        $date = new \DateTime();
        $clientLossesSum = $lotRepository->getClientLossesSumForYear($client, $date->format('Y'));

        if ($ria->getIsTlhEnabled() && $client->canUseTlh() && $account->isTaxable() && $subclass->hasTlhFund() && abs($clientLossesSum) >= $client->getStopTlhValue()) {

            $lots = $lotRepository->findLotsByAccountAndSecurity($account, $subclass->getSecurity());
            $amount = 0;

            /** @var Lot $lot */
            foreach ($lots as $lot) {
                $loss = $lot->getRealizedGainOrLoss();
                $lossPercent = round(($lot->getAmount() - $lot->getCostBasis()) / $lot->getAmount(), 2);

                if ($loss < 0 && abs($loss) >= $ria->getMinTlh() && abs($lossPercent) >= $ria->getMinTlhPercent()) {
                    $amount += $this->sellLot($lot, $subclass->getSecurity(), $account, $lot->getAmount());
                }
            }

            $this->buySecurity($subclass->getTaxLossHarvesting(), $account, $amount);
        }
    }


    /**
     * Determine if the sale will cause short term gain
     *
     * @param obj Lot [instance of Lot]
     * @return bool [returns true if sale is a short-term gain]
     */
    public function checkShortTermGain(Lot $lot)
    {
        if ($lot->getWasClosed()) {
            return false;
        }

        if (!$lot->isShortTerm()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if sale might be a wash sale
     *
     * @param obj Lot [instance of Lot]
     * @return bool [true if transaction is a "wash sale"]
     */
    public function washSale(Lot $lot)
    {
        if (!$lot->getWasClosed()) {
               return false;
        }

        if ($lot->interval() > 31) {
            return false;
        }

        if ($lot->isLoss()) {
            return false;
        }

        return true;
    }

    /**
     * Check short term redemption
     * 6.F step in spec
     *
     * @param Security $security
     * @param Lot $lot
     * @return bool
     */
    public function checkShortTermRedemption(Security $security, Lot $lot)
    {
        $client = $this->getClient();

        /** @var SecurityTransactionRepository $repository */
        $repository = $this->getRepository('SecurityTransaction');
        $securityTransaction = $repository->findOneByPortfolioAndSecurity($client->getPortfolio(), $security);

        if ($securityTransaction && $securityTransaction->isRedemptionFeeSpecified()) {
            return ($lot->interval() <= $securityTransaction->getRedemptionPenaltyInterval());
        }

        return false;
    }

    public function generateTradeFile()
    {
        $date = date('mdY-His');

        $path = __DIR__.'/../outgoing_files/trade_files/'.$date.'.csv';

        $file = new \SplFileObject($path, 'w');

        /** @var RebalancerQueueRepository $rebalancerQueueRepo */
        $rebalancerQueueRepo = $this->getRepository('RebalancerQueue');
        $tradeDataCollection = $rebalancerQueueRepo->getTradeDataCollectionForJob($this->getJob());

        /** @var TradeData $tradeData */
        foreach ($tradeDataCollection as $tradeData) {
            $file->fputcsv($tradeData->toArrayForTradeFile());

            if ($tradeData->getAction() === TradeData::ACTION_SELL) {

                foreach ($tradeData->getVsps() as $vsp) {
                    $file->fputcsv($vsp);
                }
            }
        }
    }

    /**
     * Checks if transaction is allowed
     *
     * @param  Account    $account [description]
     * @param  amountOfTransaction [$ value of transaction buy/sell]
     * @param  security            [which security are we selling?]
     * @param  txType              [type of transaction buy/sell]
     * @return [bool]      [true/false if transaction passes the filters or not]
     */
    public function checkTransaction(Account $account, $amountOfTransaction, $security = null, $txType = null) {
        $ria = $account->getClient()->getRia();
        $totalCash = $account->getTotalCash();

        if (empty($totalCash) || $totalCash == 0) {
            return false;
        }

        $txAmountPercent = round(($amountOfTransaction / $totalCash) * 100, 2);

        if (!is_null($security) && !is_null($txType)) {
            $client = $account->getClient();
            $repository = $this->getRepository('SecurityTransaction');
            $securityTransaction = $repository->findOneByPortfolioAndSecurity($client->getPortfolio(), $security);

            if ($txType === 'sell' && $amountOfTransaction >= $securityTransaction->getMinimumSell()) {
                return true;
            }

            if ($txType === 'buy' && $amountOfTransaction >= $securityTransaction->getMinimumBuy()) {
                return true;
            }
            $this->logger->logInfo("Minimum buy/sell failed for {$security->getId()}, RIA: {$ria->getId()}");

            return false;
        }

        //TODO: need more logging
        if (!$ria->getRiaCompanyInformation()->getUseTransactionFees()) {
            return true;
        }

        if ($amountOfTransaction >= $ria->getRiaCompanyInformation()->getTransactionMinAmount()) {
            return true;
        }

        if ($txAmountPercent >= $ria->getRiaCompanyInformation()->getTransactionMinAmountPercent()) {
            return true;
        }



        return false;
    }

    public function setRebalanceActionStatus()
    {
        $client = $this->getClient();
        $rebalancerAction = $this->getRebalancerAction();

        if ($client->isAccountLevelRebalancer()) {
            /** @var Account $account */
            $account = $client->getAccounts()->first();
            if (Account::STATUS_INIT_REBALANCE === $account->getStatus()) {
                $rebalancerAction->setStatus(Job::REBALANCE_TYPE_INITIAL);
            }
        }

        if ($this->getRebalancerQueue()->isEmpty()) {
            $rebalancerAction->setStatus(Job::REBALANCE_TYPE_NO_ACTIONS);
        }

        $this->getRepository('RebalancerAction')->saveStatus($rebalancerAction);

        return $rebalancerAction->getStatus();
    }
}
