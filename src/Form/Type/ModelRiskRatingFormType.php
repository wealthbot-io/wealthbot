<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 12.12.12
 * Time: 15:10
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelRiskRatingFormType extends AbstractType
{
    private $maxRating;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->maxRating = (int) $options['maxRating'] ?? $options['maxRating'] ?? 100;

        $choices = array_combine(range(1, $this->maxRating), range(1, $this->maxRating));
        $builder->add('risk_rating', ChoiceType::class, [
            'placeholder' => false,
            'choices' => $choices,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\CeModel',
            'maxRating' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'model_risk_rating';
    }
}
