<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 14.01.14
 * Time: 19:29.
 */

namespace App\Twig;

use App\Entity\Workflow;
use App\Manager\WorkflowManager;
use App\Entity\Document;
use App\Manager\DocumentManager;

class WorkflowTwigExtension extends \Twig_Extension
{
    /** @var \App\Manager\WorkflowManager */
    private $manager;

    /** @var \App\Manager\DocumentManager */
    private $documentManager;

    public function __construct(WorkflowManager $manager, DocumentManager $documentManager)
    {
        $this->manager = $manager;
        $this->documentManager = $documentManager;
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('workflow_activity', [$this, 'getActivity']),
            new \Twig\TwigFunction('workflowable_object', [$this, 'getWorkflowableObject']),
            new \Twig\TwigFunction('workflowable_objects', [$this, 'getWorkflowableObjects']),
            new \Twig\TwigFunction('workflow_documents', [$this, 'getWorkflowDocuments']),
            new \Twig\TwigFunction('workflow_documents_link', [$this, 'getWorkflowDocumentsLink']),
        ];
    }

    public function getActivity(Workflow $workflow)
    {
        return $this->manager->getActivity($workflow);
    }

    public function getWorkflowableObject(Workflow $workflow)
    {
        return $this->manager->getObject($workflow);
    }

    public function getWorkflowableObjects(Workflow $workflow)
    {
        return $this->manager->getObjects($workflow);
    }

    public function getWorkflowDocuments(Workflow $workflow)
    {
        return $this->manager->getDocumentsToDownload($workflow);
    }

    public function getWorkflowDocumentsLink(Workflow $workflow)
    {
        $documents = $this->getWorkflowDocuments($workflow);
        $count = count($documents);

        $link = '';
        if ($count > 1) {
            $link = $this->documentManager->getDocumentsPackageLink(
                $documents,
                'workflow_'.$workflow->getId().'_documents.zip'
            );
        } elseif (1 === $count) {
            $documents = array_values($documents);
            if (count($documents) > 0) {
                /** @var Document $document */
                $document = $documents[0];

                if (null !== $document->getFilename()) {
                    $link = $this->documentManager->getDownloadLink($document->getFilename());
                }
            }
        }

        return $link;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'rx_workflow_twig_extension';
    }
}
