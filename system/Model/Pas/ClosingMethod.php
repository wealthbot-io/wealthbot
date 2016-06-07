<?php

namespace Model\Pas;

class ClosingMethod extends Base
{
    /**
     * @var  string
     */
    protected $name;

    public function setName($name)
    {
        return $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}