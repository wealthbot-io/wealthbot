<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.11.12
 * Time: 16:57
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints\NotBlank;

class SuggestedPortfolioFormType extends AbstractType {

    /** @var EntityManager $em */
    private $em;

    /** @var \Wealthbot\ClientBundle\Entity\ClientPortfolio */
    private $clientPortfolio;


    public function __construct(EntityManager $em, ClientPortfolio $clientPortfolio)
    {
        $this->em = $em;
        $this->clientPortfolio = $clientPortfolio;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em      = $this->em;
        $profile = $builder->getData();
        $ria     = $profile->getRia();
        $client  = $profile->getUser();
        $riaCompanyInfo = $ria->getRiaCompanyInformation();

        if(!$profile || !$profile->getId()){
            throw new \Exception("Profile is required.");
        }

        $builder
            ->add('action_type', 'hidden', array(
                'property_path' => false,
                'attr' => array('value' => '')
            ))
            ->add('unconsolidated_ids', 'hidden', array(
                'mapped' => false,
                'attr' => array('value' => '')
            ))
            ->add('is_qualified', 'hidden', array(
                'property_path' => false,
            ))
            ->add('paymentMethod', 'choice', array(
                'choices' => array(
                    Profile::PAYMENT_METHOD_DIRECT_DEBIT => 'Direct Debit',
                    Profile::PAYMENT_METHOD_OUTSIDE_PAYMENT => 'Outside Payment'
                )
            ))
        ;

        $this->buildRetirementForm($builder, $riaCompanyInfo, $client);

        $this->buildModelForm($builder, $ria);

        $this->buildBillingSpecForm($builder, $client);

        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event) use ($em, $client) {

            $form = $event->getForm();

            // Find all outside retirement accounts that's does not have a preferred fund
            $q = $em->getRepository('WealthbotClientBundle:ClientAccount')
                ->createQueryBuilder('ca')
                ->leftJoin('ca.groupType', 'gt')
                ->leftJoin('gt.group', 'g')
                ->leftJoin('ca.accountOutsideFunds', 'aof')
                ->leftJoin('aof.securityAssignment', 's')
                ->where('aof.is_preferred = 0 OR aof.id IS NULL')
                ->andWhere('g.name = :retirement_group')
                ->andWhere('ca.client_id = :client_id')
                ->andWhere('s.subclass IS NOT NULL')
                ->setParameter('client_id', $client->getId())
                ->setParameters(array(
                    'client_id' => $client->getId(),
                    'retirement_group' => AccountGroup::GROUP_EMPLOYER_RETIREMENT
                ))
                ->getQuery();
            $preferredAccounts = $q->execute();

            // If this accounts exist then Do not let an advisor proceed in portfolio suggestion.
            if($preferredAccounts){
                $form->addError(new FormError('You should have assigning at least 1 preferred fund within every current retirement plan.'));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\Profile'
        ));
    }

    public function getName()
    {
        return 'suggested_portfolio_form';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param User $client
     */
    protected function buildBillingSpecForm(FormBuilderInterface $builder, $client)
    {
        $builder
            ->add('billingSpec', 'entity', array(
                    'class' => 'WealthbotAdminBundle:BillingSpec',
                    'property' => 'name',
                    'property_path' => 'user.appointedBillingSpec',
                    'multiple' => false,
                    'query_builder' => function (EntityRepository $er) use ($client) {
                        return $er->createQueryBuilder('b')
                            ->where('b.owner = :ria')
                            ->setParameter('ria', $client->getRia());
                    }
                ));
    }

    protected function buildCapitalEnginesForm(FormBuilderInterface $builder, $parent, $ria)
    {
        $builder
            ->add('portfolio', 'entity', array(
                'class' => 'WealthbotAdminBundle:CeModel',
                'property' => 'name',
                'property_path' => false,
                'query_builder' => function (EntityRepository $er) use ($parent, $ria) {
                    return $er->createQueryBuilder('p')
                        ->where('p.parent = :parent')
                        ->andWhere('p.ownerId = :owner_id')
                        ->setParameters(array(
                            'parent' => $parent->getParent(),
                            'owner_id' => $ria->getId()
                        ));
                },
                'data' => $parent
            ))
            ->add('selected_model', 'entity', array(
                'class' => 'WealthbotAdminBundle:CeModel',
                'property' => 'name',
                'expanded' => true,
                'multiple' => false,
                'property_path' => 'suggested_portfolio',
                'query_builder' => function (EntityRepository $er) use ($parent, $ria) {
                    return $er->createQueryBuilder('p')
                        ->where('p.parent = :parent')
                        ->andWhere('p.ownerId = :owner_id')
                        ->setParameters(array(
                            'parent' => $parent,
                            'owner_id' => $ria->getId()
                        ));
                }
            ))
        ;

        $factory = $builder->getFormFactory();

        $updateSelectedModel = function(FormInterface $form, $portfolio, $ria) use ($factory) {
            $form->add($factory->createNamed('selected_model', 'entity', null, array(
                    'class' => 'WealthbotAdminBundle:CeModel',
                    'property' => 'name',
                    'expanded' => true,
                    'multiple' => false,
                    'property_path' => 'suggested_portfolio',
                    'query_builder' => function (EntityRepository $er) use ($portfolio, $ria) {
                        return $er->createQueryBuilder('p')
                            ->where('p.parent = :parent')
                            ->andWhere('p.ownerId = :owner_id')
                            ->setParameters(array(
                                'parent' => $portfolio,
                                'owner_id' => $ria->getId()
                            ));
                    }
                ))
            );
        };

        $builder->addEventListener(
            FormEvents::PRE_BIND,
            function (FormEvent $event) use ($updateSelectedModel, $ria) {
                $form = $event->getForm();
                $data = $event->getData();

                if (array_key_exists('portfolio', $data)) {
                    $updateSelectedModel($form, $data['portfolio'], $ria);
                }
            }
        );
    }

    protected function buildModelForm(FormBuilderInterface $builder, User $ria)
    {
        $builder->add('client', new ChooseClientPortfolioFormType($this->clientPortfolio), array('mapped' => false, 'data' => $this->clientPortfolio->getClient()));

        if ($ria->getRiaCompanyInformation()->isCollaborativeProcessing()) {
            $builder->add('groups', 'entity', array(
                'multiple' => false,   // Multiple selection allowed
                'property' => 'name', // Assuming that the entity has a "name" property
                'property_path' => 'user.groups',
                'label'    => 'Groups:',
                'class'    => 'Wealthbot\UserBundle\Entity\Group',
                'query_builder' => function(EntityRepository $er) use ($ria) {
                    return $er->createQueryBuilder('g')
                        ->andWhere('g.owner = :owner')
                        ->orWhere('g.owner is null')
                        ->setParameter('owner', $ria);
                }
            ));
        }
    }

    protected function buildRetirementForm(FormBuilderInterface $builder, RiaCompanyInformation $riaCompanyInfo, User $client)
    {
        $clientPortfolios = $client->getClientPortfolios();

        if ($clientPortfolios->count() == 1) {
            if ($clientPortfolios[0]->isProposed()) {
                if ($riaCompanyInfo->isClientByClientManagedLevel()) {
                    $builder->add('client_account_managed', 'choice', array(
                        'choices' => Profile::$client_account_managed_choices,
                        'expanded' => false,
                        'constraints' => array(
                            new NotBlank(array(
                                'message' => 'Choose a Asset Location.'
                            ))
                        )
                    ));
                } else {
                    $builder->add('client_account_managed', 'hidden', array(
                        'data' => $riaCompanyInfo->getAccountManaged()
                    ));
                }
            } else {
                $builder->add('client_account_managed', 'choice', array(
                    'choices' => Profile::$client_account_managed_choices,
                    'expanded' => false,
                    'constraints' => array(
                        new NotBlank(array(
                            'message' => 'Choose a Asset Location.'
                        ))
                    ),
                    'attr' => array(
                        'disabled' => true
                    )
                ));
            }
        }
   }
}