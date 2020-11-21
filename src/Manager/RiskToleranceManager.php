<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.06.13
 * Time: 19:20
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use App\Manager\CeModelManager;
use App\Entity\ClientQuestionnaireAnswer;
use App\Model\RiskTolerance;
use App\Entity\RiskAnswer;
use App\Entity\RiskQuestion;
use App\Entity\User;

class RiskToleranceManager
{
    /** @param \App\Entity\User */
    private $user;

    /** @var ObjectManager */
    private $em;

    /** @var array $userAnswers array of ClientQuestionnaireAnswer objects */
    private $userAnswers;

    /** @var \App\Model\RiskTolerance */
    private $riskTolerance;

    public function __construct(User $user, ObjectManager $em, array $answers)
    {
        $this->user = $user;
        $this->em = $em;
        $this->userAnswers = $this->createUserAnswers($answers);
        $this->riskTolerance = new RiskTolerance($user, $this->userAnswers);
    }

    /**
     * Get UserAnswers.
     *
     * @return array
     */
    public function getUserAnswers()
    {
        return $this->userAnswers;
    }

    /**
     * Save $userAnswers in db.
     */
    public function saveUserAnswers()
    {
        foreach ($this->userAnswers as $userAnswer) {
            $this->em->persist($userAnswer);
        }

        $this->em->flush();
    }

    /**
     * * Returns suggested portfolio.
     *
     * @return \App\Entity\CeModel|null
     */
    public function getSuggestedPortfolio()
    {
        $modelManager = new CeModelManager($this->em, '\App\Entity\CeModel');

        $ria = $this->riskTolerance->getRia();
        $parentModel = $ria->getRiaCompanyInformation()->getPortfolioModel();

        return $this->riskTolerance->getSuggestedPortfolio($modelManager->getChildModels($parentModel));
    }

    /**
     * Create and return array of ClientQuestionnaireAnswer objects by $answers array.
     *
     * @param array $answers
     *
     * @return array
     */
    private function createUserAnswers(array $answers)
    {
        $userAnswers = [];

        foreach ($answers as $answer) {
            /** @var RiskQuestion $question */
            $question = $answer['question'];
            $data = $answer['data'];

            if ($question->getIsWithdrawAgeInput()) {
                $data = $this->getAnswerForWithdrawAgeQuestion($question, $answer['data']);
            }

            $userAnswer = new ClientQuestionnaireAnswer();
            $userAnswer->setClient($this->user);
            $userAnswer->setQuestion($question);
            $userAnswer->setAnswer($data);

            $userAnswers[] = $userAnswer;
        }

        return $userAnswers;
    }

    /**
     * Return RiskAnswer object for withdraw age input question.
     *
     * @param RiskQuestion $question
     * @param $ageDiff
     *
     * @return RiskAnswer|null
     */
    private function getAnswerForWithdrawAgeQuestion(RiskQuestion $question, $ageDiff)
    {
        $answers = $this->em->getRepository('App\Entity\RiskAnswer')->findBy(
            [
                'risk_question_id' => $question->getId(),
            ],
            [
                'title' => 'DESC',
            ]
        );

        $result = null;

        /** @var RiskAnswer $answer */
        foreach ($answers as $answer) {
            $string = $answer->getTitle();
            $symbol = substr($string, 0, 1);
            $number = (int) substr($string, 1);

            if ('>' === $symbol) {
                if ($ageDiff >= $number) {
                    return $answer;
                }
            } else {
                if ($ageDiff <= $number) {
                    $result = $answer;
                }
            }
        }

        return $result;
    }
}
