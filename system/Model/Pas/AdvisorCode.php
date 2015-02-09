<?php

namespace Model\Pas;

class AdvisorCode extends Base
{
    /**
     * @var  string
     */
    protected $name;

    /**
     * @var int
     */
    protected $custodianId;

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
     * @param int $custodianId
     * @return $this
     */
    public function setCustodianId($custodianId)
    {
        $this->custodianId = $custodianId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustodianId()
    {
        return $this->custodianId;
    }
}