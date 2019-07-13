<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 22.10.12
 * Time: 12:04
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\EventListener;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use App\Entity\CeModel;
use App\Entity\CeModelEntity;
use App\Repository\SecurityAssignmentRepository;
use App\Model\User;

class PortfolioModelEntityTypeEventsListener implements EventSubscriberInterface
{
    /** @var \Symfony\Component\Form\FormFactoryInterface $factory */
    private $factory;

    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    /** @var CeModel */
    private $portfolioModel;

    /** @var \App\Model\User */
    private $user;

    private $isQualifiedModel;

    public function __construct(FormFactoryInterface $factory, \Doctrine\ORM\EntityManager $em, CeModel $portfolioModel, User $user, $isQualifiedModel = false)
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->portfolioModel = $portfolioModel;
        $this->user = $user;
        $this->isQualifiedModel = $isQualifiedModel;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preBind',
            FormEvents::SUBMIT => 'bind',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var CeModelEntity $data */
        $data = $event->getData();

        if (null === $data) {
            $this->updateMuniSubstitutionSymbol($form, null);
            $this->updateSecuritySymbol($form, null);
            $this->updateSecurity($form, null);
            $this->updateSubclass($form, null);
            $this->updateTaxLossHarvesting($form, null, []);
            $this->updateTaxLossHarvestingIdSymbol($form, null);
        } else {
            if ($data->getMuniSubstitution()) {
                $this->updateMuniSubstitutionSymbol($form, $data->getMuniSubstitutionId());
            }

            if ($data->getSecurityAssignment()) {
                $this->updateSecuritySymbol($form, $data->getSecurityAssignmentId());
                $this->updateTaxLossHarvesting($form, $data->getSubclassId(), [$data->getSecurityAssignmentId(), $data->getMuniSubstitutionId()]);
            }

            if ($data->getAssetClass()) {
                $this->updateSubclass($form, $data->getAssetClassId());
            }

            if ($data->getSubClass()) {
                $this->updateSecurity($form, $data->getSubclassId(), $data->getId());
                $this->updateMuniSubstitution($form, $data->getSubclass());
            }

            if ($data->getTaxLossHarvesting()) {
                $this->updateTaxLossHarvestingIdSymbol($form, $data->getTaxLossHarvestingId());
            }
        }
    }

    public function preBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (array_key_exists('muniSubstitution', $data)) {
            $this->updateMuniSubstitutionSymbol($form, $data['muniSubstitution']);
        }

        if (array_key_exists('tax_loss_harvesting', $data)) {
            $this->updateTaxLossHarvestingIdSymbol($form, $data['tax_loss_harvesting']);
        }

        if (array_key_exists('security', $data) && $data['security']) {
            $this->updateSecuritySymbol($form, $data['security']);

            $withoutIds = [$data['security']];
            if (array_key_exists('muniSubstitution', $data)) {
                $withoutIds[] = $data['muniSubstitution'];
            }

            $this->updateTaxLossHarvesting($form, $data['subclass'], $withoutIds);
        }

        if (array_key_exists('assetClass', $data)) {
            $this->updateSubclass($form, $data['assetClass']);
        }

        if (array_key_exists('subclass', $data)) {
            $obj = $form->getData();

            if (is_object($obj) && $obj->getId()) {
                $this->updateSecurity($form, $data['subclass'], $obj->getId());
            } else {
                $this->updateSecurity($form, $data['subclass']);
            }
            $this->updateMuniSubstitution($form, $data['subclass']);
        }
    }

    public function bind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $em = $this->em;

        if (null === $data) {
            return;
        }

        if ($data->getSubclass() && $data->getSecurityAssignment()) {
            $exist = $em->getRepository('App\Entity\CeModelEntity')->findOneBy([
                'modelId' => $this->portfolioModel->getId(),
                'assetClassId' => $data->getAssetClass()->getId(),
                'subclassId' => $data->getSubclass()->getId(),
                'securityAssignmentId' => $data->getSecurityAssignemnt()->getId(),
                'isQualified' => $this->isQualifiedModel,
            ]);
            if ($exist) {
                if ($data->getId()) {
                    if ($exist->getId() !== $data->getId()) {
                        $form->get('subclass')->addError(new FormError('You already hold this subclass in the model.'));
                    }
                } else {
                    $form->get('subclass')->addError(new FormError('You already hold this subclass in the model.'));
                }
            }
        }
    }

    protected function updateSubclass(FormInterface $form, $assetClassId)
    {
        $owner = $this->user;

        $form->add($this->factory->createNamed('subclass', EntityType::class, null, [
            'class' => 'App\\Entity\\Subclass',
            'property_path' => 'name',
            'required' => false,
            'placeholder' => 'Choose Subclass',
            'auto_initialize' => false,
            'query_builder' => function (EntityRepository $er) use ($assetClassId, $owner) {
                $query = $er->createQueryBuilder('s')
                    ->where('s.asset_class_id = :assetClassId')
                    ->andWhere("s.name NOT IN ('Intermediate Muni', 'Short Muni')")
                    ->setParameters(['assetClassId' => $assetClassId])
                    ->orderBy('s.id', 'ASC');

                if ($owner->hasRole('ROLE_RIA') || $owner->hasRole('ROLE_CLIENT')) {
                    $query->leftJoin('s.securityAssignments', 'sec');
                    $query->andWhere('sec.model_id IS NOT NULL');
                    $query->andWhere('s.owner_id = :owner_id');
                    $query->setParameter('owner_id', $owner->getId());
                } else {
                    $query->andWhere('s.owner_id IS NULL AND s.source_id IS NULL');
                }

                return $query;
            },
            'attr' => is_null($assetClassId) ? ['disabled' => 'disabled'] : [],
        ]));
    }

    protected function updateMuniSubstitution(FormInterface $form, $subclass)
    {
        if (!($subclass instanceof \App\Entity\Subclass)) {
            $subclass = $this->em->getRepository('App\Entity\Subclass')->find($subclass);
        }

        if ($this->user->hasRole('ROLE_ADMIN') || $this->user->hasRole('ROLE_SUPER_ADMIN') ||
            ($this->user->hasRole('ROLE_RIA') && $this->user->getRiaCompanyInformation()->getUseMunicipalBond())
        ) {
            $model = $this->portfolioModel;
            $selectedModel = $model->getParent();

            //TODO need move to the repository
            $qb = $this->em->getRepository('App\Entity\SecurityAssignment')->createQueryBuilder('s');

            if ($this->user->hasRole('ROLE_RIA') || $this->user->hasRole('ROLE_SUPER_ADMIN')) {
                $qb->where('s.ria_user_id IS NULL');
            } else {
                $qb->where('s.ria_user_id = :ria_user_id')->setParameter('ria_user_id', $this->user->getId());
            }

            $qb->andWhere('s.model_id = :model_id')
                ->andWhere('s.subclass_id = :subclass_id')
                ->andWhere('s.muni_substitution = 1')
                ->setParameter('model_id', $selectedModel->getId())
                ->setParameter('subclass_id', $subclass->getId())
                ->setMaxResults(1)
            ;

            $existMuniSubstitution = $qb->getQuery()->getOneOrNullResult();

            if ($existMuniSubstitution) {
                $form->add($this->factory->createNamed('muniSubstitution', EntityType::class, null, [
                    'class' => 'App\\Entity\\SecurityAssignment',
                    'property_path' => 'security.name',
                    'auto_initialize' => false,
                    'required' => false,
                    'placeholder' => 'Choose Muni Substitution',
                    'query_builder' => function (EntityRepository $er) use ($selectedModel, $subclass) {
                        $query = $er->createQueryBuilder('s')
                            ->where('s.model_id = :model_id')
                            ->andWhere('s.muni_substitution = 1')
                            ->andWhere('s.subclass_id = :subclass_id')
                            ->setParameter('model_id', $selectedModel->getId())
                            ->setParameter('subclass_id', $subclass->getId())
                        ;

                        return $query;
                    },
                ]));
            }
        }
    }

    protected function updateSecurity(FormInterface $form, $subclassId, $currentEntityId = null)
    {
        $entities = $this->portfolioModel->getModelEntities();

        $securitiesIds = [];
        //TODO discuss with Andrey about this code
        if ($currentEntityId) {
            foreach ($entities as $entity) {
                if (!$currentEntityId || $currentEntityId !== $entity->getId()) {
                    $securitiesIds[] = $entity->getSecurityAssignmentId();
                }
            }
        }

        $form->add($this->factory->createNamed('security', EntityType::class, null, [
            'class' => 'App\\Entity\\SecurityAssignment',
            'property_path' => 'security.name',
            'auto_initialize' => false,
            'placeholder' => 'Choose Security',
            'query_builder' => function (EntityRepository $er) use ($subclassId, $securitiesIds) {
                $qb = $er->createQueryBuilder('s');
                $qb->where('s.subclass_id = :subclassId AND s.ria_user_id IS NULL');
                $qb->andWhere('s.muni_substitution = 0');

                if (!empty($securitiesIds)) {
                    $qb->andWhere($qb->expr()->notIn('s.id', $securitiesIds));
                }

                $qb->setParameter('subclassId', $subclassId)
                    ->orderBy('s.id', 'ASC');

                return $qb;
            },
            'attr' => is_null($subclassId) ? ['disabled' => 'disabled'] : [],
        ]));
    }

    protected function updateSecuritySymbol(FormInterface $form, $securityAssignmentId)
    {
        if ($securityAssignmentId) {
            /** @var $obj \App\Entity\SecurityAssignment */
            $obj = $this->em->getRepository('App\Entity\SecurityAssignment')->find($securityAssignmentId);
            $value = $obj->getSecurity()->getSymbol();
        } else {
            $value = '';
        }

        $form->add($this->factory->createNamed('symbol', TextType::class, null, [
            'mapped' => false,
            'required' => false,
            'auto_initialize' => false,
            'attr' => [
                'readonly' => 'readonly',
                'value' => $value,
            ],
        ]));
    }

    protected function updateMuniSubstitutionSymbol(FormInterface $form, $muniSubstitutionId)
    {
        $value = '';

        if ($muniSubstitutionId) {
            /** @var $obj \Entity\SecurityAssignment */
            $obj = $this->em->getRepository('App\Entity\SecurityAssignment')->find($muniSubstitutionId);
            $value = $obj->getSecurity()->getSymbol();
        }

        $form->add($this->factory->createNamed('muni_substitution_symbol', TextType::class, null, [
            'mapped' => false,
            'auto_initialize' => false,
            'required' => false,
            'attr' => [
                'readonly' => 'readonly',
                'value' => $value,
            ],
        ]));
    }

    protected function updateTaxLossHarvestingIdSymbol(FormInterface $form, $taxLossHarvestingId)
    {
        $value = '';

        if ($taxLossHarvestingId) {
            /** @var $obj \Entity\SecurityAssignment */
            $obj = $this->em->getRepository('App\Entity\SecurityAssignment')->find($taxLossHarvestingId);
            $value = $obj->getSecurity()->getSymbol();
        }

        $form->add($this->factory->createNamed('tax_loss_harvesting_symbol', TextType::class, null, [
            'mapped' => false,
            'required' => false,
            'auto_initialize' => false,
            'attr' => [
                'readonly' => 'readonly',
                'value' => $value,
            ],
        ]));
    }

    protected function updateTaxLossHarvesting(FormInterface $form, $subclassId, $withoutIds = [])
    {
        if ($this->user->hasRole('ROLE_RIA') && $this->user->getRiaCompanyInformation()->getIsTaxLossHarvesting()) {
            /** @var $securityAssignmentRepo SecurityAssignmentRepository */
            $securityAssignmentRepo = $this->em->getRepository('App\Entity\SecurityAssignment');
            $securityQueryBuilder = $securityAssignmentRepo->getSecuritiesQBBySubclassIdAndWithoutSecuritiesIds($subclassId, $withoutIds);

            $form->add($this->factory->createNamed('tax_loss_harvesting', EntityType::class, null, [
                'class' => 'App\\Entity\\SecurityAssignment',
                'property_path' => 'security.name',
                'auto_initialize' => false,
                'placeholder' => 'Choose TLH Substitution',
                'query_builder' => $securityQueryBuilder,
                'attr' => empty($withoutIds) ? ['disabled' => 'disabled'] : [],
                'required' => ($securityQueryBuilder->getQuery()->getResult() ? true : false),
            ]));
        }
    }
}
