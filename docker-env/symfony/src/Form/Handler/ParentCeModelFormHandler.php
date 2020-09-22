<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.03.13
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Entity\CeModel;

class ParentCeModelFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        /** @var $ceModel CeModel */
        $ceModel = $this->form->getData();

        $this->em->persist($ceModel);
        $this->em->flush();
    }
}
