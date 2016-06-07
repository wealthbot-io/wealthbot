<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.08.13
 * Time: 12:27
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\UserBundle\Form\Handler;

use Wealthbot\AdminBundle\Entity\Custodian;
use Wealthbot\UserBundle\Entity\Document;

class CustodianDocumentsFormHandler extends DocumentsFormHandler
{
    protected function getDocumentsOwner()
    {
        $owner = $this->getOption('documents_owner');
        if (!($owner instanceof Custodian)) {
            throw new \InvalidArgumentException(sprintf('Option documents_owner must be instance of %s', get_class(new Custodian())));
        }

        return $owner;
    }

    protected function getExistDocuments($owner)
    {
        $documents = [];
        foreach ($owner->getCustodianDocuments() as $doc) {
            $documents[$doc->getType()] = $doc;
        }

        return $documents;
    }

    protected function addDocumentForOwner($owner, Document $document)
    {
        if (!$owner->getCustodianDocuments()->contains($document)) {
            $owner->addCustodianDocument($document);
        }
    }
}
