<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 08.07.13
 * Time: 13:41
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Handler;

use Wealthbot\AdminBundle\Form\Handler\AbstractFormHandler;
use Wealthbot\UserBundle\Entity\User;

class RiaCompanyInformationTwoFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        /** @var User $ria */
        $ria = $this->getOption('ria');
        //$originalFees = $this->getOption('original_fees');

        if (!$ria || !($ria instanceof User)) {
            throw new \InvalidArgumentException(sprintf(
                'Option "ria" must be object instance of %s.',
                'Wealthbot\UserBundle\Entity\User'
            ));
        }

        $companyInformation = $this->form->getData();

        $this->processFees($ria);

        $this->em->persist($companyInformation);
        $this->em->flush();
    }

    private function getOriginalFees(User $ria)
    {
        $originalFees = [];
        foreach ($ria->getFees() as $fee) {
            $originalFees[] = $fee;
        }

        return $originalFees;
    }

    private function processFees(User $ria)
    {
        $originalFees = $this->getOriginalFees($ria);
        $fees = $this->form->get('fees')->getData();

        foreach ($fees as $fee) {
            $fee->setOwner($ria);
            $this->em->persist($fee);

            foreach ($originalFees as $key => $toDel) {
                if ($fee->getId() === $toDel->getId()) {
                    unset($originalFees[$key]);
                }
            }
        }

        foreach ($originalFees as $fee) {
            $this->em->remove($fee);
        }

        $this->em->flush();
    }
}
