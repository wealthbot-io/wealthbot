<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 28.12.13
 * Time: 14:54.
 */

namespace App\Twig;

use App\Manager\DocumentManager;

class DocumentExtension extends \Twig_Extension
{
    /** @var \App\Manager\DocumentManager */
    private $manager;

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('download_document_link', [$this, 'getDownloadDocumentLink']),
            new \Twig\TwigFunction('download_archive_link', [$this, 'getDownloadArchiveLink']),
        ];
    }

    public function getDownloadDocumentLink($filename, $originalName = null)
    {
        return $this->manager->getDownloadLink($filename, $originalName);
    }

    public function getDownloadArchiveLink($documents, $originalName = null)
    {
        return $this->manager->getDocumentsPackageLink($documents, $originalName);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'rx_document_extension';
    }
}
