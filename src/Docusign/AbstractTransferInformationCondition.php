<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.09.13
 * Time: 19:06
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

use App\Entity\TransferInformation;
use App\Docusign\DocusignConditionInterface;

abstract class AbstractTransferInformationCondition implements DocusignConditionInterface
{
    /**
     * Check condition.
     *
     * @param $param
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function check($param)
    {
        if (!($param instanceof TransferInformation)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "param" must be instance of %s.',
                get_class(new TransferInformation())
            ));
        }

        return $this->checkObject($param);
    }

    abstract protected function checkObject(TransferInformation $object);
}
