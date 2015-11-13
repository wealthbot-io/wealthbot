<?php

namespace Model\Pas;

class Custodian extends Base
{
    /**
     * @var  string
     */
    protected $name;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getShortName()
    {
        $name = explode(' ', $this->name);

        return trim(strtoupper($name[0]));
    }
}