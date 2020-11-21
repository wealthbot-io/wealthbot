<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.03.13
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Entity\CeModelEntity;

class CeModelEntityFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $model = $this->getOption('model');

        $isQualified = $this->getOption('is_qualified');

        /** @var $modelEntity CeModelEntity */
        $modelEntity = $this->form->getData();

        if (!$modelEntity->getId()) {
            $modelEntity->setModel($model);
        } else {
            $nowDate = new \DateTime();
            $modelEntity->setUpdated($nowDate);
            $modelEntity->setNbEdits(($modelEntity->getNbEdits() + 1));
        }

        if (null !== $isQualified) {
            $modelEntity->setIsQualified($isQualified);
        }

        $this->em->persist($modelEntity);
        $this->em->flush();
    }
}
