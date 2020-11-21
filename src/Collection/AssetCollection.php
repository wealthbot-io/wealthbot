<?php

namespace App\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\AssetClass;
use App\Model\CeModelInterface;

class AssetCollection
{
    private $model;

    /** @var \Doctrine\Common\Collections\ArrayCollection */
    private $assets;

    public function __construct(array $assets = [], CeModelInterface $model = null)
    {
        $this->assets = new ArrayCollection($assets);
        $this->model = $model;
    }

    /**
     * Add asset.
     *
     * @param AssetClass $asset
     *
     * @return $this
     */
    public function addAsset(AssetClass $asset)
    {
        $asset->setModel($this->model);
        if ($this->model->getId()) {
            $asset->setModelId($this->model->getId());
        }

        $this->assets[] = $asset;

        return $this;
    }

    /**
     * Remove Asset.
     *
     * @param AssetClass $asset
     */
    public function removeAsset(AssetClass $asset)
    {
        $this->assets->removeElement($asset);
    }

    /**
     * Add Assets.
     *
     * @param $assets
     *
     * @return $this
     */
    public function setAssets($assets)
    {
        foreach ($assets as $asset) {
            $asset->setModel($this->model);
            if ($this->model->getId()) {
                $asset->setModelId($this->model->getId());
            }

            $this->addAsset($asset);
        }

        return $this;
    }

    /**
     * Get all assets.
     *
     * @return ArrayCollection
     */
    public function getAssets()
    {
        return $this->assets;
    }
}
