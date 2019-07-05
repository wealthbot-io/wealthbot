<?php

namespace App\Form\Handler;

use App\Entity\CeModel;
use App\Entity\Subclass;
use App\Form\Handler\AbstractFormHandler;
use Manager\CeModelManager;
use App\Model\CeModelInterface;
use App\Entity\RiaCompanyInformation;

class RiaCompanyInformationThreeFormHandler extends AbstractFormHandler
{
    public function success()
    {
        $user = $this->getOption('user');
        /** @var $ceModelManager CeModelManager */
        $ceModelManager = $this->getOption('model_manager');

        /** @var $data RiaCompanyInformation */
        $data = $this->form->getData();

        $modelType = $this->form->get('model_type')->getData();

        switch ($modelType) {
            case CeModel::TYPE_CUSTOM:
                $parentModel = $ceModelManager->createCustomModel($user);
                $this->em->persist($parentModel);
                break;

            case CeModel::TYPE_STRATEGY:
                $strategyParentModelId = $this->form->get('strategy_model')->getData();

                /** @var $strategyParentModel CeModel */
                $strategyParentModel = $this->em->getRepository('App\Entity\CeModel')->find($strategyParentModelId);

                $parentModel = $ceModelManager->copyForOwner($strategyParentModel, $user);

                $this->buildSubclassesForModel($this->form->get('subclasses')->getData(), $parentModel);
                $this->em->persist($parentModel);
                break;
        }

        if (!$this->request->isXmlHttpRequest()) {
            $profile = $user->getProfile();
            $profile->setRegistrationStep(4);
            $this->em->persist($profile);
        }

        $data->setPortfolioModel($parentModel);

        $this->em->persist($data);

        $this->em->flush();
    }

    private function buildSubclassesForModel($formSubclasses, CeModelInterface $model)
    {
        $subclasses = [];

        foreach ($model->getChildren() as $childModel) {
            foreach ($childModel->getModelEntities() as $modelEntity) {
                /** @var $subclass Subclass */
                $subclass = $modelEntity->getSubclass();

                if (!isset($subclasses[$subclass->getSource()->getId()])) {
                    foreach ($formSubclasses as $formSubclass) {
                        if ($formSubclass->getSource()->getId() === $subclass->getSource()->getId()) {
                            $subclass->setExpectedPerformance($formSubclass->getExpectedPerformance());
                            $subclass->setAccountType($formSubclass->getAccountType());

                            break;
                        }
                    }

                    $subclasses[$subclass->getSource()->getId()] = $subclass;
                }
            }
        }

        foreach ($formSubclasses as $formSubclass) {
            $sourceId = $formSubclass->getSource()->getId();

            if (!isset($subclasses[$sourceId])) {
                $formAsset = $formSubclass->getAssetClass();
                $identicalSourceId = null;

                foreach ($formSubclasses as $tmpFormSubclass) {
                    $tmpFormAsset = $tmpFormSubclass->getAssetClass();
                    $tmpSourceId = $tmpFormSubclass->getSource()->getId();

                    if (isset($subclasses[$tmpSourceId]) && $formAsset === $tmpFormAsset) {
                        $identicalSourceId = $tmpSourceId;
                        break;
                    }
                }

                if ($identicalSourceId) {
                    $asset = $subclasses[$identicalSourceId]->getAssetClass();
                    $formSubclass->setAssetClass($asset);

                    $this->em->persist($formSubclass);
                } else {
                    $oldAsset = $formSubclass->getAssetClass();
                    $newAsset = $oldAsset->getCopy();

                    $oldAsset->removeSubclasse($formSubclass);
                    $newAsset->addSubclasse($formSubclass);
                    $formSubclass->setAssetClass($newAsset);

                    $subclasses[$sourceId] = $formSubclass;
                }
            }
        }
    }
}
