<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Base implements ModelInterface {

    private $id;

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
     * @throws \Exception
     */
    public function loadFromArray(array $data = array())
    {
        $relations = $this->getRelations();

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $relations)) {
                $class = $relations[$key];
                $model = new $class();

                if (!($model instanceof Base)) {
                    throw new \Exception(sprintf(
                        'Invalid relation object. Relation object must be instance of %s, %s given.',
                        'Model\WealthbotRebalancer\Base',
                        $class
                    ));
                }

                $model->loadFromArray($value);
                $this->$key = $model;

            } else {
                $this->$key = $value;
            }
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

        $tmp = array_map(function ($item) {
            return ucfirst($item);
        }, $tmp);

        $setter = 'set' . implode('', $tmp);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
    }
}