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

    public function __construct(EntityManager $em, User $user, CeModelInterface $parent, $isShowAssumption = false)
    {
        $this->user = $user;
        $this->parent = $parent;
        $this->em = $em;
        $this->isShowAssumption = $isShowAssumption;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($this->isShowAssumption) {
            $modelAssumptionType = new ModelAssumptionFormType($this->em);
            $modelAssumptionType->buildForm($builder, $options);
        }

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
