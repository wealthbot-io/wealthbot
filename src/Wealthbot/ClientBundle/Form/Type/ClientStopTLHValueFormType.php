<?php


namespace Wealthbot\ClientBundle\Form\Type;

use Wealthbot\ClientBundle\Entity\ClientSettings;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                array('required' => false, 'precision' => 2, 'grouping' => true)
            ));
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Wealthbot\ClientBundle\Entity\ClientSettings'));
    }


    public function getName()
    {
        return 'stop_tlh_form';
    }
}
