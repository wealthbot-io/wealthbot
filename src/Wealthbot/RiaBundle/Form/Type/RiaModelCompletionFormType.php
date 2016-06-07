<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.12.12
 * Time: 12:43
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Entity\User;

class RiaModelCompletionFormType extends AbstractType
{
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
            ->add('users_and_user_groups', 'checkbox', ['required' => false])
            ->add('select_custodians', 'checkbox', ['required' => false])
            ->add('rebalancing_settings', 'checkbox', ['required' => false])
            ->add('customize_proposals', 'checkbox', ['required' => false])
            ->add('billingComplete', 'checkbox', ['required' => false])
            ->add('proposalDocuments', 'checkbox', ['required' => false])
        ;

        if ($model->isCustom()) {
            $builder
                ->add('create_securities', 'checkbox', ['required' => false])
                ->add('assign_securities', 'checkbox', ['required' => false])
                ->add('models_created', 'checkbox', ['required' => false])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaModelCompletion',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'model_completion';
    }
}
