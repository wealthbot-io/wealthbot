<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 23.01.13
 * Time: 14:03
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\AccountGroup;
use App\Entity\ClientAccount;
use App\Form\EventListener\RiaClientAccountFormEventSubscriber;
use App\Entity\User;

class RiaClientAccountFormType extends AbstractType
{
    private $client;
    private $em;
    private $isAllowRetirementPlan;
    private $validateAdditionalFields;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->client = $options['client'];
        $this->em = $options['em'];
        $this->validateAdditionalFields = $options['validateAdditionalFields'] ?? true;

        $riaCompanyInformation = $this->client->getProfile()->getRia()->getRiaCompanyInformation();
        $this->isAllowRetirementPlan = $riaCompanyInformation->getIsAllowRetirementPlan();




        $groupChoices = [
            AccountGroup::GROUP_DEPOSIT_MONEY => 'New Account',
            AccountGroup::GROUP_FINANCIAL_INSTITUTION => 'Transfer',
            AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT => 'Rollover',
            //code_v2: NOT DELETE THIS CODE
            //AccountGroup::GROUP_EMPLOYER_RETIREMENT     => 'Advice'
        ];

        $factory = $builder->getFormFactory();
        //$isAllowRetirementPlan = $this->isAllowRetirementPlan;
        $data = $builder->getData();

        if (($data instanceof ClientAccount) && $data->getId()) {
            $selectedGroup = $data->getGroupName();
            $consolidate = ($data->getConsolidatorId() || !$data->getUnconsolidated()) ? true : false;
        } else {
            $selectedGroup = null;
            $consolidate = true;
        }

        $builder->add('group', ChoiceType::class, [
                'choices' => $groupChoices,
                'mapped' => false,
                'placeholder' => false,
                'data' => $selectedGroup,
            ])
            ->add('consolidate', CheckboxType::class, [
                'mapped' => false,
                'attr' => $consolidate ? ['checked' => 'checked'] : [],
                'required' => false,
            ])
            ->add('value', NumberType::class, [
                'grouping' => true,
                'scale' => 2,
                'label' => 'Estimated Value',
            ])
            ->add('monthly_contributions', NumberType::class, [
                'grouping' => true,
                'scale' => 2,
                'label' => 'Estimated Monthly Contributions', 'required' => false,
            ])
            ->add('monthly_distributions', NumberType::class, [
                'grouping' => true,
                'scale' => 2,
                'label' => 'Estimated Monthly Distributions', 'required' => false,
            ])
            ->add('sas_cash', NumberType::class, [
                'grouping' => true,
                'scale' => 2,
                'required' => false,
            ]);

        $subscriber = new RiaClientAccountFormEventSubscriber(
            $factory,
            $this->client,
            $this->em,
            $this->validateAdditionalFields
        );
        $builder->addEventSubscriber($subscriber);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ClientAccount',
            'validateAdditionalFields' => null,
            'em' => null,
            'client' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_client_account';
    }
}
