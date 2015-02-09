<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 23.01.13
 * Time: 14:03
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;


use Doctrine\ORM\EntityManager;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\RiaBundle\Form\EventListener\RiaClientAccountFormEventSubscriber;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RiaClientAccountFormType extends AbstractType
{
    private $client;
    private $em;
    private $isAllowRetirementPlan;
    private $validateAdditionalFields;

    public function __construct(User $client, EntityManager $em, $validateAdditionalFields = true)
    {
        $this->client = $client;
        $this->em = $em;
        $this->validateAdditionalFields = $validateAdditionalFields;

        $riaCompanyInformation = $client->getProfile()->getRia()->getRiaCompanyInformation();
        $this->isAllowRetirementPlan = $riaCompanyInformation->getIsAllowRetirementPlan();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $groupChoices = array(
            AccountGroup::GROUP_DEPOSIT_MONEY           => 'New Account',
            AccountGroup::GROUP_FINANCIAL_INSTITUTION   => 'Transfer',
            AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT => 'Rollover',
            //code_v2: NOT DELETE THIS CODE
            //AccountGroup::GROUP_EMPLOYER_RETIREMENT     => 'Advice'
        );

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

        $builder->add('group', 'choice', array(
                'choices' => $groupChoices,
                'property_path' => false,
                'empty_value' => false,
                'data' => $selectedGroup
            ))
            ->add('consolidate', 'checkbox', array(
                'mapped' => false,
                'attr' => $consolidate ? array('checked' => 'checked') : array(),
                'required' => false
            ))
            ->add('value', 'number', array(
                'grouping' => true,
                'precision' => 2,
                'label' => 'Estimated Value'
            ))
            ->add('monthly_contributions', 'number', array(
                'grouping' => true,
                'precision' => 2,
                'label' => 'Estimated Monthly Contributions', 'required' => false
            ))
            ->add('monthly_distributions', 'number', array(
                'grouping' => true,
                'precision' => 2,
                'label' => 'Estimated Monthly Distributions', 'required' => false
            ))
            ->add('sas_cash', 'number', array(
                'grouping' => true,
                'precision' => 2,
                'required' => false
            ));

        $subscriber = new RiaClientAccountFormEventSubscriber(
            $factory,
            $this->client,
            $this->em,
            $this->validateAdditionalFields
        );
        $builder->addEventSubscriber($subscriber);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAccount'
        ));
    }

    public function getName()
    {
        return 'ria_client_account';
    }
}