<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.06.13
 * Time: 12:32
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Entity\CeModel;
use App\Exception\AdvisorHasNoExistingModel;
use App\Entity\User;

class RiskTolerance
{
    /** @param \App\Entity\User */
    private $user;

    /** @var array $answers array of ClientQuestionnaireAnswer objects */
    private $userAnswers;

    /** @var int $points */
    private $points;

    public function __construct(User $user, array $userAnswers)
    {
        $this->user = $user;
        $this->userAnswers = $userAnswers;
        $this->points = null;
    }

    /**
     * Get answers points.
     *
     * @return int
     */
    public function getPoints()
    {
        if (null === $this->points) {
            $this->calculatePoints();
        }

        return $this->points;
    }

    /**
     * Returns suggested portfolio.
     *
     * @param $allowedModels
     *
     * @return CeModel
     *
     * @throws \App\Exception\AdvisorHasNoExistingModel
     */
    public function getSuggestedPortfolio($allowedModels)
    {
        $result = null;
        $models = $this->getRia()->getCeModels();
        $points = $this->getPoints();

        foreach ($allowedModels as $model) {
            $rating = $model->getRiskRating();

            if ($points === $rating) {
                return $model;
            }

            if ($points > $rating) {
                $models[] = $model;
            }
        }

        if (!empty($models)) {
            $result = $this->getClosestModelByPoints($models, $points);
        } else {
            $result = $this->getClosestModelByPoints($allowedModels, $points);
        }

        if (null === $result) {
            throw new AdvisorHasNoExistingModel();
        }

        return $result;
    }

    private function getClosestModelByPoints($allowedModels, $points)
    {
        $result = null;
        $tmpDiff = null;
        $ratingDiff = null;

        foreach ($allowedModels as $model) {
            $rating = $model->getRiskRating();

            $tmpDiff = abs($points - $rating);

            if (null === $result) {
                $ratingDiff = $tmpDiff;
                $result = $model;
            } else {
                if ($tmpDiff < $ratingDiff) {
                    $ratingDiff = $tmpDiff;
                    $result = $model;
                }
            }
        }

        return $result;
    }

    /**
     * Recalculate answers points.
     */
    private function calculatePoints()
    {
        $points = 50;

        foreach ($this->userAnswers as $userAnswer) {
            $points += $userAnswer->getAnswer()->getPoint();
        }

        $this->points = $points;
    }

    /**
     * Get ria.
     *
     * @return User
     */
    public function getRia()
    {
        if ($this->user->hasRole('ROLE_CLIENT')) {
            return $this->user->getRia();
        }

        return $this->user;
    }
}
