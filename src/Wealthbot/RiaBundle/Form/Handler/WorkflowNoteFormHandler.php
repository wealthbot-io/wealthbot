<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 09.10.13
 * Time: 16:00
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Handler;

use Wealthbot\AdminBundle\Form\Handler\AbstractFormHandler;

class WorkflowNoteFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $workflow = $this->form->getData();

        $this->em->persist($workflow);
        $this->em->flush();
    }
}
