<?php

namespace Bug;

class Model 
{
    protected $type;
    protected $query;
    protected $message;
    protected $createdAt;
    
    const TYPE_INFO = 'INFO';
    const TYPE_ERROR = 'ERROR';    
    const TYPE_WARNING = 'WARNING';
    const TYPE_CRITICAL_ERROR = 'CRITICAL ERROR';    
    
    public function __construct($type, $message, $query)
    {
        $this->setType($type);
        $this->setQuery($query);
        $this->setMessage($message);
        $this->createdAt = new \DateTime();
    }
    
    public function __toString()
    {
        return $this->getCreatedAt() . " - " . $this->getType() . " - " . $this->getMessage() . " - Query: [" . $this->getQuery() . "]";
    }
    
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setQuery($query)
    {
        $this->query = $query;
        
        return $this;
    }
    
    public function getQuery()
    {
        return $this->query;        
    }

    public function setMessage($message)
    {
        $this->message = $message;
        
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }
    
    public function getCreatedAt()
    {
        return $this->createdAt->format('d/m/Y');
    }
    
    public function getLogString()
    {
        return $this->getMessage() . "" . " - Query: [" . $this->getQuery() . "]";
    }
}    