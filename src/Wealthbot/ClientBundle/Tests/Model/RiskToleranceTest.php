<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.06.13
 * Time: 13:13
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Tests\Model;

use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\ClientBundle\Entity\ClientQuestionnaireAnswer;
use Wealthbot\ClientBundle\Model\RiskTolerance;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\RiaBundle\Entity\RiskAnswer;
use Wealthbot\RiaBundle\Entity\RiskQuestion;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Entity\User;

class RiskToleranceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  RiskTolerance $riskTolerance */
    private $riskTolerance;

    public function setUp()
    {
        $questions = array();
        for ($i = 0; $i < 4; $i++) {
            $question = new RiskQuestion();
            $question->setTitle('Question ' . ($i + 1));

            for ($j = 0; $j < 4; $j++) {
                $answer = new RiskAnswer();
                $answer->setTitle('Answer ' . ($i + 1) . ' - ' . ($j + 1));
                $answer->setQuestion($question);
                $answer->setPoint($j);

                $question->addAnswer($answer);
            }

            $questions[] = $question;
        }

        $userAnswers = array();
        foreach ($questions as $key => $question) {
            $userAnswer = new ClientQuestionnaireAnswer();
            $userAnswer->setQuestion($question);

            $questionAnswers = $question->getAnswers();
            $userAnswer->setAnswer($questionAnswers[$key]);

            $userAnswers[] = $userAnswer;
        }

        $portfolio = new CeModel();

        for ($i = 0; $i < 4; $i++) {
            $modelMock = $this->getMock('Wealthbot\AdminBundle\Entity\CeModel', array('getId'));
            $modelMock->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($i + 1));
            $modelMock->setName('Model ' . ($i + 1));
            $modelMock->setRiskRating(($i + 1));

            $portfolio->addChildren($modelMock);
        }

        $riaCompanyInformation = new RiaCompanyInformation();
        $riaCompanyInformation->setPortfolioModel($portfolio);

        $ria = new User();
        $ria->setRoles(array('ROLE_RIA'));
        $ria->setRiaCompanyInformation($riaCompanyInformation);

        $userProfile = new Profile();
        $userProfile->setRia($ria);

        $user = new User();
        $user->setRoles(array('ROLE_CLIENT'));
        $user->setProfile($userProfile);


        $this->riskTolerance = new RiskTolerance($user, $userAnswers);
    }

    public function testGetPoints()
    {
        $this->assertEquals(56, $this->riskTolerance->getPoints());
    }

    public function testGetSuggestedPortfolio()
    {
        $portfolio = $this->riskTolerance->getRia()->getRiaCompanyInformation()->getPortfolioModel();

        $model = $this->riskTolerance->getSuggestedPortfolio($portfolio->getChildren());
        $this->assertEquals(4, $model->getId());
    }

    public function testGetSuggestedPortfolioException()
    {
        $this->setExpectedException('Wealthbot\RiaBundle\Exception\AdvisorHasNoExistingModel');
        $this->riskTolerance->getSuggestedPortfolio(array());
    }
}