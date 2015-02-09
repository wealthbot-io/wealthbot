<?php

namespace Console;

require_once(__DIR__ . '/../Config.php');
require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Lib\File;
use Lib\ArrayCollection;
use Model\Pas\Repository\CustodianRepository;
use Model\Pas\Repository\AdvisorCodeRepository;
use Console\Packer\Archive;

class PackManager extends Console
{
    /**
     * @var string
     */
    protected $date;

    /**
     * @var array $counters
     */
    protected $counters = array(
        'unrealized' => 0,
        'realized' => 0,
        'data' => 0
    );

    /**
     * @param array $argv
     */
    public function __construct($argv)
    {
        if ( ! $inputDate = $this->checkInputDate($argv[1])) {
            throw new \Exception("File date is required. Format: YYYY-MM-DD\n");
        }

        $this->setDate($inputDate);

        $path = \Config::$ARCHIVE_UNPACK_PATH;
        if ( ! $this->checkPathAccess($path)) {
            throw new \Exception("Directory [$path] is not accessible\n");
        }
    }

    public function run()
    {
        foreach ($this->generateArchiveCollection() as $archive) {
            $this->unPack($archive);
            $this->moveFile($archive);
        }
        $this->emailReport();
    }

    /**
     * @param string $path
     * @return bool
     */
    public function checkPathAccess($path)
    {
        return File::exists($path) && File::isWritable($path);
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        $this->date = $dt->format('md');
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return ArrayCollection
     */
    protected function generateArchiveCollection()
    {
        $collection = new ArrayCollection();
        $custodianRepo = new CustodianRepository();
        $advisorCodeRepo = new AdvisorCodeRepository();

        foreach ($custodianRepo->findAll() as $custodian) {
            $codes = $advisorCodeRepo->findBy(array(
                'custodian_id' => $custodian->getId()
            ));

            foreach ($codes as $code) {
                $archive = new Archive();
                $archive->setDate($this->getDate());
                $archive->setCustodian($custodian->getShortName());
                $archive->setCustodianName($custodian->getName());
                $archive->setAdvisorCode($code->getName());
                $archive->setType('data');
                $collection->add($archive);

                $realized = clone($archive);
                $realized->setType('realized');
                $collection->add($realized);

                $unrealized = clone($archive);
                $unrealized->setType('unrealized');
                $collection->add($unrealized);
            }
        }

        return $collection;
    }

    /**
     * @param Archive $archive
     */
    public function unPack(Archive $archive)
    {
        $path = $archive->getFilePath();

        if ($path) {
            $command = \Config::$UNPACK_COMMAND . ' ' . \Config::$UNPACK_DESTINATION_COMMAND . ' "' . \Config::$ARCHIVE_UNPACK_PATH . '"' . ' "' . $path . '" ';
            exec($command, $output);

            if (\Config::$DEBUG) {
                $output = implode($output, "\n") . "\n";
                echo $output;
            }
            $this->counters[$archive->getType()]++;
        } else {
            if (\Config::$DEBUG) {
                $debug = $archive->debug();
                echo "Path [$debug] not found.\n";
            }
        }
    }

    /**
     * @param Archive $archive
     * @throws \Exception
     */
    public function moveFile(Archive $archive)
    {
        $files = [];
        $iterator = new \FilesystemIterator(\Config::$ARCHIVE_UNPACK_PATH, \FilesystemIterator::SKIP_DOTS);
        $extensions = array_keys($this->docMap);

        foreach ($iterator as $fileInfo) {
            $ext = $fileInfo->getExtension();
            if (in_array($ext, $extensions)) {
                $files[$ext] = $fileInfo->getPathname();
                try {
                    $path = $this->getDestinationFilePath($archive, $ext);
                    File::move($files[$ext], $path . $fileInfo->getFilename());
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }
        }
    }

    private function isReceived($fileType)
    {
        return $this->counters[$fileType] > 0 ? 'Received' : 'Not Received';
    }

    private function isAllFiletypesReceived()
    {
        if (empty($this->counters)) {
            return false;
        }
        foreach ($this->counters as $fileTypeCounter) {
            if ($fileTypeCounter == 0) {
                return false;
            }
        }
        return true;
    }

    public function emailReport()
    {
        $dayOfWeek = date('w');
        if ($dayOfWeek > 1 && !$this->isAllFiletypesReceived()) {
            $from = 'From: ' . \Config::$from;
            $subject = 'File Status Report';
            $body = "File Status (" . date('m/d/Y') . "):" . "\n" .
                "Data Files: " . $this->isReceived('data') . "\n" .
                "Unrealized: " . $this->isReceived('unrealized') . "\n" .
                "Realized: " . $this->isReceived('realized');
            mail(\Config::$to, $subject, $body, $from);
        }
    }

    /**
     * @param Archive $archive
     * @param $ext
     * @return string
     * @throws \Exception
     */
    protected function getDestinationFilePath(Archive $archive, $ext)
    {
        $path = strtr($this->getAbsoluteDestinationPath(), array(
            '{PATH}' => $this->docMap[$ext],
            '{CODE}' => $archive->getAdvisorCode(),
            '{CUSTODIAN}' => $archive->getCustodian()
        ));

        if (File::exists($path)) {
            return $path;
        }

        if (File::mkdir($path) == false) {
            throw new \Exception("Path [$path] has not been created.");
        }

        return $path;
    }
}

ini_set('memory_limit', '-1');

$packManager = new PackManager($argv);
$packManager->run();