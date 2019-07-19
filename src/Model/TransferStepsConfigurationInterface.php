<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 25.09.13
 * Time: 12:51
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

interface TransferStepsConfigurationInterface
{
    /**
     * Get next transfer screen step by current step.
     *
     * @param string $currentStep
     *
     * @return string
     */
    public function getNextStep($currentStep);

    /**
     * Get previous  transfer screen step by current step.
     *
     * @param string $currentStep
     *
     * @return string
     */
    public function getPreviousStep($currentStep);
}
