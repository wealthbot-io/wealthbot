<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.04.13
 * Time: 17:53
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use App\Entity\Distribution;
use App\Form\Validator\BankInformationFormValidator;
use App\Form\Validator\ScheduledDistributionFormValidator;

class ScheduledDistributionFormEventSubscriber implements EventSubscriberInterface
{
    private $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preBind',
            FormEvents::SUBMIT => 'bind',
        ];
    }

    /**
     * PRE_SET_DATA event handler.
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $date = ['month' => '', 'day' => ''];
        if (null !== $data && $data->getTransferDate()) {
            /** @var \DateTime $transferDate */
            $transferDate = $data->getTransferDate();

            $date['month'] = $transferDate->format('m');
            $date['day'] = $transferDate->format('d');
        }

        $this->updateStartTransferDate($form, $date);

        $frequencyChoices = $this->getFrequencyChoices();
        if (is_array($frequencyChoices) && count($frequencyChoices)) {
            $form->add($this->factory->createNamed('frequency', ChoiceType::class, null, [
                'choices' => $frequencyChoices,
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'auto_initialize' => false,
            ]));
        }
    }

    /**
     * PRE_SUBMIT event handler.
     *
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (array_key_exists('transfer_date_month', $data) && array_key_exists('transfer_date_day', $data)) {
            $this->updateStartTransferDate(
                $form,
                [
                    'day' => $data['transfer_date_day'],
                    'month' => $data['transfer_date_month'],
                ]
            );
        }
    }

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

        $validator = new ScheduledDistributionFormValidator($form, $data);
        $validator->validate();

        if ($form->has('bankInformation')) {
            if (!$data->getBankInformation()) {
                $form->get('bankInformation')->addError(new FormError('Required.'));
            } else {
                $bankInformationValidator = new BankInformationFormValidator(
                    $form->get('bankInformation'),
                    $data->getBankInformation()
                );

                $bankInformationValidator->validate();
            }
        }
    }

    /**
     * Add transfer date fields.
     *
     * @param FormInterface $form
     * @param array         $date
     */
    protected function updateStartTransferDate(FormInterface $form, array $date)
    {
        $form->add($this->factory->createNamed('transfer_date_month', TextType::class, null, [
            'attr' => ['value' => $date['month']],
            'mapped' => false,
            'required' => false,
            'auto_initialize' => false,
        ]))
        ->add($this->factory->createNamed('transfer_date_day', TextType::class, null, [
            'attr' => ['value' => $date['day']],
            'mapped' => false,
            'required' => false,
            'auto_initialize' => false,
        ]));
    }

    /**
     * Get choices for frequency field.
     *
     * @return array
     */
    protected function getFrequencyChoices()
    {
        return array_reverse(Distribution::getFrequencyChoices(), true);
    }
}
