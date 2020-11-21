<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Document;

class LoadDocumentData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $path = Document::getUploadRootDir();
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $document1 = $this->createDocument('test1.pdf', $path, Document::TYPE_ADV, 'adv-document');
        $document2 = $this->createDocument('test2.pdf', $path, Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT, 'inv-document');

        $manager->persist($document1);
        $manager->persist($document2);

        $manager->flush();
    }

    public function createDocument($fileName, $path, $type, $ref)
    {
        $document = new Document();
        copy(__DIR__.'/pdf/'.$fileName, $path.'/'.$fileName);
        $document->setMimeType('application/pdf');
        $document->setOriginalName($fileName);
        $document->setFilename($fileName);
        $document->setType($type);
        $this->addReference($ref, $document);

        return $document;
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 5;
    }
}
