<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Wealthbot\ClientBundle\Model\ClientAccount;
use Wealthbot\ClientBundle\Model\SystemAccount;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class AccountSettingsFormType extends AbstractType
{
    /**
     * @var FormFactory
     */
    protected $factory, $em;

    protected function getBillingAccountChoices($account)
    {
        $billingAccountChoices = array();
        foreach ($account->getClient()->getSystemAccounts() as $billingAccount) {
            /** @var \Wealthbot\ClientBundle\Entity\SystemAccount $billingAccount */
            $billingAccountId = $billingAccount->getId();
            $billingAccountOwner = $billingAccount->getClientAccount()->getPrimaryApplicant();
            $billingAccountName = $billingAccountOwner->getFirstName() . ' ' . $billingAccountOwner->getLastName();

            $billingAccountChoices[$billingAccountId] = $billingAccount->getAccountNumber() . ' ' . $billingAccountName;
        }
        return $billingAccountChoices;
    }

    public function __construct($em) {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $account = $builder->getData();

        $this->factory = $builder->getFormFactory();
        $statusChoices = array(
            SystemAccount::STATUS_ACTIVE => 'Account active',
            SystemAccount::STATUS_CLOSED => 'Account closed'
        );

        $builder
            ->add('dateClosed', 'date', array(
                'attr' => array('class' => 'jq-ce-date input-small'),
                'format' => 'MM-dd-yyyy',
                'property_path' => 'systemAccount.closed',
                'required' => false,
                'widget' => 'single_text'
            ))
            ->add('status', 'choice', array(
                'attr' => array('class' => 'input-medium'),
                'property_path' => 'systemAccount.status',
                'choices' => $statusChoices
            ))
            ->add('firstName', 'text', array(
                'attr' => array('class' => 'input-small'),
                'label' => 'First Name',
                'property_path' => 'primaryApplicant.firstName'
            ))
            ->add('lastName', 'text', array(
                'attr' => array('class' => 'input-small'),
                'label' => 'Last Name',
                'property_path' => 'primaryApplicant.lastName'
            ))
        ;

        $builder
            ->add('accountNumber', 'text', array(
                'attr' => array('class' => 'input-small'),
                'label' => 'Account Number: ',
                'property_path' => 'systemAccount.accountNumber'
            ))
            ->add('accountType', 'choice', array(
                'attr' => array('class' => 'input-xlarge'),
                'choices' => SystemAccount::getTypeChoices(),
                'label' => 'Account Type: ',
                'property_path' => 'systemAccount.type'
            ))
            ->add('sasCash', 'money', array(
                'attr' => array('class' => 'input-mini'),
                'currency' => 'USD',
                'label' => 'SAS Cash'
            ))
            ->add('performanceInception', 'date', array(
                'attr' => array('class' => 'jq-date input-small'),
                'format' => 'MM-dd-yyyy',
                'label' => 'Performance Inception: ',
                'property_path' => 'systemAccount.performanceInception',
                'widget' => 'single_text'
            ))
            ->add('billingInception', 'date', array(
                'attr' => array('class' => 'jq-date input-small'),
                'format' => 'MM-dd-yyyy',
                'label' => 'Billing Inception: ',
                'property_path' => 'systemAccount.billingInception',
                'widget' => 'single_text'
            ))
            ->add('billingAccount', 'entity', array(
                'class' => 'WealthbotClientBundle:SystemAccount',
                'label' => 'Billing Account: ',
                'property_path' => 'systemAccount.billingAccount',
                'query_builder' => function (EntityRepository $er) use ($account) {
                        return $er->createQueryBuilder('systemAccounts')
                            ->where('systemAccounts.client = :client')
                            ->setParameter('client', $account->getClient());
                }
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::BIND, array($this, 'onBindData'));
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $account = $event->getData();
        $systemAccount = $account->getSystemAccount();
        $rebalancerActionsRepo = $this->em->getRepository('WealthbotAdminBundle:RebalancerAction');

        if (null === $systemAccount->getBillingAccount()) {
            $systemAccount->setBillingAccount($systemAccount);
        }
        if (SystemAccount::STATUS_CLOSED === $systemAccount->getStatus() && null === $systemAccount->getClosed()) {
            $systemAccount->setClosed(new \DateTime());
        }

        $firstRebalance = $rebalancerActionsRepo
            ->findOneBy(array(
                'systemClientAccount' => $account->getSystemAccount()
            ), array(
                'started_at' => 'DESC'
            ))
        ;

        if (null !== $firstRebalance) {
            $initialRebalanceDate = $firstRebalance->getStartedAt();
            if (null === $systemAccount->getPerformanceInception()) {
                $systemAccount->setPerformanceInception($initialRebalanceDate);
            }
            if (null === $systemAccount->getBillingInception()) {
                $systemAccount->setBillingInception($initialRebalanceDate);
            }
        }

        $ssn = $account->getPrimaryApplicant()->getSsnTin();
        preg_match("~(\d{3})(\d{2})(\d{4})~", $ssn, $matches);
        if (empty($matches)) {
            $matches = array(1 => '', '', '');
        }

        $form
            ->add($this->factory->createNamed('ssn1', 'number', null, array(
                'attr' => array(
                    'class' => 'input-xmini',
                    'placeholder' => '###',
                ),
                'property_path' => false,
                'data' => $matches[1],
                'constraints' => array(
                    new NotBlank(array('message' => 'Can not be blank.')),
                    new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be a number.')),
                    new Length(array(
                        'min' => 3,
                        'max' => 3,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.'
                    ))
            ))))
            ->add($this->factory->createNamed('ssn2', 'number', null, array(
                'attr' => array(
                    'class' => 'input-xmini',
                    'placeholder' => '###',
                ),
                'property_path' => false,
                'data' => $matches[2],
                'constraints' => array(
                    new NotBlank(array('message' => 'Can not be blank.')),
                    new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be a number.')),
                    new Length(array(
                        'min' => 2,
                        'max' => 2,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.'
                    ))
            ))))
            ->add($this->factory->createNamed('ssn3', 'number', null, array(
                'attr' => array(
                    'class' => 'input-xmini',
                    'placeholder' => '###',
                ),
                'property_path' => false,
                'data' => $matches[3],
                'constraints' => array(
                    new NotBlank(array('message' => 'Can not be blank.')),
                    new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be a number.')),
                    new Length(array(
                        'min' => 4,
                        'max' => 4,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.'
                    ))
            ))))
        ;
    }

    public function onBindData(FormEvent $event)
    {
        /* @var ClientAccount $account */
        $account = $event->getData();

        /* @var User $user */
        $user = $account->getClient();
        $form = $event->getForm();

        $hasUnclosed = false;

        /* @var SystemAccount $systemAccount */
        foreach ($user->getSystemAccounts() as $systemAccount) {
            if (SystemAccount::STATUS_CLOSED != $systemAccount->getStatus()) {
                $hasUnclosed = true;
            }
        }

        if (!$hasUnclosed && $user->isEnabled()) {
            $user
                ->setEnabled($hasUnclosed)
                ->setClosed(new \DateTime())
            ;
        }

        if (SystemAccount::STATUS_CLOSED == $form->get('status')->getData() && null == $form->get('dateClosed')->getData()) {
            $form->get('dateClosed')->addError(new FormError('This field is required when closing account'));
        }

        if ($form->has('ssn1') && $form->has('ssn2') && $form->has('ssn3')) {
            $ssn1 = $form->get('ssn1')->getData();
            $ssn2 = $form->get('ssn2')->getData();
            $ssn3 = $form->get('ssn3')->getData();
            $account->getPrimaryApplicant()->setSsnTin($ssn1 . $ssn2 . $ssn3);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAccount'
        ));
    }

    public function getName()
    {
        return 'account_settings';
    }
}
