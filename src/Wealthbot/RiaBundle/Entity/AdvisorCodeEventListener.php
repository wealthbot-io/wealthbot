<?php

/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 23.02.14
 * Time: 0:31
 */
namespace Wealthbot\RiaBundle\Entity;

use \Symfony\Component\Filesystem\Filesystem;

class AdvisorCodeEventListener
{
    private $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    public function createDirectory(AdvisorCode $advisorCode)
    {
        $filename = self::getIncomingFilesLocation() . $advisorCode->getName();

        if (!$this->fs->exists($filename)) {
            $this->fs->mkdir($filename, 0777);
        }
    }

    public static function getIncomingFilesLocation()
    {
        return __DIR__ . '/../../../../system/incoming_files/';
    }
}
