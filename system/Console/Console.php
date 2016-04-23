<?php

namespace Console;

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Lib\File;

class Console
{
    protected $pathTemplate = '/../incoming_files/{CUSTODIAN}/{CODE}/{PATH}/';

    protected $docMap = array(
        'TRN' => 'transactions',
        'SEC' => 'securities',
        'PRI' => 'prices',
        'POS' => 'positions',
        'TRD' => 'demographics',
        'TXT' => 'realized',
        'CBL' => 'unrealized',
        'CBP' => 'unrealized'
    );

    protected function getAbsoluteDestinationPath()
    {
        return __DIR__ . rtrim($this->pathTemplate, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Checks if the input date is valid and in the right format
     *
     * @param  string $date input YYYY-MM-DD
     * @return bool
     */
    protected function checkInputDate($date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        if ($dt !== false && !array_sum($dt->getLastErrors())) {
            return $dt->format('Y-m-d');
        }

        return false;
    }

    /**
     * @param $path
     * @return null|string
     * @throws \Exception
     */
    protected function getAbsolutePath($path)
    {
        $path = __DIR__ . DS . ltrim($path, DS);

        if ( ! File::exists($path)) {
            return null;
        }

        $directory = dirname($path);
        if ( ! File::isWritable($directory)) {
            throw new \Exception("Directory [$directory] is not writable");
        }

        return $path;
    }
}