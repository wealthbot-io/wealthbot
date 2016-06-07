<?php

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

class AutoLoader 
{
	public static function autoload($className) 
    {
	    $thisClass = str_replace(__NAMESPACE__.'\\', '', __CLASS__);

	    $baseDir = __DIR__ . DS;

		if (strstr($className, 'Psr')) {
			$baseDir = __DIR__ . DS . '..' . DS . 'vendor' . DS . 'psr' . DS . 'log' . DS;
		} else if (strstr($className, 'Monolog')) {
			$baseDir = __DIR__ . '/../vendor/monolog/monolog/src/';
		}

	    if (substr($baseDir, -strlen($thisClass)) === $thisClass) {
	        $baseDir = substr($baseDir, 0, -strlen($thisClass));
	    }

	    $className = ltrim($className, '\\');
	    $fileName = $baseDir;
	    $namespace = '';

        if ($lastNsPos = strripos($className, '\\')) {
	        $namespace = substr($className, 0, $lastNsPos);
	        $className = substr($className, $lastNsPos + 1);
	        $fileName .= str_replace('\\', DS, $namespace) . DS;
	    }

        $fileName .= str_replace('_', DS, $className) . '.php';

        if (file_exists($fileName)) {
	        require $fileName;
	    } else {
            return require_once __DIR__.'/../app/autoload.php';
        }
	}

	public static function registerAutoloader()
	{
	    spl_autoload_register(__NAMESPACE__ . "\\AutoLoader::autoload");
	}

}