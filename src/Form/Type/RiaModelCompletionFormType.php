<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.12.12
 * Time: 12:43
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class RiaModelCompletionFormType extends AbstractType
{
    private $ria;
    private $em;
    private $modelCompletion;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->ria = $options['user'];
        $this->em = $options['em'];
        $this->modelCompletion = $options['modelCompletion'];

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
            'data_class' => 'App\Entity\RiaModelCompletion',
            'user' => null,
            'em' => null,
            'modelCompletion' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'model_completion';
    }
}
