<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 09.04.13
 * Time: 14:35
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Collection\AssetCollection;

class CategoriesFormType extends AbstractType
{
    private $user;

    private $em;

    private $ria;

    private $assets;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->assets = $options['assets'];
        $this->em = $options['em'];
        $this->ria = $options['ria'];
        $this->user = $options['user'];
        $em = $this->em;
        $user = $this->user;
        $factory = $builder->getFormFactory();

        $refreshAssets = function (FormInterface $form, $allSubclasses) use ($factory, $user, $em) {
            $form->add($factory->createNamed('assets', CollectionType::class, null, [
                'entry_type' => $factory->create(AssetClassWithSubclassesFormType::class, null, ['user'=>$this->user, 'em'=>$this->em, 'allSubclasses'=>$this->assets]),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'auto_initialize' => false,
            ]));
        };

        $validateUniqueName = function (FormInterface $form, $originalObject, Collection $collection, array &$indexes) {
            $errorMessage = 'This value is already used.';

            $exists = $collection->filter(function ($item) use ($originalObject) {
                return $item->getName() === $originalObject->getName();
            });

            if (1 < $exists->count()) {
                while ($exists->next()) {
                    $key = $collection->indexOf($exists->current());

                    if (!in_array($key, $indexes)) {
                        $form->get($key)->get('name')->addError(new FormError($errorMessage));
                        $indexes[] = $key;
                    }
                }
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshAssets) {
            $form = $event->getForm();
            $data = $event->getData();
            if (null === $data) {
                return;
            }

            if ($data instanceof AssetCollection) {
                $refreshAssets($form, $data);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($refreshAssets) {
            $form = $event->getForm();
            $data = $event->getData();

            if (null === $data || !isset($data['assets'])) {
                return;
            }

            $allSubclasses = [];
            $assets = $data['assets'];
            foreach ($assets as $asset) {
                if (isset($asset['subclasses'])) {
                    foreach ($asset['subclasses'] as $subclass) {
                        $allSubclasses[] = $subclass;
                    }
                }
            }

            $refreshAssets($form, $allSubclasses);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($validateUniqueName) {
            /** @var AssetCollection $data */
            $data = $event->getData();
            $form = $event->getForm();

            $assetIndexes = [];
            $subclassesIndexes = [];

            $assetsForm = $form->get('assets');
            $assets = $data->getAssets();
            $subclassPriorities = [];

            foreach ($assets as $assetKey => $asset) {
                // Validate unique asset class
                $validateUniqueName($assetsForm, $asset, $assets, $assetIndexes);

                /** @var ArrayCollection $subclasses */
                $subclasses = $asset->getSubclasses();
                $subclassesForm = $assetsForm->get($assetKey)->get('subclasses');

                foreach ($subclasses as $subclassKey => $subclass) {
                    $subclassField = $subclassesForm->get($subclassKey);

                    if ($subclass->getAccountType() && $subclass->getAccountType()->getId()) {
                        if (isset($subclassPriorities[$subclass->getAccountType()->getId()]) &&
                            in_array($subclass->getPriority(), $subclassPriorities[$subclass->getAccountType()->getId()]) &&
                            $subclassField->has('priority')) {
                            $subclassField->get('priority')->addError(new FormError('Value must be unique'));
                        }

                        $subclassPriorities[$subclass->getAccountType()->getId()][] = $subclass->getPriority();
                    }

                    // Validate unique subclass
                    $validateUniqueName($subclassesForm, $subclass, $subclasses, $subclassesIndexes);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'em' => null,
            'ria' => null,
            'assets' => null,
            'user' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'categories';
    }
}
