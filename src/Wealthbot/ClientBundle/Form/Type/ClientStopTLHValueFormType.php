<?php

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\ClientBundle\Entity\ClientSettings;

class ClientStopTLHValueFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory) {
            /** @var ClientSettings $data */
            $data = $event->getData();
            $form = $event->getForm();

            if ($data && (null === $data->getStopTlhValue()) && $data->getClient()) {
                $riaCompanyInfo = $data->getClient()->getRiaCompanyInformation();
                $value = $riaCompanyInfo ? $riaCompanyInfo->getStopTlhValue() : null;
            } else {
                $value = null;
            }

            $form->add($factory->createNamed(
                'stop_tlh_value',
                'number',
                $value,
                ['required' => false, 'precision' => 2, 'grouping' => true, 'auto_initialize' => false]
            ));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Wealthbot\ClientBundle\Entity\ClientSettings']);
    }

    public function getBlockPrefix()
    {
        return 'stop_tlh_form';
    }
}
