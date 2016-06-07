<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.08.13
 * Time: 17:47
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Handler;

use Wealthbot\AdminBundle\Form\Handler\AbstractFormHandler;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\User;

class RegistrationStepOneFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $user = $this->getOption('user');
        if (!($user instanceof User)) {
            throw new \InvalidArgumentException(sprintf('Option user must be instance of %s', get_class(new User())));
        }

        /** @var RiaCompanyInformation $data */
        $data = $this->form->getData();

        $existDocuments = $this->getExistDocuments($user);
        $documentFiles = $this->form->get('documents')->getData();

        foreach ($documentFiles as $key => $file) {
            if (isset($existDocuments[$key])) {
                $document = $existDocuments[$key];
            } else {
                $document = new Document();
                $existDocuments[$key] = $document;
            }

            $document->setFile($file);
            $document->setType($key);
            $document->setOwnerId($user->getId());
            $document->upload();

            if (!$user->getUserDocuments()->contains($document)) {
                $user->addUserDocument($document);
            }
        }

        $data->setRia($user);

        $this->em->persist($data);
        $this->em->persist($user);

        $this->em->flush();
    }

    private function getExistDocuments(User $user)
    {
        $documents = [];
        foreach ($user->getUserDocuments() as $document) {
            $documents[$document->getType()] = $document;
        }

        return $documents;
    }
}
