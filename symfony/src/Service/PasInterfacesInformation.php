<?php

namespace App\Service;

use App\PasInterfaces\DataInterface;

/**
 * Class PasFilesInformation.
 *
 * Get information about selected type PAS files.
 */
class PasInterfacesInformation
{
    /**
     * @var DataInterface[]
     */
    protected $loaderByType;

    /**
     * Loaders are created by compiler pass from tagged services
     * with tag "wealthbot_admin.pas_files_loader".
     *
     * @param DataInterface[] $loaders
     */
    public function __construct($loaders)
    {
        $this->loaderByType = [];

        foreach ($loaders as $loader) {
            $acceptedType = $loader->getFileType();
            if (array_key_exists($acceptedType, $this->loaderByType)) {
                throw new \Exception('There is two DataInterface loaders for one type. You have to leave only one (you can turn off this in services.yml).');
            }
            if (!$loader instanceof DataInterface) {
                throw new \Exception('Data loader must implement DataInterface.');
            }
            $this->loaderByType[$loader->getFileType()] = $loader;
        }
    }

    /**
     * Load data about files with needed type on date.
     *
     * @param $fileType
     * @param \DateTime $date
     *
     * @throws \Exception
     */
    public function loadInformation($fileType, \DateTime $date, $page = 0)
    {
        if (!array_key_exists($fileType, $this->loaderByType)) {
            throw new \Exception('No handler for '.$fileType.'. Check tagged service.');
        }

        $loader = $this->loaderByType[$fileType];

        return $loader->load($date, $page);
    }

    /**
     * Load data on date about files by selected array of types.
     * Returns array with keys by type ("type_" + type of file), for example, "type_pos".
     *
     * @param $fileTypes
     * @param \DateTime $date
     *
     * @return array
     */
    public function loadAllInformation($fileTypes, \DateTime $date)
    {
        $params = [];

        foreach ($fileTypes as $fileType) {
            $params['type_'.$fileType] = $this->loadInformation($fileType, $date);
        }

        return $params;
    }
}
