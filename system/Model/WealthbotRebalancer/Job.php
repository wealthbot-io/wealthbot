<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Job extends Base {

    const JOB_NAME_REBALANCER = 'rebalancer';

    const REBALANCE_TYPE_FULL          = 0;
    const REBALANCE_TYPE_REQUIRED_CASH = 1;
    const REBALANCE_TYPE_FULL_AND_TLH  = 2;
    const REBALANCE_TYPE_NO_ACTIONS    = 3;
    const REBALANCE_TYPE_INITIAL       = 4;

    /** @var  string */
    private $name;

    /** @var int */
    private $rebalanceType;

    /** @var  string */
    private $startedAt;

    /** @var  string */
    private $finishedAt;

    /** @var  bool */
    private $isError;

    /** @var  Ria */
    private $ria;

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
     * @param string $finished_at
     * @return $this
     */
    public function setFinishedAt($finished_at)
    {
        $this->finishedAt = $finished_at;

        return $this;
    }

    /**
     * @return string
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * @param bool $is_error
     * @return $this
     */
    public function setIsError($is_error)
    {
        $this->isError = $is_error;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsError()
    {
        return $this->isError;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $rebalanceType
     * @return $this
     */
    public function setRebalanceType($rebalanceType)
    {
        $this->rebalanceType = $rebalanceType;

        return $this;
    }

    /**
     * @return int
     */
    public function getRebalanceType()
    {
        return $this->rebalanceType;
    }

    /**
     * Is Full rebalance
     *
     * @return bool
     */
    public function isFullRebalance()
    {
        return ($this->rebalanceType == self::REBALANCE_TYPE_FULL);
    }

    /**
     * Is Required cash rebalance
     *
     * @return bool
     */
    public function isRequiredCashRebalance()
    {
        return ($this->rebalanceType == self::REBALANCE_TYPE_REQUIRED_CASH);
    }

    /**
     * Is Full and TLH rebalance
     *
     * @return bool
     */
    public function isFullAndTlhRebalance()
    {
        return ($this->rebalanceType == self::REBALANCE_TYPE_FULL_AND_TLH);
    }

    /**
     * @param string $started_at
     * @return $this
     */
    public function setStartedAt($started_at)
    {
        $this->startedAt = $started_at;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    protected function getRelations()
    {
        return array(
            'ria' => 'Model\WealthbotRebalancer\Ria'
        );
    }
}