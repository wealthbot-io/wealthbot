<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.11.12
 * Time: 16:57
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Entity\User;

class SuggestedPortfolioFormType extends AbstractType
{
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
        $em = $this->em;
        $profile = $builder->getData();
        $ria = $profile->getRia();
        $client = $profile->getUser();
        $riaCompanyInfo = $ria->getRiaCompanyInformation();

        if (!$profile || !$profile->getId()) {
            throw new \Exception('Profile is required.');
        }

        $builder
            ->add('action_type', 'hidden', [
                'mapped' => false,
                'attr' => ['value' => ''],
            ])
            ->add('unconsolidated_ids', 'hidden', [
                'mapped' => false,
                'attr' => ['value' => ''],
            ])
            ->add('is_qualified', 'hidden', [
                'mapped' => false,
            ])
            ->add('paymentMethod', 'choice', [
                'choices' => [
                    Profile::PAYMENT_METHOD_DIRECT_DEBIT => 'Direct Debit',
                    Profile::PAYMENT_METHOD_OUTSIDE_PAYMENT => 'Outside Payment',
                ],
            ])
        ;

        $this->buildRetirementForm($builder, $riaCompanyInfo, $client);

        $this->buildModelForm($builder, $ria);

        $this->buildBillingSpecForm($builder, $client);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($em, $client) {

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
            'data_class' => 'Wealthbot\UserBundle\Entity\Profile',
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
            ->add('billingSpec', 'entity', [
                    'class' => 'Wealthbot\\AdminBundle\\Entity\\BillingSpec',
                    'property' => 'name',
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
            ->add('portfolio', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\CeModel',
                'property' => 'name',
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
            ->add('selected_model', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\CeModel',
                'property' => 'name',
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
            $form->add($factory->createNamed('selected_model', 'entity', null, [
                    'class' => 'Wealthbot\\AdminBundle\\Entity\\CeModel',
                    'property' => 'name',
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
        $builder->add('client', new ChooseClientPortfolioFormType($this->clientPortfolio), ['mapped' => false, 'data' => $this->clientPortfolio->getClient()]);

        if ($ria->getRiaCompanyInformation()->isCollaborativeProcessing()) {
            $builder->add('groups', 'entity', [
                'multiple' => false,   // Multiple selection allowed
                'property' => 'name', // Assuming that the entity has a "name" property
                'property_path' => 'user.groups',
                'label' => 'Groups:',
                'class' => 'Wealthbot\UserBundle\Entity\Group',
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

        if ($clientPortfolios->count() === 1) {
            if ($clientPortfolios[0]->isProposed()) {
                if ($riaCompanyInfo->isClientByClientManagedLevel()) {
                    $builder->add('client_account_managed', 'choice', [
                        'choices' => Profile::$client_account_managed_choices,
                        'expanded' => false,
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Choose a Asset Location.',
                            ]),
                        ],
                    ]);
                } else {
                    $builder->add('client_account_managed', 'hidden', [
                        'data' => $riaCompanyInfo->getAccountManaged(),
                    ]);
                }
            } else {
                $builder->add('client_account_managed', 'choice', [
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
