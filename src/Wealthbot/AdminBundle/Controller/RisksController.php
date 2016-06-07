<?php

namespace Wealthbot\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Form\Type\RiskQuestionFormType;
use Wealthbot\AdminBundle\Model\Acl;
use Wealthbot\RiaBundle\Repository\RiskQuestionRepository;
use Wealthbot\UserBundle\Entity\User;

class RisksController extends AclController
{
    public function indexAction()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo RiskQuestionRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotRiaBundle:RiskQuestion');

        $questions = $repo->getAdminQuestions();

        return $this->render('WealthbotAdminBundle:Risks:index.html.twig', [
            'questions' => $questions,
        ]);
    }

    public function createQuestionAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $question = new \Wealthbot\RiaBundle\Entity\RiskQuestion();
        for ($i = 0; $i < 2; ++$i) {
            $answer = new \Wealthbot\RiaBundle\Entity\RiskAnswer();
            $question->addAnswer($answer);
        }

        $form = $this->createForm(new RiskQuestionFormType(), $question);
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();
                $question = $form->getData();

                $question->setOwner($superAdmin);
                $em->persist($question);

                foreach ($question->getAnswers() as $answer) {
                    $answer->setQuestion($question);
                    $em->persist($answer);
                }

                $em->flush();

                return $this->getJsonResponse([
                    'status' => 'success',
                    'new_row' => $this->renderView('WealthbotAdminBundle:Risks:_question_row.html.twig', ['question' => $question]),
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'form' => $this->renderView('WealthbotAdminBundle:Risks:_question_form.html.twig', ['form' => $form->createView()]),
                ]);
            }
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotAdminBundle:Risks:_question_form.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function editQuestionAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUser();

        $this->checkAccess(Acl::PERMISSION_EDIT, $user);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $question = $em->getRepository('WealthbotRiaBundle:RiskQuestion')->findOneBy([
            'id' => $request->get('id'),
            'owner_id' => $user->getId(),
        ]);

        if (!$question) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Question does not exist.']);
        }

        $originalAnswers = [];
        foreach ($question->getAnswers() as $answer) {
            $originalAnswers[] = $answer;
        }

        $form = $this->createForm(new RiskQuestionFormType(), $question);
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

                return $this->getJsonResponse([
                    'status' => 'success',
                    'content' => $this->renderView('WealthbotAdminBundle:Risks:_question_row.html.twig', ['question' => $question]),
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'form' => $this->renderView('WealthbotAdminBundle:Risks:_question_form.html.twig', ['form' => $form->createView()]),
                ]);
            }
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotAdminBundle:Risks:_question_form.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function deleteQuestionAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();
        $question = $em->getRepository('WealthbotRiaBundle:RiskQuestion')->findOneBy([
            'id' => $request->get('id'),
            'owner_id' => $superAdmin->getId(),
            'is_withdraw_age_input' => 0,
        ]);

        if (!$question) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Question does not exist.']);
        }

        $em->remove($question);
        $em->flush();

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function updateOrderAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $data = $request->get('item');
        if (!is_array($data) || empty($data)) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'No data.',
            ]);
        }

        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        $qb = $em->createQueryBuilder();
        foreach ($data as $position => $id) {
            $qb->update('WealthbotRiaBundle:RiskQuestion rq')
                ->set('rq.sequence', $position)
                ->where('rq.id = :id AND rq.owner_id = :owner_id')
                ->setParameters([
                    'id' => $id,
                    'owner_id' => $superAdmin->getId(),
                ])
                ->getQuery()
                ->execute()
            ;
        }

        return $this->getJsonResponse(['status' => 'success']);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
