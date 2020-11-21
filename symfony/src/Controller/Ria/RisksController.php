<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.11.12
 * Time: 18:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Ria;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\CeModel;
use App\Form\Type\RiskQuestionFormType;
use App\Entity\RiskAnswer;
use App\Entity\RiskQuestion;
use App\Exception\AdvisorHasNoExistingModel;
use App\Form\Handler\RiskQuestionsFormHandler;
use App\Form\Type\RiskQuestionsFormType;

class RisksController extends Controller
{
    public function index()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var \Repository\RiskQuestionRepository $repo */
        $repo = $em->getRepository('App\Entity\RiskQuestion');

        $user = $this->getUser();
        $questions = $repo->getOrderedQuestionsByOwnerId($user->getId());

        if (!count($questions)) {
            $questions = $repo->getClonedAdminQuestions($user);
        }

        return $this->render('/Ria/Risks/index.html.twig', [
            'user' => $user,
            'questions' => $questions,
        ]);
    }

    public function createQuestion(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();
        $question = new RiskQuestion();

        // Add one empty answer row
        $answer = new RiskAnswer();
        $question->addAnswer($answer);

        $form = $this->createForm(RiskQuestionFormType::class, $question);
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $question = $form->getData();
                $question->setOwner($user);
                $em->persist($question);

                foreach ($question->getAnswers() as $answer) {
                    $answer->setQuestion($question);
                    $em->persist($answer);
                }

                $em->flush();

                return $this->json([
                    'status' => 'success',
                    'new_row' => $this->renderView('/Ria/Risks/_question_row.html.twig', ['question' => $question]),
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'form' => $this->renderView('/Ria/Risks/_question_form.html.twig', ['form' => $form->createView()]),
                ]);
            }
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Risks/_question_form.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function editQuestion(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $this->getUser();
        $question = $em->getRepository('App\Entity\RiskQuestion')->findOneBy([
            'id' => $request->get('id'),
            'owner_id' => $user->getId(),
        ]);

        if (!$question) {
            return $this->json(['status' => 'error', 'message' => 'Question does not exist.']);
        }

        $originalAnswers = [];
        foreach ($question->getAnswers() as $answer) {
            $originalAnswers[] = $answer;
        }

        $form = $this->createForm(RiskQuestionFormType::class, $question);
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $question = $form->getData();

                foreach ($question->getAnswers() as $answer) {
                    $answer->setQuestion($question);
                    $em->persist($answer);

                    foreach ($originalAnswers as $key => $toDel) {
                        if ($toDel->getId() === $answer->getId()) {
                            unset($originalAnswers[$key]);
                        }
                    }
                }

                foreach ($originalAnswers as $answer) {
                    $em->remove($answer);
                }

                $em->persist($question);
                $em->flush();

                return $this->json([
                    'status' => 'success',
                    'content' => $this->renderView('/Ria/Risks/_question_row.html.twig', ['question' => $question]),
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'form' => $this->renderView('/Ria/Risks/_question_form.html.twig', ['form' => $form->createView()]),
                ]);
            }
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Risks/_question_form.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function deleteQuestion(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $this->getUser();
        $question = $em->getRepository('App\Entity\RiskQuestion')->findOneBy([
            'id' => $request->get('id'),
            'owner_id' => $user->getId(),
        ]);

        if (!$question) {
            return $this->json(['status' => 'error', 'message' => 'Question does not exist.']);
        }

        $em->remove($question);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    public function test(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $this->getUser();
        $form = $this->createForm(RiskQuestionsFormType::class, null, ['em'=>$em, 'user'=>$user, 'is_pre_save'=>false]);

        if ($request->isMethod('post')) {
            $formHandler = new RiskQuestionsFormHandler($form, $request, $em, ['session' => $this->get('session')]);

            try {
                $process = $formHandler->process($user);
            } catch (NotFoundHttpException $e) {
                throw $this->createNotFoundException($e->getMessage(), $e);
            } catch (AdvisorHasNoExistingModel $e) {
                throw $this->createNotFoundException($e->getMessage(), $e);
            }

            if ($process) {
                return $this->redirect($this->generateUrl('rx_ria_risks_test_result'));
            }
        }

        return $this->render('Ria/Risks/test.html.twig', ['form' => $form->createView()]);
    }

    public function testResult(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var \App\Repository\CeModelRepository $repo */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\CeModel');

        $ria = $this->getUser();

        /** @var CeModel $sessionPortfolio */
        $sessionPortfolio = $repo->find($this->get('session')->get('ria.risk_profiling.suggested_portfolio'));
        if (!$sessionPortfolio || !($sessionPortfolio instanceof CeModel)) {
            throw $this->createNotFoundException('No Suggested Portfolio.');
        }

        $portfolioInformationManager = $this->get('wealthbot_client.portfolio_information_manager');
        $companyInformation = $ria->getRiaCompanyInformation();
        $isUseQualified = $companyInformation->getIsUseQualifiedModels();

        if ($isUseQualified) {
            if (null !== $request->get('is_qualified')) {
                $this->setIsQualifiedModel($request->get('is_qualified'));
            }
            $isQualified = $this->getIsQualifiedModel();
        } else {
            $isQualified = false;
        }

        $data = [
            'is_final' => false,
            'is_risks_test' => true,
            'portfolio_information' => $portfolioInformationManager->getPortfolioInformation($ria, $sessionPortfolio, $isQualified),
            'has_retirement_account' => false,
            'ria_company_information' => $ria->getRiaCompanyInformation(),
            'is_use_qualified_models' => $isUseQualified,
            'client' => null,
            'action' => 'view_and_test',
        ];

        return $this->render('/Client/Portfolio/index.html.twig', $data);
    }

    public function updateOrder(Request $request)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();

        $data = $request->get('item');
        if (!is_array($data) || empty($data)) {
            return $this->json([
                'status' => 'error',
                'message' => 'No data.',
            ]);
        }

        $qb = $em->createQueryBuilder();
        foreach ($data as $position => $id) {
            $qb->update('App\Entity\RiskQuestion rq')
                ->set('rq.sequence', $position)
                ->where('rq.id = :id AND rq.owner_id = :owner_id')
                ->setParameters([
                        'id' => $id,
                        'owner_id' => $user->getId(),
                    ])
                ->getQuery()
                ->execute()
            ;
        }

        return $this->json(['status' => 'success']);
    }

    /**
     * Set what type of models RIA will be used (qualified or non-qualified).
     *
     * @param bool $value
     */
    protected function setIsQualifiedModel($value)
    {
        /** @var Session $session */
        $session = $this->get('session');
        $session->set('risk.is_qualified', (bool) $value);
    }

    /**
     * Set what type of models RIA will be used (qualified or non-qualified).
     *
     * @return bool
     */
    protected function getIsQualifiedModel()
    {
        /** @var Session $session */
        $session = $this->get('session');

        return (bool) $session->get('risk.is_qualified', false);
    }
}
