<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 22.12.12
 * Time: 1:45
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RiskAdjustmentFormType extends AbstractType
{
    private $models;

    public function __construct($models)
    {
        $this->models = $models;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ratings', 'collection', [
                'type' => new ModelRiskRatingFormType(),
                'data' => $this->models,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $values = [];
                foreach ($data['ratings'] as $rating) {
                    $values[] = $rating['risk_rating'];
                }

                $unique = array_unique($values);
                if (count($unique) !== count($values)) {
                    $form->addError(new FormError('Please choose different risk for models'));
                }
            });
    }

    public function getDefaultOptions(array $options)
    {
        return [
        ];
    }

    public function getBlockPrefix()
    {
        return 'risk_adjustment';
    }
}
