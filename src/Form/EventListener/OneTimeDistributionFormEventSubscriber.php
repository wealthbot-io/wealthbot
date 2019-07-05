<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.04.13
 * Time: 18:51
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use App\Form\Validator\OneTimeDistributionFormValidator;

class OneTimeDistributionFormEventSubscriber extends ScheduledDistributionFormEventSubscriber
{
    /**
     * BIND event handler
     * Validate form fields.
     *
     * @param FormEvent $event
     */
    public function bind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if ($form->has('transfer_date_month') && $form->has('transfer_date_day')) {
            $month = $form->get('transfer_date_month')->getData();
            $day = $form->get('transfer_date_day')->getData();
            $year = date('Y');

            if ($month && $day) {
                $date = new \DateTime($year.'-'.$month.'-'.$day);
                $data->setTransferDate($date);
            }
        }

        $validator = new OneTimeDistributionFormValidator($form, $data);
        $validator->validate();
    }

    /**
     * Get choices for frequency field.
     *
     * @return array
     */
    protected function getFrequencyChoices()
    {
        return [];
    }
}
