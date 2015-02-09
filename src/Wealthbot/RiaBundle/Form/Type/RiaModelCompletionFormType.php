<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.12.12
 * Time: 12:43
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RiaModelCompletionFormType extends AbstractType {

    private $ria;
    private $em;

    public function __construct(User $ria, EntityManager $em)
    {
        $this->ria = $ria;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $model = $this->ria->getRiaCompanyInformation()->getPortfolioModel();

        $builder
            ->add('users_and_user_groups', 'checkbox', array('required' => false))
            ->add('select_custodians', 'checkbox', array('required' => false))
            ->add('rebalancing_settings', 'checkbox', array('required' => false))
            ->add('customize_proposals', 'checkbox', array('required' => false))
            ->add('billingComplete', 'checkbox', array('required' => false))
            ->add('proposalDocuments', 'checkbox', array('required' => false))
        ;

        if ($model->isCustom()) {
            $builder
                ->add('create_securities', 'checkbox', array('required' => false))
                ->add('assign_securities', 'checkbox', array('required' => false))
                ->add('models_created', 'checkbox', array('required' => false))
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaModelCompletion',
        ));
    }

    public function getName()
    {
        return 'model_completion';
    }

}
