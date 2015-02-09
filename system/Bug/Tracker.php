<?php

namespace Bug;

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Lib\Ioc;

class Tracker 
{
    protected $log;
    protected $models = array();
    
    public function __construct()
    {
        $this->log = Ioc::resolve('log');
	}
    
    public function getLog()
    {
        return $this->log;
    }
    
    public function addWarning($message, $query = null)
    {
        $model = new Model(Model::TYPE_WARNING, $message, $query);
        $this->models[] = $model;
        $this->log->addWarning($model->getLogString());
    }
    
    public function addError($message, $query = null)
    {
        $model = new Model(Model::TYPE_ERROR, $message, $query);
        $this->models[] = $model;
        $this->log->addError($model->getLogString());
    }    
    
    public function addCritical($message, $query = null)
    {
        $model = new Model(Model::TYPE_CRITICAL_ERROR, $message, $query);
        $this->models[] = $model;
        
        $this->log->addError($model->getLogString());
        $this->send();
        
        die("-- Critical error --\n");
    }
    
    public function send()
    {
        if (\Config::$DEBUG) {
            echo $this;
        }    
        
        // TODO: Send email here
    }
        
    public function __toString()
    {
        $output = "";
        
        foreach ($this->models as $key => $model) {
            $output .= "{$model}\n";
        }
        
        return $output;
    }
}    