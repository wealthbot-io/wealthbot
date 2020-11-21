<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.10.12
 * Time: 17:56
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\EventListener\CeModelFormTypeEventsListener;

class CeModelFormType extends ParentCeModelFormType
{
    /** @var EntityManager */
    private $em;

    private $user;

    private $isShowAssumption;

    private $parent;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $this->user = $options['user'];
        $this->parent = $options['parent'];
        $this->isShowAssumption = $options['isShowAssumption'];

        parent::buildForm($builder, $options);

        if ($this->isShowAssumption) {
            $modelAssumptionType = new ModelAssumptionFormType();
            $modelAssumptionType->buildForm($builder, $options);
        }

        $this->subscribe($builder);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\CeModel',
            'em' => null,
            'user' =>  null,
            'parent' => null,
            'isShowAssumption' => null
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
