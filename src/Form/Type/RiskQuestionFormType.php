<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.11.12
 * Time: 17:59
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiskQuestionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextareaType::class, [
                'attr' => ['rows' => 3],
            ])
            ->add('answers', CollectionType::class, [
                'entry_type' => RiskAnswerFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @param \App\Entity\RiskQuestion $data */
            $data = $event->getData();
            $form = $event->getForm();

            $answersCount = $data->getAnswers()->count();

            if ($answersCount < 2) {
                $form->get('title')->addError(new FormError('The question should have at least %nb_answers% answers', ['%nb_answers%' => 2]));
            } elseif ($answersCount > 5) {
                $form->get('title')->addError(new FormError('The question should have no more than %nb_answers% answers', ['%nb_answers%' => 5]));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiskQuestion',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'rx_risk_question';
    }
}
