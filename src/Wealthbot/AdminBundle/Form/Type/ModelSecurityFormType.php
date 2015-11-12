<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 14.04.13
 * Time: 14:37
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Wealthbot\AdminBundle\Entity\AssetClass;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ModelSecurityFormType extends AbstractType
{
    /** @var \Wealthbot\AdminBundle\Entity\CeModel */
    private $model;
    private $em;

    public function __construct(CeModel $model, EntityManager $em)
    {
        $this->model = $model;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $model = $this->model;
        $em    = $this->em;

        $builder
            ->add('fund_symbol', 'text', array('property_path' => false /*'fund.symbol'*/))
            ->add('security_id', 'hidden', array('constraints' => array(new NotBlank(array('message' => 'Please choice a Symbol from list.')))))
            ->add('type', 'hidden', array('mapped' => false))
            ->add('expense_ratio', 'hidden', array('mapped' => false));

        $factory = $builder->getFormFactory();

        if ($model->getOwner()->hasRole('ROLE_RIA') && $model->getOwner()->getRiaCompanyInformation()->getUseMunicipalBond()) {
            $builder->add('muni_substitution', 'checkbox', array('required' => false));
        }

        $refreshSubclasses = function($form, $assetClass) use ($factory, $model) {

            $form->add($factory->createNamed('subclass_id', 'entity', null, array(
                'class' => 'WealthbotAdminBundle:Subclass',
                'query_builder' => function(EntityRepository $er) use ($model, $assetClass) {
                    $qb = $er->createQueryBuilder("s")
                        ->leftJoin("s.assetClass", "ac")
                        ->andWhere("ac.model_id = :model_id")
                        ->andWhere("ac.id = :asset_class_id")
                        ->setParameter("model_id", $model->getId())
                        ->orderBy("s.name");

                    if($assetClass instanceof AssetClass){
                        $qb->setParameter("asset_class_id", $assetClass->getId());
                    } elseif(is_numeric($assetClass)) {
                        $qb->setParameter('asset_class_id', $assetClass);
                    } else {
                        $qb->setParameter('asset_class_id', null);
                    }
                    return $qb;
                },
                'property_path' => 'subclass',
                'empty_value' => 'Choose an option'
            )));

            $form->add($factory->createNamed('asset_class_id', 'entity', null, array(
                'class' => 'WealthbotAdminBundle:AssetClass',
                'query_builder' => function(EntityRepository $er) use ($model) {
                    return $er->createQueryBuilder('ac')
                        ->where("ac.model_id = :model_id")
                        ->setParameter(":model_id", $model->getId())
                        ->orderBy("ac.name");
                },
                'empty_value' => 'Choose an option',
                'property_path' => false,
                'data' => $assetClass ? $assetClass : null
            )));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($refreshSubclasses){

            $form = $event->getForm();
            $data = $event->getData();

            if($data === null){
                return;
            }

            if($data instanceof SecurityAssignment){
                if($data->getSubclass()){
                    $refreshSubclasses($form, $data->getSubclass()->getAssetClass());
                } else {
                    $refreshSubclasses($form, null);
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) use ($refreshSubclasses) {
            $form = $event->getForm();
            $data = $event->getData();

            if(array_key_exists('asset_class_id', $data)) {
                $refreshSubclasses($form, $data['asset_class_id']);
            }
        });

        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) use ($em, $model){
            $form = $event->getForm();
            $data = $event->getData();

            if($data === null) return;

            $exist = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findByModelIdAndSecurityId($model->getId(), $data->getSecurityId());
            if($exist && $data->getId() != $exist->getId()) {
                $form->addError(new FormError('This SecurityAssignment already exist for this model.'));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\SecurityAssignment'
        ));
    }

    public function getName()
    {
        return 'model_security_form';
    }
}