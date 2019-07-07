<?php

namespace Console\Packer;

require_once(__DIR__ . '/../../Config.php');
require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Lib\File;

class Archive
{
    protected $date;
    protected $custodian;
    protected $custodianName;
    protected $advisorCode;
    protected $type;
    protected $fileNameTemplates = array(
        'data' => 'W{CODE}TD{DATE}.EXE',
        'unrealized' => 'W{CODE}CB{DATE}.EXE',
        'realized' => 'W{CODE}CB{DATE}.ZIP'
    );
    protected $filePathCollection = [];

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setCustodian($custodian)
    {
        $this->custodian = $custodian;
    }

    public function getCustodian()
    {
        return $this->custodian;
    }

    public function setCustodianName($custodianName)
    {
        $this->custodianName = $custodianName;
    }

    public function getCustodianName()
    {
        return $this->custodianName;
    }

    public function setAdvisorCode($advisorCode)
    {
        $this->advisorCode = $advisorCode;
    }

    public function getAdvisorCode()
    {
        return $this->advisorCode;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFileName()
    {
        return strtr($this->fileNameTemplates[$this->type], array(
            '{DATE}'      => $this->getDate(),
            '{CODE}'      => $this->getAdvisorCode()
        ));
    }

    public function getFilePath()
    {
        $fileName = $this->getFileName();
        $filePath = rtrim(\Config::$ARCHIVE_SOURCE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->getCustodianName() . DIRECTORY_SEPARATOR . $fileName;
        $this->filePathCollection[] = $filePath;
        if (File::exists($filePath)) {
            return $filePath;
        }

        return null;
    }

    public function getFilePathCollection()
    {
        return $this->filePathCollection;
    }

    public function debug()
    {
        $string = implode($this->getFilePathCollection(), "\n");

        return $string;
    }
}