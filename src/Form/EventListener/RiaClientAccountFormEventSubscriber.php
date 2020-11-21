<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.05.13
 * Time: 15:15
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use App\Entity\AccountGroup;
use App\Entity\AccountGroupType;
use App\Entity\ClientAccount;
use App\Entity\ClientAdditionalContact;
use App\Form\Type\AccountTransferInformationFormType;
use App\Model\ClientAccountOwner;
use App\Form\Type\RiaClientAccountOwnerFormType;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RiaClientAccountFormEventSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $client;
    private $em;
    private $validateAdditionalFields;

    public function __construct(FormFactoryInterface $factory, User $client, EntityManager $em, $validateAdditionalFields = true)
    {
        $this->factory = $factory;
        $this->client = $client;
        $this->em = $em;
        $this->validateAdditionalFields = $validateAdditionalFields;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preBind',
            FormEvents::SUBMIT => 'bind',
        ];
    }

    /**
     * PRE_SET_DATA event handler.
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (($data instanceof ClientAccount) && $data->getId()) {
            $group = $data->getGroupName();
            $financialInstitution = $data->getFinancialInstitution();
            $groupType = $data->getGroupType();
        } else {
            $group = null;
            $financialInstitution = null;
            $groupType = null;
        }

        $this->updateFieldsByGroup($form, $group, $financialInstitution);

        if ($groupType) {
            $this->updateOwnersField($form, $groupType, $data);
        }
    }

    /**
     * PRE_SUBMIT event handler.
     *
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (array_key_exists('group', $data)) {
            $this->updateFieldsByGroup($form, $data['group']);
        }

        if (array_key_exists('groupType', $data)) {
            if ($data['groupType'] instanceof AccountGroupType) {
                $groupType = $data['groupType'];
            } else {
                $groupType = $this->em->getRepository('App\Entity\AccountGroupType')->find($data['groupType']);
            }

            if ($groupType) {
                $this->updateOwnersField($form, $groupType);
            }
        }
    }

    /**
     * BIND event handler.
     *
     * @param FormEvent $event
     */
    public function bind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $this->validate($form, $data);
    }

    /**
     * Update form fields by group.
     *
     * @param FormInterface $form
     * @param $group
     * @param null $financialInstitution
     */
    private function updateFieldsByGroup(FormInterface $form, $group, $financialInstitution = null)
    {
        switch ($group) {
            case AccountGroup::GROUP_DEPOSIT_MONEY:
                if ($form->has('financial_institution')) {
                    $form->remove('financial_institution');
                }
                break;

            case AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT:
                $form->add(
                    $this->factory->createNamed('financial_institution', TextType::class, null, [
                        'label' => 'Former Employer',
                    ])
                );
                break;

            case AccountGroup::GROUP_EMPLOYER_RETIREMENT:
                if ($financialInstitution) {
                    $employerFinancialInstitution = explode('(', $financialInstitution);
                    $provider = trim($employerFinancialInstitution[0]);
                    $company = trim($employerFinancialInstitution[1], ' )');
                } else {
                    $provider = null;
                    $company = null;
                }

                $form->add(
                    $this->factory->createNamed('financial_institution', TextType::class, null, [
                        'label' => 'Employer Name',
                        'data' => $company,
                    ])
                )->add(
                    $this->factory->createNamed('plan_provider', TextType::class, null, [
                            'label' => 'Retirement Plan Provide',
                            'mapped' => false,
                            'data' => $provider,
                    ])
                );
                break;

            case AccountGroup::GROUP_FINANCIAL_INSTITUTION:
                //$group = AccountGroup::GROUP_FINANCIAL_INSTITUTION;

                $form->add(
                    $this->factory->createNamed('financial_institution', TextType::class, null, [
                        'label' => 'Financial Institution',
                    ])
                )->add(
                    $this->factory->createNamed(
                        'transferInformation',
                        AccountTransferInformationFormType::class,
                        null,
                        ['label' => ' ']
                    )
                );
                break;

            default:
                $group = AccountGroup::GROUP_DEPOSIT_MONEY;
                if ($form->has('financial_institution')) {
                    $form->remove('financial_institution');
                }
                break;
        }

        $this->updateGroupTypeField($form, $group);
    }

    /**
     * Update groupType field by group.
     *
     * @param FormInterface $form
     * @param $group
     */
    private function updateGroupTypeField(FormInterface $form, $group)
    {
        $form->add(
            $this->factory->createNamed('groupType', EntityType::class, null, [
                'class' => 'App\\Entity\\AccountGroupType',
                'query_builder' => function (EntityRepository $er) use ($group) {
                    $qb = $er->createQueryBuilder('gt');
                    $qb->leftJoin('gt.group', 'g')
                        ->leftJoin('gt.type', 't')
                        ->where('g.name = :group')
                        ->setParameter('group', $group)
                        ->orderBy('t.id', 'asc');

                    return $qb;
                },
                'property_path' => 'type',
                'label' => 'Account Type',
                'placeholder' => 'Select Type',
                'auto_initialize' => false,
            ])
        );
    }

    private function updateOwnersField(FormInterface $form, AccountGroupType $groupType, ClientAccount $account = null)
    {
        $isJoint = ($groupType->getType() && 'Joint Account' === $groupType->getType()->getName());

        if ($this->client->isMarried() || $isJoint) {
            $form->add(
                $this->factory->createNamed(
                    'owners',
                    RiaClientAccountOwnerFormType::class,
                    null,
                    [
                        'mapped' => false,
                    ]
                )
            );
        }
    }

    /**
     * Validate form data.
     *
     * @param FormInterface $form
     * @param ClientAccount $data
     */
    private function validate(FormInterface $form, ClientAccount $data)
    {
        $group = $form->get('group')->getData();

        if ($data) {
            $data->setClient($this->client);

            if (AccountGroup::GROUP_EMPLOYER_RETIREMENT === $group) {
                $data->setMonthlyDistributions(null);
                $data->setSasCash(null);

                if (floatval($data->getValue()) < 50000) {
                    $form->get('value')->addError(new FormError('Minimum value must be $50,000 for retirement plans.'));
                }

                if ($form->get('plan_provider')->getData()) {
                    $financialInstitution = $data->getFinancialInstitution();
                    $data->setFinancialInstitution($form->get('plan_provider')->getData().' ('.$financialInstitution.')');
                }
            }

            if ($this->validateAdditionalFields && $form->has(('owners'))) {
                if ('Joint Account' === $data->getTypeName()) {
                    $ownerTypes = $form->get('owners')->get('owner_types')->getData();

                    if (!is_array($ownerTypes) || 2 !== count($ownerTypes)) {
                        $form->get('owners')->get('owner_types')->addError(
                            new FormError('You should select two owners of the account.')
                        );
                    }

                    if ($form->get('owners')->has('other_contact')) {
                        /** @var ClientAdditionalContact $otherContact */
                        $otherContact = $form->get('owners')->get('other_contact')->getData();

                        if (!$otherContact->getFirstName() || trim('' === $otherContact->getFirstName())) {
                            $form->get('owners')->get('other_contact')->get('first_name')->addError(
                                new FormError('Required.')
                            );
                        }
                        if (!$otherContact->getMiddleName() || trim('' === $otherContact->getMiddleName())) {
                            $form->get('owners')->get('other_contact')->get('middle_name')->addError(
                                new FormError('Required.')
                            );
                        }
                        if (!$otherContact->getLastName() || trim('' === $otherContact->getLastName())) {
                            $form->get('owners')->get('other_contact')->get('last_name')->addError(
                                new FormError('Required.')
                            );
                        }
                        if (!$otherContact->getRelationship() || trim('' === $otherContact->getRelationship())) {
                            $form->get('owners')->get('other_contact')->get('relationship')->addError(
                                new FormError('Required.')
                            );
                        }
                    }
                } elseif ($this->client->isMarried()) {
                    $ownerType = $form->get('owners')->get('owner_types')->getData();
                    $choices = ClientAccountOwner::getOwnerTypeChoices();

                    unset($choices[ClientAccountOwner::OWNER_TYPE_OTHER]);

                    if (!$ownerType || !in_array($ownerType, $choices)) {
                        $form->get('owners')->get('owner_types')->addError(
                            new FormError('Select owner of the account.')
                        );
                    }
                }
            }
        }
    }
}
