<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 15.08.13
 * Time: 16:41
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DocusignSessionPersistence extends AbstractDocusign
{
    const PREFIX = '_wealthbot_docusign_';

    private $session;
    private $prefix;

    public function __construct(array $config, SessionInterface $session, $prefix = self::PREFIX)
    {
        $this->session = $session;
        $this->prefix = $prefix;

        parent::__construct($config);
    }

    /**
     * Set $value data for $key in the persistent storage.
     *
     * @param string $key
     * @param $value
     */
    protected function setPersistentData($key, $value)
    {
        $this->session->set($this->constructSessionVariableName($key), $value);
    }

    /**
     * Get data for $key from the persistent storage.
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    protected function getPersistentData($key, $default = null)
    {
        $name = $this->constructSessionVariableName($key);
        if ($this->session->has($name)) {
            return $this->session->get($name);
        }

        return $default;
    }

    /**
     * Remove data with $key from the persistent storage.
     *
     * @param string $key
     */
    protected function removePersistentData($key)
    {
        $this->session->remove($this->constructSessionVariableName($key));
    }

    /**
     * Remove all data from the persistent storage.
     */
    protected function removeAllPersistentData()
    {
        foreach ($this->session->all() as $key => $value) {
            if (0 !== strpos($key, $this->prefix)) {
                continue;
            }

            $this->session->remove($key);
        }
    }

    private function constructSessionVariableName($key)
    {
        return $this->prefix.$key;
    }
}
