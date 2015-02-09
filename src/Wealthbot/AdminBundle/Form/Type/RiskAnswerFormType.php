<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.11.12
 * Time: 18:06
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;

class RiskAnswerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array_combine(range(-100, 100), range(-100, 100));

        $builder->add('title')
            ->add('point', 'choice', array(
                'empty_value' => 'Select value',
                'choices' => $choices
            ))
        ;

        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event) {
            /** @var \Wealthbot\RiaBundle\Entity\RiskAnswer $data */
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data->getTitle()) {
                $form->get('title')->addError(new FormError('Required.'));
            }

            if (!is_numeric($data->getPoint())) {
                $form->get('point')->addError(new FormError('Required.'));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiskAnswer'
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'rx_risk_answer';
    }

}
