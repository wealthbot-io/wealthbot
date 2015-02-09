<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 28.12.13
 * Time: 14:54
 */

namespace Wealthbot\UserBundle\Twig;


use Wealthbot\UserBundle\Manager\DocumentManager;

class DocumentExtension extends \Twig_Extension
{
    /** @var \Wealthbot\UserBundle\Manager\DocumentManager */
    private $manager;

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('download_document_link', array($this, 'getDownloadDocumentLink')),
            new \Twig_SimpleFunction('download_archive_link', array($this, 'getDownloadArchiveLink'))
        );
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