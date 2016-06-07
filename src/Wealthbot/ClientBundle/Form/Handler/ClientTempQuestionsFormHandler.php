<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.07.13
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Handler;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\ClientBundle\Document\TempPortfolio;
use Wealthbot\ClientBundle\Document\TempQuestionnaire;
use Wealthbot\ClientBundle\Manager\RiskToleranceManager;
use Wealthbot\UserBundle\Entity\User;

class ClientTempQuestionsFormHandler
{
    private $form;
    private $request;
    private $em;
    private $dm;

    public function __construct(Form $form, Request $request, EntityManager $em, DocumentManager $dm)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
        $this->dm = $dm;
    }

    public function process(User $user)
    {
        $this->form->handleRequest($this->request);

        if ($this->form->isValid()) {
            $questionsOwner = $this->em->getRepository('WealthbotRiaBundle:RiskQuestion')->getQuestionsOwner($user);

            $this->preSuccess($user);
            $this->onSuccess($user, $questionsOwner);

            return true;
        }

        return false;
    }

    private function preSuccess(User $user)
    {
        $userId = $user->getId();
        $questionnaire = $this->dm->getRepository('WealthbotClientBundle:TempQuestionnaire')->findByClientUserId($userId);
        $portfolio = $this->dm->getRepository('WealthbotClientBundle:TempPortfolio')->findOneByClientUserId($userId);

        if ($portfolio) {
            $this->dm->remove($portfolio);
        }

        foreach ($questionnaire as $item) {
            $this->dm->remove($item);
        }

        $this->dm->flush();
    }

    private function onSuccess(User $user, User $questionsOwner)
    {
        $questions = $this->em->getRepository('WealthbotRiaBundle:RiskQuestion')->getOrderedQuestionsByOwnerId(
            $questionsOwner->getId()
        );

        $answers = [];
        foreach ($questions as $question) {
            $key = 'answer_'.$question->getId();
            $data = $this->form->get($key)->getData();

            $answer = [
                'question' => $question,
            ];

            if ($question->getIsWithdrawAgeInput()) {
                $withdrawAge = (int) $answer;

                $age = $this->getClientAge($user);
                $ageDiff = $withdrawAge - (int) $age;

                $answer['data'] = $ageDiff;
            } else {
                $answer['data'] = $data;
            }

            $answers[] = $answer;

            $this->saveTempQuestionnaireItem($user->getId(), $question->getId(), $data->getId());
        }

        $riskToleranceManager = new RiskToleranceManager($user, $this->em, $answers);
        $suggestedPortfolio = $riskToleranceManager->getSuggestedPortfolio();

        $this->saveTempPortfolio($user->getId(), $suggestedPortfolio->getId());

        $this->dm->flush();
    }

    private function saveTempQuestionnaireItem($userId, $questionId, $answerId)
    {
        $tmpQuestionnaire = new TempQuestionnaire();

        $tmpQuestionnaire->setClientUserId($userId);
        $tmpQuestionnaire->setQuestionId($questionId);
        $tmpQuestionnaire->setAnswerId($answerId);

        $this->dm->persist($tmpQuestionnaire);
    }

    private function saveTempPortfolio($userId, $modelId)
    {
        $tmpPortfolio = new TempPortfolio();

        $tmpPortfolio->setClientUserId($userId);
        $tmpPortfolio->setModelId($modelId);

        $this->dm->persist($tmpPortfolio);
    }
}
