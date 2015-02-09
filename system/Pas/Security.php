<?php

namespace Pas;

use Model\Pas\Security as SecurityModel;
use Model\Pas\Repository\SecurityRepository as SecurityRepo;
use Model\Pas\Repository\SecurityTypeRepository as SecurityTypeRepo;
use Model\Pas\DocumentRepository\BaseRepository as DocumentBaseRepo;
use Model\Pas\DocumentRepository\SecurityRepository as DocumentSecurityRepo;

class Security
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->securityRepo = new SecurityRepo();
        $this->securityTypeRepo = new SecurityTypeRepo();
        $this->docSecurityRepo = new DocumentSecurityRepo();
    }

    /**
     * @param array $data
     * @return id|null
     */
    public function process(array $data)
    {
        if (null == $securityType = $this->securityTypeRepo->findOneByName($data['security_type'])) {
            // Add error
            return null;
        }

        $model = new SecurityModel();
        $model->setSymbol($data['symbol']);
        $model->setName(addslashes($data['description']));
        $model->setSecurityTypeId($securityType->getId());
        return $this->securityRepo->save($model);
    }

    /**
     * @param string $startDate
     * @param string $endDate
     */
    public function run($startDate, $endDate)
    {
        $securities = $this->docSecurityRepo->getAllByDate($startDate, $endDate);
        foreach ($securities as $security) {
            $this->process($security);
        }
    }
}