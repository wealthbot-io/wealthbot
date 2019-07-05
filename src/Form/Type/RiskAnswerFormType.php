<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.11.12
 * Time: 18:06
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiskAnswerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array_combine(range(-100, 100), range(-100, 100));

        $builder->add('title')
            ->add('point', ChoiceType::class, [
                'placeholder' => 'Select value',
                'choices' => $choices,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @param \App\Entity\RiskAnswer $data */
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiskAnswer',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'rx_risk_answer';
    }
}
