<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vova
 * Date: 28.02.13
 * Time: 13:35
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\CeModel;
use App\Form\EventListener\ModelAssumptionFormTypeEventListener;

class ModelAssumptionFormType extends AbstractType
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CeModel $data */
        $data = $builder->getData();
        $owner = $data->getOwner();

        $this->em = $options['em'];

        if ($owner->hasRole('ROLE_RIA') && $owner->getRiaCompanyInformation()->getIsShowClientExpectedAssetClass()) {
            $builder
                ->add(
                    'generous_market_return',
                    NumberType::class,
                    [
                        'label' => 'Generous Market Returns',
                        'scale' => 2,
                        'grouping' => true,
                        'required' => true,
                    ]
                )
                ->add(
                    'low_market_return',
                    NumberType::class,
                    [
                        'label' => 'Low Market Returns',
                        'scale' => 2,
                        'grouping' => true,
                        'required' => true,
                    ]
                );
        }

        $subscriber = new ModelAssumptionFormTypeEventListener($builder->getFormFactory(), $this->em);
        $builder->addEventSubscriber($subscriber);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\CeModel',
            'em'=>null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_adminbundle_model_assumption_type';
    }
}
