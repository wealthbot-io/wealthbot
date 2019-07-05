<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 16.07.13
 * Time: 16:40
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

class CategoriesFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $originalAssets = $this->getOption('original_assets');
        $originalSubclasses = $this->getOption('original_subclasses');

        if (!is_array($originalAssets) || !is_array($originalSubclasses)) {
            throw new \InvalidArgumentException(sprintf(
                'Options %s and %s must be array.',
                'original_assets',
                'original_subclasses'
            ));
        }

        $data = $this->form->getData();

        $toDeleteAssets = [];
        foreach ($originalAssets as $originalAsset) {
            $toDeleteAssets[$originalAsset->getId()] = $originalAsset;
        }

        foreach ($data->getAssets() as $asset) {
            if (isset($toDeleteAssets[$asset->getId()])) {
                unset($toDeleteAssets[$asset->getId()]);
            }

            // filter $originalSubclasses to contain subclasses no longer present
            foreach ($asset->getSubclasses() as $subclass) {
                if (isset($originalSubclasses[$asset->getId()]) && is_array($originalSubclasses[$asset->getId()])) {
                    foreach ($originalSubclasses[$asset->getId()] as $toDelSubclass) {
                        if ($toDelSubclass->getId() === $subclass->getId()) {
                            unset($originalSubclasses[$asset->getId()][$subclass->getId()]);
                        } else {
                            $asset->addSubclasse($subclass);
                        }
                    }
                }
            }

            // remove the relationship between the Subclass and the Asset
            if (isset($originalSubclasses[$asset->getId()])) {
                foreach ($originalSubclasses[$asset->getId()] as $subclass) {
                    $this->em->remove($subclass);
                }
            }

            $this->em->persist($asset);
        }
        $this->em->flush();
        $this->em->clear();

        foreach ($toDeleteAssets as $asset) {
            $this->em->remove($asset);
        }

        $this->em->flush();
    }
}
