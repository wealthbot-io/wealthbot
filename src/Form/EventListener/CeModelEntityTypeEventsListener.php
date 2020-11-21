<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 22.10.12
 * Time: 12:04
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\EventListener;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use App\Entity\CeModel;
use App\Entity\CeModelEntity;
use App\Entity\SecurityAssignment;
use App\Entity\Subclass;
use App\Repository\CeModelEntityRepository;
use App\Repository\SecurityAssignmentRepository;
use App\Entity\User;

class CeModelEntityTypeEventsListener implements EventSubscriberInterface
{
    /** @var $factory FormFactoryInterface */
    private $factory;

    /** @var EntityManager $em */
    private $em;

    /** @var CeModel */
    private $ceModel;

    /** @var User */
    private $user;

    private $isQualifiedModel;

    public function __construct(FormFactoryInterface $factory, EntityManager $em, CeModel $ceModel, User $user, $isQualifiedModel = false)
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->ceModel = $ceModel;
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
        /** @var $data CeModelEntity */
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
                $this->updateSecurity($form, $data->getSubclassId());
                $this->updateMuniSubstitution($form, $data->getSubclass());
            }

            if ($data->getTaxLossHarvesting()) {
                $this->updateTaxLossHarvestingIdSymbol($form, $data->getTaxLossHarvestingId());
            } else {
                $this->updateTaxLossHarvestingIdSymbol($form, null);
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

        //update security symbol
        $securityAssignment = array_key_exists('securityAssignment', $data) ? $data['securityAssignment'] : null;
        $this->updateSecuritySymbol($form, $securityAssignment);

        $withoutIds = [$securityAssignment];
        if (array_key_exists('muniSubstitution', $data)) {
            $withoutIds[] = $data['muniSubstitution'];
        }

        $this->updateTaxLossHarvesting($form, isset($data['subclass']) ? $data['subclass'] : null, $withoutIds);

        // end update security symbol

        if (array_key_exists('assetClass', $data)) {
            $this->updateSubclass($form, $data['assetClass']);
        }

        if (array_key_exists('subclass', $data)) {
            //$obj = $form->getData();

            //if (is_object($obj) && $obj->getId()) {
            $this->updateSecurity($form, $data['subclass']);
//            } else {
//                $this->updateSecurity($form, $data['subclass']);
//            }
            $this->updateMuniSubstitution($form, $data['subclass']);
        }
    }

    public function bind(FormEvent $event)
    {
        /** @var $data CeModelEntity */
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        $em = $this->em;
        $form = $event->getForm();

        /** @var $ceModelEntityRepo CeModelEntityRepository */
        $ceModelEntityRepo = $em->getRepository('App\Entity\CeModelEntity');

        if ($data->getSubclass()) {
            $exist = $ceModelEntityRepo->isExistSameSubclassesForModel(
                $this->ceModel->getId(),
                $data->getSubclass()->getId(),
                $this->isQualifiedModel,
                $data->getId()
            );

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

        //TODO: Andrey
        $stock = 0;
        $bond = 0;

        $modelEntities = $ceModelEntityRepo->findBy([
            'modelId' => $this->ceModel->getId(),
            'isQualified' => $this->isQualifiedModel,
        ]);

        foreach ($modelEntities as $entity) {
            if (!$data->getId() || ($data->getId() !== $entity->getId())) {
                if ('Stocks' === $entity->getAssetClass()->getType()) {
                    $stock += $entity->getPercent();
                }

                if ('Bonds' === $entity->getAssetClass()->getType()) {
                    $bond += $entity->getPercent();
                }
            }
        }

        $overAll = $stock + $bond + $data->getPercent();

        if (($overAll) > 100) {
            $form->get('percent')->addError(new FormError('Sum of the percents must be equal 100.'));
        }
    }

    protected function updateSubclass(FormInterface $form, $assetClassId)
    {
        $queryBuilder = $this->em->getRepository('App\Entity\Subclass')->getAvailableSubclassesQuery($assetClassId, $this->user);

        $form->add($this->factory->createNamed('subclass', EntityType::class, null, [
            'class' => 'App\\Entity\\Subclass',
            'property_path' => 'subclass',
            'auto_initialize' => false,
            'required' => false,
            'placeholder' => 'Choose Subclass',
        //    'query_builder' => $queryBuilder,
            'attr' => [],
        ]));
    }

    protected function updateMuniSubstitution(FormInterface $form, $subclass)
    {
        if (!($subclass instanceof Subclass)) {
            $subclass = $this->em->getRepository('App\Entity\Subclass')->find($subclass);
        }

        if ($this->user->hasRole('ROLE_ADMIN') || $this->user->hasRole('ROLE_SUPER_ADMIN') ||
            ($this->user->hasRole('ROLE_RIA') && $this->user->getRiaCompanyInformation()->getUseMunicipalBond())
        ) {
            /** @var SecurityAssignmentRepository $repo */
            $repo = $this->em->getRepository('App\Entity\SecurityAssignment');
            $parentModel = $this->ceModel->getParent();

            $existMuniSubstitution = $repo->hasMuniSubstitution($parentModel, $subclass, $this->user);

            if ($existMuniSubstitution) {
                $queryBuilder = $repo->getAvailableMuniSubstitutionsQuery($parentModel->getId(), $subclass->getId());

                $form->add($this->factory->createNamed('muniSubstitution', EntityType::class, null, [
                    'class' => 'App\\Entity\\SecurityAssignment',
                    'property_path' => 'muniSubstitution',
                    'auto_initialize' => false,
                    'required' => false,
                    'placeholder' => 'Choose Muni Substitution',
                    'query_builder' => $queryBuilder,
                ]));
            }
        }
    }

    protected function updateSecurity(FormInterface $form, $subclassId, $currentEntityId = null)
    {
        $queryBuilder = $this->em->getRepository('App\Entity\SecurityAssignment')->getAvailableSecuritiesQuery($this->ceModel, $subclassId, $currentEntityId);

        $form->add($this->factory->createNamed('securityAssignment', EntityType::class, null, [
            'class' => 'App\\Entity\\SecurityAssignment',
            'property_path' => 'securityAssignment',
            'auto_initialize' => false,
            'placeholder' => 'Choose Security',
         //   'query_builder' => $queryBuilder,

        ]));
    }

    protected function updateTaxLossHarvesting(FormInterface $form, $subclassId, $withoutIds = [])
    {
        if ($this->user->hasRole('ROLE_RIA') && $this->user->getRiaCompanyInformation()->getIsTaxLossHarvesting() &&
            (!$this->user->getRiaCompanyInformation()->getIsUseQualifiedModels() || ($this->user->getRiaCompanyInformation()->getIsUseQualifiedModels() && !$this->isQualifiedModel))) {
            /** @var $securityAssignmentRepo SecurityAssignmentRepository */
            $securityAssignmentRepo = $this->em->getRepository('App\Entity\SecurityAssignment');
            $securityQueryBuilder = $securityAssignmentRepo->getSecuritiesQBBySubclassIdAndWithoutSecuritiesIds($subclassId, $withoutIds);

            $form->add($this->factory->createNamed('tax_loss_harvesting', EntityType::class, null, [
                'class' => 'App\\Entity\\SecurityAssignment',
                'property_path' => 'taxLossHarvesting',
                'auto_initialize' => false,
                'placeholder' => 'Choose TLH Substitution',
               // 'query_builder' => $securityQueryBuilder,

                'required' => false,
            ]));
        }
    }

    protected function updateSecuritySymbol(FormInterface $form, $securityId)
    {
        $this->updateSymbol($form, $securityId, 'symbol');
    }

    protected function updateMuniSubstitutionSymbol(FormInterface $form, $muniSubstitutionId)
    {
        $this->updateSymbol($form, $muniSubstitutionId, 'muni_substitution_symbol');
    }

    protected function updateTaxLossHarvestingIdSymbol(FormInterface $form, $taxLossHarvestingId)
    {
        $this->updateSymbol($form, $taxLossHarvestingId, 'tax_loss_harvesting_symbol');
    }

    protected function updateSymbol(FormInterface $form, $securityAssignmentId, $name)
    {
        $value = '';
        if ($securityAssignmentId) {
            /** @var $obj SecurityAssignment */
            $obj = $this->em->getRepository('App\Entity\SecurityAssignment')->find($securityAssignmentId);
            $value = $obj->getSecurity()->getSymbol();
        }

        $form->add($this->factory->createNamed($name, TextType::class, null, [
            'mapped' => false,
            'required' => false,
            'auto_initialize' => false,
            'attr' => [
                'readonly' => 'readonly',
                'value' => $value,
            ],
        ]));
    }
}
