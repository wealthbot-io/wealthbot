<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Client extends Base {

    const ACCOUNT_MANAGED_ACCOUNT = 1;
    const ACCOUNT_MANAGED_HOUSEHOLD = 2;

    /** @var  Ria */
    protected $ria;

    /** @var AccountCollection  */
    protected $accounts;

    /** @var  Portfolio */
    protected $portfolio;

    /** @var  Job */
    protected $job;

    /** @var  string */
    private $email;

    /** @var  int */
    protected $accountManaged;

    /** @var float */
    private $taxBracket;

    /** @var float */
    private $stopTlhValue;


    public function __construct()
    {
        $this->accounts = new AccountCollection();
    }


    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * @param Ria $ria
     * @return $this
     */
    public function setRia(Ria $ria)
    {
        $this->ria = $ria;

        return $this;
    }

    /**
     * @return Ria
     */
    public function getRia()
    {
        return $this->ria;
    }

    /**
     * @param Portfolio $portfolio
     * @return $this
     */
    public function setPortfolio(Portfolio $portfolio)
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * @return Portfolio
     */
    public function getPortfolio()
    {
        return $this->portfolio;
    }

    /**
     * @param AccountCollection $accounts
     * @return $this
     */
    public function setAccounts(AccountCollection $accounts)
    {
        $this->accounts = $accounts;

        return $this;
    }

    /**
     * Add account
     *
     * @param Account $account
     */
    public function addAccount(Account $account)
    {
        $this->accounts->add($account);
    }

    /**
     * @return AccountCollection
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }



    /**
     * @param int $accountManaged
     * @return $this
     */
    public function setAccountManaged($accountManaged)
    {
        $this->accountManaged = $accountManaged;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountManaged()
    {
        return $this->accountManaged;
    }

    /**
     * @param float $taxBracket
     * @return $this
     */
    public function setTaxBracket($taxBracket)
    {
        $this->taxBracket = $taxBracket;

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxBracket()
    {
        return $this->taxBracket;
    }

    /**
     * Can use TLH
     *
     * @return bool
     */
    public function canUseTlh()
    {
        if ($this->taxBracket >= $this->ria->getClientTaxBracket() &&
            $this->portfolio->getTotalValue() >= $this->ria->getMinRelationshipValue()) {

            return true;
        }

        return false;
    }

    /**
     * @param float $stopTlhValue
     * @return $this
     */
    public function setStopTlhValue($stopTlhValue)
    {
        $this->stopTlhValue = $stopTlhValue;

        return $this;
    }

    /**
     * @return float
     */
    public function getStopTlhValue()
    {
        return $this->stopTlhValue;
    }

    public function isHouseholdLevelRebalancer()
    {
        return $this->getAccountManaged() == self::ACCOUNT_MANAGED_HOUSEHOLD;
    }

    public function isAccountLevelRebalancer()
    {
        return $this->getAccountManaged() == self::ACCOUNT_MANAGED_ACCOUNT;
    }

    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($key === 'accounts') {
                $accounts = new AccountCollection();
                foreach ($value as $accountData) {
                    $class = 'Model\WealthbotRebalancer\Account';

                    $account = new $class;
                    $account->loadFromArray($accountData);

                    $accounts->add($account, $account->getId());
                }

                $this->setAccounts($accounts);
            } elseif ($key === 'ria') {
                $class = 'Model\WealthbotRebalancer\Ria';
                $ria = new $class;
                $ria->loadFromArray($value);

                $this->setRia($ria);
            } elseif ($key === 'portfolio') {
                $class = 'Model\WealthbotRebalancer\Portfolio';
                $portfolio = new $class;
                $portfolio->loadFromArray($value);

                $this->setPortfolio($portfolio);
            } elseif ($key === 'job') {
                $class = 'Model\WealthbotRebalancer\Job';
                $job = new $class;
                $job->loadFromArray($value);

                $this->setJob($job);
            } else {
                $this->$key = $value;
            }
        }
    }
}