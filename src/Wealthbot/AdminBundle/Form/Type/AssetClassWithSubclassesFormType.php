<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 09.04.13
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */
namespace Wealthbot\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Proxies\__CG__\Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\AdminBundle\Entity\AssetClass;
use Symfony\Component\Validator\Constraints\NotBlank;

class AssetClassWithSubclassesFormType extends AbstractType
{
    private $user;

    private $em;

    private $allSubclasses;

    public function __construct(User $user, EntityManager $em, $subclasses = array())
    {
        $this->user = $user;
        $this->em = $em;
        $this->allSubclasses = $subclasses;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em = $this->em;
        $user = $this->user;
        $allSubclasses = $this->allSubclasses;

        $builder
            ->add('name', 'text', array('constraints' => array(new NotBlank())));

        if ($user->hasRole('ROLE_RIA') &&
                $user->getRiaCompanyInformation()->isRebalancedFrequencyToleranceBand())
        {
            $builder->add('tolerance_band', 'number', array('precision' => 2));
        }

        $factory = $builder->getFormFactory();

        $refreshSubclasses = function($form, $allSubclasses) use ($factory, $user, $em) {
            $form->add($factory->createNamed('subclasses', 'collection', null, array(
                'type'               => new SubclassFormType($user, $em, $allSubclasses),
                'cascade_validation' => true,
                'allow_add'          => true,
                'allow_delete'       => true,
                'prototype'          => true,
                'by_reference'       => false,
            )));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($refreshSubclasses, $em, $allSubclasses) {
            $form = $event->getForm();
            $data = $event->getData();

            if($data === null) return;

            if($data instanceof AssetClass) {
                $refreshSubclasses($form, $allSubclasses);
            }
        });


        $builder->addEventListener(FormEvents::PRE_BIND, function(FormEvent $event) use($refreshSubclasses, $user, $allSubclasses) {
            $form = $event->getForm();
            $data = $event->getData();

            if($data === null) return;

            $refreshSubclasses($form, $allSubclasses);
        });


        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event) {
            $data = $event->getData();
            $data->setType(AssetClass::TYPE_STOCKS);
            $data->setSubclasses($data->getSubclasses());
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\AssetClass',
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'asset';
    }
}