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

class CeModelFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $isShowAssumption = $this->getOption('is_show_assumption');

        /** @var $ceModel CeModel */
        $ceModel = $this->form->getData();

        if ($isShowAssumption) {
            $ceModel->setIsAssumptionLocked(true);
        };

        if ($this->form->has('risk_rating')) {
            $ceModel->setRiskRating($this->form->get('risk_rating')->getData());
        };

        $this->em->persist($ceModel);
        $this->em->flush();
    }
}
