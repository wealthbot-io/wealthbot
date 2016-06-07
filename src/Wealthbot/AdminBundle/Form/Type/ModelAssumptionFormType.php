<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vova
 * Date: 28.02.13
 * Time: 13:35
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Form\EventListener\ModelAssumptionFormTypeEventListener;

class ModelAssumptionFormType extends AbstractType
{
    /** @var \Doctrine\ORM\EntityManager  */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CeModel $data */
        $data = $builder->getData();
        $owner = $data->getOwner();

        if ($owner->hasRole('ROLE_RIA') && $owner->getRiaCompanyInformation()->getIsShowClientExpectedAssetClass()) {
            $builder
                ->add('generous_market_return', 'number', [
                        'label' => 'Generous Market Returns',
                        'precision' => 2,
                        'grouping' => true,
                        'required' => true,
                    ]
                )
                ->add('low_market_return', 'number', [
                        'label' => 'Low Market Returns',
                        'precision' => 2,
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
            'data_class' => 'Wealthbot\AdminBundle\Entity\CeModel',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_adminbundle_model_assumption_type';
    }
}
