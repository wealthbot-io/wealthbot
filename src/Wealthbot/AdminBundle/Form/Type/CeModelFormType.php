<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.10.12
 * Time: 17:56
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\AdminBundle\Form\EventListener\CeModelFormTypeEventsListener;
use Wealthbot\AdminBundle\Model\CeModelInterface;
use Wealthbot\UserBundle\Entity\User;

class CeModelFormType extends ParentCeModelFormType
{
    /** @var EntityManager */
    private $em;

    private $user;

    private $isShowAssumption;

    private $parent;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event){
            $this->em = $event->getForm()->getConfig()->getOption('em');
            $this->user =  $event->getForm()->getConfig()->getOption('user');
            $this->parent =  $event->getForm()->getConfig()->getOption('parent');
            $this->isShowAssumption = $event->getForm()->getConfig()->getOption('isShowAssumption');
        });


        parent::buildForm($builder, $options);

        if ($this->isShowAssumption) {
            $modelAssumptionType = new ModelAssumptionFormType($this->em);
            $modelAssumptionType->buildForm($builder, $options);
        };

        $this->subscribe($builder);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\CeModel',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'third_party_model';
    }

    protected function subscribe(FormBuilderInterface $builder)
    {
        $subscriber = new CeModelFormTypeEventsListener($builder->getFormFactory(), $this->em, $this->parent, $this->user);
        $builder->addEventSubscriber($subscriber);
    }
}
