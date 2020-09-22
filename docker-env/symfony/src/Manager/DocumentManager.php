<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Custodian;
use App\Entity\Document;
use Symfony\Component\Routing\RouterInterface;

class DocumentManager
{
    protected $objectManager;
    protected $router;
    protected $class;
    protected $repository;

    public function __construct(EntityManagerInterface $om, $class, RouterInterface $router)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();

        $this->router = $router;
    }

    /**
     * Find one document by criteria.
     *
     * @param array $criteria
     *
     * @return object
     */
    public function findDocumentBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find user document.
     *
     * @param $documentId
     * @param $userId
     *
     * @return bool
     */
    public function isUserDocument($documentId, $userId)
    {
        if ($this->repository->isUserDocument($documentId, $userId)) {
            return true;
        }

        return false;
    }

    /**
     * Get user documents.
     *
     * @param int $userId
     *
     * @return array
     */
    public function getUserDocuments($userId)
    {
        $documents = $this->repository->findByUserId($userId);

        return $this->getAssocDocumentsArray($documents);
    }

    public function getUserDocumentSorted($userId, $sort, $direction)
    {
        return $this->repository->getUserDocumentSorted($userId, $sort, $direction);
    }

    /**
     * Get custodian documents.
     *
     * @param int $custodianId
     *
     * @return array
     */
    public function getCustodianDocuments($custodianId)
    {
        $documents = $this->repository->findByCustodianId($custodianId);

        return $this->getAssocDocumentsArray($documents);
    }

    /**
     * Get user documents by user_id and type.
     *
     * @param int    $userId
     * @param string $type
     *
     * @return mixed
     */
    public function getUserDocumentByType($userId, $type)
    {
        return $this->repository->getUserDocumentByType($userId, $type);
    }

    /**
     * Get custodian documents by custodian_id and type.
     *
     * @param int    $custodianId
     * @param string $type
     *
     * @return mixed
     */
    public function getCustodianDocumentByType($custodianId, $type)
    {
        return $this->repository->getCustodianDocumentByType($custodianId, $type);
    }

    /**
     * TODO: is it need?
     * Get user document link.
     *
     * @param int         $userId
     * @param string      $type
     * @param string|null $originalName
     *
     * @return string
     */
    public function getUserDocumentLinkByType($userId, $type, $originalName = null)
    {
        $document = $this->getUserDocumentByType($userId, $type);
        if (!$document) {
            return '#';
        }

        return $this->getDownloadLink($document->getFilename(), $originalName);
    }

    /**
     * TODO: is it need?
     * Get custodian document link.
     *
     * @param int         $custodianId
     * @param string      $type
     * @param string|null $originalName
     *
     * @return string
     */
    public function getCustodianDocumentLinkByType($custodianId, $type, $originalName = null)
    {
        $document = $this->getCustodianDocumentByType($custodianId, $type);
        if (!$document) {
            return '#';
        }

        return $this->getDownloadLink($document->getFilename(), $originalName);
    }

    /**
     * Returns array of custodian disclosure files links.
     * example: array('document_type' => array('title' => 'Disclosure', 'link' => 'CustodianDisclosure.pdf')).
     *
     * @param Custodian $custodian
     *
     * @return array
     */
    public function getCustodianDisclosuresLinks(Custodian $custodian)
    {
        $custodianId = $custodian->getId();
        $custodianPrefix = str_replace(' ', '', $custodian->getName());

        $documents = $this->getCustodianDocuments($custodianId);

        $result = [];
        foreach ($documents as $type => $document) {
            $nameParams = explode('_', $type);
            foreach ($nameParams as &$param) {
                if ('ira' === $param) {
                    $param = strtoupper($param);
                } else {
                    $param = ucfirst($param);
                }
            }

            $linkTitle = implode(' ', $nameParams);
            $linkFileName = $custodianPrefix.'_'.str_replace(' ', '', $linkTitle).'.pdf';

            $result[$type] = [
                'title' => $linkTitle,
                'link' => $this->getCustodianDocumentLinkByType($custodianId, $type, $linkFileName),
            ];
        }

        return $result;
    }

    /**
     * Get link for archive of the documents.
     *
     * @param \ArrayAccess|array $documents
     * @param string|null        $originalName
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getDocumentsPackageLink($documents, $originalName = null)
    {
        if (!is_array($documents) && !($documents instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException('Argument must be array or instanceof ArrayAccess.');
        }

        $hash = $this->generatePackageHash($documents);

        if (!$this->hasDocumentsPackage($hash)) {
            $tmpDir = Document::getUploadRootDir().'/tmp_'.$hash;
            if (is_dir($tmpDir)) {
                $this->removeDir($tmpDir);
            }

            mkdir($tmpDir);

            $files = [];

            foreach ($documents as $document) {
                if (!$document || !file_exists($document->getAbsolutePath())) {
                    continue;
                }

                $filename = $document->getOriginalName();
                $postfix = 1;
                while (in_array($filename, $files)) {
                    $parts = explode('.', $document->getOriginalName());

                    $parts[0] .= '_'.$postfix;
                    $filename = implode('.', $parts);

                    ++$postfix;
                }

                $files[] = $filename;
                $destination = $tmpDir.'/'.$filename;
                copy($document->getAbsolutePath(), $destination);
            }

            $fileString = implode(' ', $files);
            if (!$fileString) {
                rmdir($tmpDir);

                return '#';
            }

            exec('cd '.$tmpDir.' && zip '.Document::getUploadRootDir().'/'.$hash.'.zip '.$fileString);
            $this->removeDir($tmpDir);
        }

        return $this->getDownloadLink($hash.'.zip', $originalName);
    }

    /**
     * Generate hash for archive of the documents.
     *
     * @param \ArrayAccess|array $documents
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function generatePackageHash($documents)
    {
        if (!is_array($documents) && !($documents instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException('Argument must be array or instanceof ArrayAccess.');
        }

        return sha1($this->getArchivedFilesString($documents));
    }

    /**
     * Get concatenated string of names of the documents.
     *
     * @param \ArrayAccess|array $documents
     *
     * @return string
     */
    private function getArchivedFilesString($documents)
    {
        $fileString = '';
        foreach ($documents as $document) {
            if (!$document) {
                continue;
            }

            $fileString .= ' '.$document->getFilename();
        }

        return $fileString;
    }

    /**
     * Check if document exist by hash.
     *
     * @param string $hash
     *
     * @return bool
     */
    private function hasDocumentsPackage($hash)
    {
        return file_exists($this->getArchiveFileByHash($hash));
    }

    /**
     * Get archive file by hash.
     *
     * @param string $hash
     *
     * @return string
     */
    private function getArchiveFileByHash($hash)
    {
        return Document::getUploadRootDir().'/'.$hash.'.zip';
    }

    /**
     * Build array of documents as array('type' => document).
     *
     * @param \ArrayAccess|array $documents
     *
     * @return array
     */
    private function getAssocDocumentsArray($documents)
    {
        $result = [];

        foreach ($documents as $document) {
            $result[$document->getType()] = $document;
        }

        return $result;
    }

    /**
     * Recursive remove directory with files and subdirectories.
     *
     * @param $dir
     */
    private function removeDir($dir)
    {
        $it = new \RecursiveDirectoryIterator($dir);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ('.' === $file->getFilename() || '..' === $file->getFilename()) {
                continue;
            }
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }

    /**
     * Generate download link for $filename with $originalName name.
     *
     * @param string      $filename
     * @param string|null $originalName
     *
     * @return string
     */
    public function getDownloadLink($filename, $originalName = null)
    {
        return $this->router->generate('rx_download_document', [
            'filename' => $filename,
            'originalName' => $originalName,
        ]);
    }
}
