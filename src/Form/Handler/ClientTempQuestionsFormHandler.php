<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.07.13
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use App\Manager\RiskToleranceManager;
use App\Entity\User;

class ClientTempQuestionsFormHandler
{
    private $form;
    private $request;
    private $em;
    private $dm;

    public function __construct(Form $form, Request $request, EntityManager $em, $dm)
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
            $questionsOwner = $this->em->getRepository('App\Entity\RiskQuestion')->getQuestionsOwner($user);

            $this->preSuccess($user);
            $this->onSuccess($user, $questionsOwner);

            return true;
        }

        return false;
    }

    private function preSuccess(User $user)
    {
        $userId = $user->getId();
        $questionnaire = $this->em->getRepository('App\Entity\TempQuestionnaire')->findByClientUserId($userId);
        $portfolio = $this->em->getRepository('App\Entity\TempPortfolio')->findOneByClientUserId($userId);

        if ($portfolio) {
            $this->em->remove($portfolio);
        }

        foreach ($questionnaire as $item) {
            $this->em->remove($item);
        }

        $this->em->flush();
    }

    private function onSuccess(User $user, User $questionsOwner)
    {
        $questions = $this->em->getRepository('App\Entity\RiskQuestion')->getOrderedQuestionsByOwnerId(
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

                $age = $user->getProfile()->getAge();
                $ageDiff = $withdrawAge - (int) $age;

                $answer['data'] = $ageDiff;
            } else {
                $answer['data'] = $data;
            }

            $answers[] = $answer;
        }

        $riskToleranceManager = new RiskToleranceManager($user, $this->em, $answers);

        $this->em->flush();
    }
}
