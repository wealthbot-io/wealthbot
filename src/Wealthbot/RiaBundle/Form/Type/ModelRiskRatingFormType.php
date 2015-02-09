<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 12.12.12
 * Time: 15:10
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ModelRiskRatingFormType extends AbstractType {

    private $maxRating;

    public function __construct($maxRating = 100)
    {
        $this->maxRating = (integer)$maxRating;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array_combine(range(1, $this->maxRating), range(1, $this->maxRating));
        $builder->add('risk_rating', 'choice', array(
            'empty_value' => false,
            'choices' => $choices,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\CeModel',
        ));
    }

    public function getName()
    {
        return 'model_risk_rating';
    }
}
