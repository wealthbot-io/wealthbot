<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.11.12
 * Time: 16:57
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\AccountGroup;
use App\Entity\ClientPortfolio;
use App\Entity\RiaCompanyInformation;
use App\Entity\Profile;
use App\Entity\User;

class SuggestedPortfolioFormType extends AbstractType
{
    /** @var EntityManager $em */
    private $em;

    /** @param \App\Entity\ClientPortfolio */
    private $clientPortfolio;

    private $client;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $this->clientPortfolio = $options['clientPortfolio'];
        $client = $this->client = $options['client'];

        $em = $this->em;
        $profile = $this->client->getProfile();
        $ria = $profile->getRia();
        $riaCompanyInfo = $ria->getRiaCompanyInformation();

        if (!$profile || !$profile->getId()) {
            throw new \Exception('Profile is required.');
        }

        $builder
            ->add('action_type', HiddenType::class, [
                'mapped' => false,
                'data' => 'submit'
            ])
            ->add('unconsolidated_ids', HiddenType::class, [
                'mapped' => false,
                'attr' => ['value' => ''],
            ])
            ->add('is_qualified', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'choices' => [
                    'Direct Debit' => Profile::PAYMENT_METHOD_DIRECT_DEBIT,
                    'Outside Payment'  =>  Profile::PAYMENT_METHOD_OUTSIDE_PAYMENT ,
                ],
            ])
        ;

        $this->buildRetirementForm($builder, $riaCompanyInfo, $client);

        $this->buildModelForm($builder, $ria);

        $this->buildBillingSpecForm($builder, $client);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($em, $client) {
            $form = $event->getForm();

            // Find all outside retirement accounts that's does not have a preferred fund
            $q = $em->getRepository('App\Entity\ClientAccount')
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
                ->setParameters([
                    'client_id' => $client->getId(),
                    'retirement_group' => AccountGroup::GROUP_EMPLOYER_RETIREMENT,
                ])
                ->getQuery();
            $preferredAccounts = $q->execute();

            // If this accounts exist then Do not let an advisor proceed in portfolio suggestion.
            if ($preferredAccounts) {
                $form->addError(new FormError('You should have assigning at least 1 preferred fund within every current retirement plan.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
         //   'data_class' => 'App\Entity\ClientPortfolio',
            'em' => null,
            'clientPortfolio' => null,
            'client' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'suggested_portfolio_form';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param User                 $client
     */
    protected function buildBillingSpecForm(FormBuilderInterface $builder, $client)
    {
        $builder
            ->add('billingSpec', EntityType::class, [
                    'class' => 'App\\Entity\\BillingSpec',
                    'property_path' => 'user.appointedBillingSpec',
                    'multiple' => false,
                    'query_builder' => function (EntityRepository $er) use ($client) {
                        return $er->createQueryBuilder('b')
                            ->where('b.owner = :ria')
                            ->setParameter('ria', $client->getRia());
                    },
                ]);
    }

    protected function buildCapitalEnginesForm(FormBuilderInterface $builder, $parent, $ria)
    {
        $builder
            ->add('portfolio', EntityType::class, [
                'class' => 'App\\Entity\\CeModel',
                'property_path' => 'name',
                'mapped' => false,
                'query_builder' => function (EntityRepository $er) use ($parent, $ria) {
                    return $er->createQueryBuilder('p')
                        ->where('p.parent = :parent')
                        ->andWhere('p.ownerId = :owner_id')
                        ->setParameters([
                            'parent' => $parent->getParent(),
                            'owner_id' => $ria->getId(),
                        ]);
                },
                'data' => $parent,
            ])
            ->add('selected_model', EntityType::class, [
                'class' => 'App\\Entity\\CeModel',
                'expanded' => true,
                'multiple' => false,
                'property_path' => 'suggested_portfolio',
                'query_builder' => function (EntityRepository $er) use ($parent, $ria) {
                    return $er->createQueryBuilder('p')
                        ->where('p.parent = :parent')
                        ->andWhere('p.ownerId = :owner_id')
                        ->setParameters([
                            'parent' => $parent,
                            'owner_id' => $ria->getId(),
                        ]);
                },
            ])
        ;

        $factory = $builder->getFormFactory();

        $updateSelectedModel = function (FormInterface $form, $portfolio, $ria) use ($factory) {
            $form->add(
                $factory->createNamed('selected_model', EntityType::class, null, [
                    'class' => 'App\\Entity\\CeModel',
                    'expanded' => true,
                    'multiple' => false,
                    'auto_initialize' => false,
                    'property_path' => 'suggested_portfolio',
                    'query_builder' => function (EntityRepository $er) use ($portfolio, $ria) {
                        return $er->createQueryBuilder('p')
                            ->where('p.parent = :parent')
                            ->andWhere('p.ownerId = :owner_id')
                            ->setParameters([
                                'parent' => $portfolio,
                                'owner_id' => $ria->getId(),
                            ]);
                    },
                ])
            );
        };

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
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
        $builder->add('client', ChooseClientPortfolioFormType::class, ['mapped' => false, 'data' => $this->clientPortfolio->getClient()]);

        if ($ria->getRiaCompanyInformation()->isCollaborativeProcessing()) {
            $builder->add('groups', EntityType::class, [
                'multiple' => false,   // Multiple selection allowed
                'property_path' => 'user.groups',
                'label' => 'Groups:',
                'class' => 'App\Entity\Group',
                'query_builder' => function (EntityRepository $er) use ($ria) {
                    return $er->createQueryBuilder('g')
                        ->andWhere('g.owner = :owner')
                        ->orWhere('g.owner is null')
                        ->setParameter('owner', $ria);
                },
            ]);
        }
    }

    protected function buildRetirementForm(FormBuilderInterface $builder, RiaCompanyInformation $riaCompanyInfo, User $client)
    {
        $clientPortfolios = $client->getClientPortfolios();

        if (1 === $clientPortfolios->count()) {
            if ($clientPortfolios[0]->isProposed()) {
                if ($riaCompanyInfo->isClientByClientManagedLevel()) {
                    $builder->add('client_account_managed', ChoiceType::class, [
                        'choices' => Profile::$client_account_managed_choices,
                        'expanded' => false,
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Choose a Asset Location.',
                            ]),
                        ],
                    ]);
                } else {
                    $builder->add('client_account_managed', HiddenType::class, [
                        'data' => $riaCompanyInfo->getAccountManaged(),
                    ]);
                }
            } else {
                $builder->add('client_account_managed', ChoiceType::class, [
                    'choices' => Profile::$client_account_managed_choices,
                    'expanded' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Choose a Asset Location.',
                        ]),
                    ],
                    'attr' => [
                        'disabled' => true,
                    ],
                ]);
            }
        }
    }
}
