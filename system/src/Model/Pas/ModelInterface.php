<?php

namespace System\Model\Pas;

interface ModelInterface
{
    public function getId();

    public function setId($id);

    public function loadFromArray(array $data);
}