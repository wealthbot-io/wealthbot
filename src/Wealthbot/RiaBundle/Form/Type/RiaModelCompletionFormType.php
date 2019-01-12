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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('users_and_user_groups', CheckboxType::class, ['required' => false])
            ->add('select_custodians', CheckboxType::class, ['required' => false])
            ->add('rebalancing_settings', CheckboxType::class, ['required' => false])
            ->add('customize_proposals', CheckboxType::class, ['required' => false])
            ->add('billingComplete', CheckboxType::class, ['required' => false])
            ->add('proposalDocuments', CheckboxType::class, ['required' => false])
        ;

        if ($model->isCustom()) {
            $builder
                ->add('create_securities', CheckboxType::class, ['required' => false])
                ->add('assign_securities', CheckboxType::class, ['required' => false])
                ->add('models_created', CheckboxType::class, ['required' => false])
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
