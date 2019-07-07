<?php

namespace Model\Pas;

class Base implements ModelInterface
{
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get array of object relations
     *
     * @return array
     */
    protected function getRelations()
    {
        return array();
    }

    /**
     * Load object data from array
     *
     * @param array $data
     */
    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $setter = 'set' . ucfirst($key);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }

        $tmp = explode('_', $key);
        $tmp = array_map(function ($item) { return ucfirst($item); }, $tmp);

        $setter = 'set' . implode('', $tmp);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
    }
}