<?php

namespace Wealthbot\AdminBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Form\Handler\ModelSecurityFormHandler;
use Wealthbot\AdminBundle\Form\Handler\SecurityFormHandler;
use Wealthbot\AdminBundle\Form\Type\ModelSecurityFormType;
use Wealthbot\AdminBundle\Form\Type\SecurityFormType;
use Wealthbot\AdminBundle\Model\Acl;

class SecuritiesController extends AclController
{
    public function indexAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WealthbotAdminBundle:Security');

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $repository->getFindWithCurrentPriceQueryBuilder()->getQuery(),
            $request->query->get('page', 1),
            30
        );

        $form = $this->get('wealthbot_admin.securities.form');
        $formHandler = $this->get('wealthbot_admin.securities.form.handler');

        if ($request->isMethod('post')) {
            $process = $formHandler->process();

            if ($process) {
                $form = $this->createForm(new SecurityFormType());
            }
        }

        return $this->render('WealthbotAdminBundle:Securities:index.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'is_employer_account_owned' => false,
        ]);
    }

    public function newAction()
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        return $this->getJsonResponse([
            'status' => 'success',
            'form' => $this->renderView('WealthbotAdminBundle:Securities:_form.html.twig', [
                'form' => $this->createForm(new SecurityFormType())->createView(),
            ]),
        ]);
    }

    // TODO: check!
    public function editAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        $id = $request->get('id');

        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WealthbotAdminBundle:Security');
        $priceRepository = $em->getRepository('WealthbotAdminBundle:SecurityPrice');

        $security = $repository->find($id);
        if (!$security) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Security object with id: '.$id.' does not exist.',
            ]);
        }

        $form = $this->createForm(new SecurityFormType(), $security);
        $formHandler = new SecurityFormHandler($form, $request, $em, [
            'token_storage' => $this->get('security.token_storage'),
        ]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                $security = $form->getData();
                $currentPrice = $priceRepository->getCurrentBySecurityId($security->getId());

                $dql = 'SELECT COUNT(cra.id) FROM WealthbotClientBundle:ClientAccount cra
                        LEFT JOIN cra.accountOutsideFunds aof LEFT JOIN aof.securityAssignment s LEFT JOIN s.security f
                        WHERE f.symbol=:symbol';
                $isEmployerAccountOwned = $em->createQuery($dql)->setParameter(
                    'symbol',
                    $security->getSymbol()
                )->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR);

                return $this->getJsonResponse([
                    'status' => 'success',
                    'id' => $security->getId(),
                    'content' => $this->renderView('WealthbotAdminBundle:Securities:_securities_list_item.html.twig', [
                        'security' => $security,
                        'current_price' => ($currentPrice ? $currentPrice->getPrice() : null),
                        'is_employer_account_owned' => $isEmployerAccountOwned,
                    ]),
                    'form' => $this->renderView('WealthbotAdminBundle:Securities:_form.html.twig', [
                        'form' => $this->createForm(new SecurityFormType())->createView(),
                    ]),
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'form' => $this->renderView('WealthbotAdminBundle:Securities:_edit_form.html.twig', [
                        'form' => $form->createView(),
                        'security' => $security,
                    ]),
                ]);
            }
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'form' => $this->renderView('WealthbotAdminBundle:Securities:_edit_form.html.twig', [
                'form' => $form->createView(),
                'security' => $security,
            ]),
        ]);
    }

    public function deleteAction($id)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $securityAssignment = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->find($id);

        if ($securityAssignment) {
            $em->remove($securityAssignment);
            $em->flush();

            return $this->getJsonResponse(['status' => 'success']);
        }

        return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Error.',
            ]);
    }

    public function modelSecuritiesListAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $model CeModel */
        $em = $this->get('doctrine.orm.entity_manager');
        $model = $em->getRepository('WealthbotAdminBundle:CeModel')->find($request->get('model_id'));

        $securityAssignments = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findBy(['model_id' => $model->getId()]);

        $securityAssignment = new SecurityAssignment();
        $securityAssignment->setModel($model);

        $form = $this->createForm(new ModelSecurityFormType($model, $em), $securityAssignment);

        if ($request->isMethod('post')) {
            $formHandler = new ModelSecurityFormHandler($form, $request, $em, ['security_assignment' => $securityAssignment]);

            if ($formHandler->process()) {
                return $this->redirect($this->generateUrl('rx_admin_model_securities_list', ['model_id' => $model->getId()]));
            }
        }

        return $this->render('WealthbotAdminBundle:Securities:model_securities_list.html.twig', [
            'form' => $form->createView(),
            'security_assignments' => $securityAssignments,
        ]);
    }

    public function completeFundsAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $query = $request->get('query');

        //TODO need check why NOT IN doesn't work in query
        $securities = $em->getRepository('WealthbotAdminBundle:Security')->createQueryBuilder('s')
            ->leftJoin('s.securityAssignments', 'sa')
            ->where('s.symbol LIKE :symbol')
            ->andWhere('sa.id IS NULL')
            ->orWhere('sa.ria_user_id IS NULL AND sa.model_id IS NULL')
            ->setParameters([
                'symbol' => '%'.$query.'%',
            ])
            ->getQuery()
            ->execute();

        $output = [];

        /** @var Security $security */
        foreach ($securities as $security) {
            $card['id'] = $security->getId();
            $card['display_name'] = $security->getSymbol().' ('.$security->getName().')';
            $card['symbol'] = $security->getSymbol();
            $card['name'] = $security->getName();
            $card['expense_ratio'] = $security->getExpenseRatio();
            $card['type'] = $security->getSecurityType()->getDescription();

            $output[] = $card;
        }

        return $this->getJsonResponse($output);
    }

    public function completeSubclassesAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $assetClass = $em->getRepository('WealthbotAdminBundle:AssetClass')->find($request->get('asset_id'));

        if (!$assetClass) {
            throw $this->createNotFoundException(sprintf('AssetClass with id %d does not exist.', $request->get('asset_id')));
        }

        $subclasses = $em->getRepository('WealthbotAdminBundle:Subclass')->findDefaultsByAssetClass($assetClass->getId());

        $output = "<option value=''>Choose an option</option>";
        foreach ($subclasses as $subclass) {
            $output .= "<option value='".$subclass->getId()."'>".$subclass->getName().'</option>';
        }

        return new Response($output);
    }

    public function editModelSecurityAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $securityAssignment = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->find($request->get('id'));

        if (!$securityAssignment) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }

        $model = $securityAssignment->getModel();
        $form = $this->createForm(new ModelSecurityFormType($model, $em), $securityAssignment);

        if ($request->isMethod('post')) {
            $formHandler = new ModelSecurityFormHandler($form, $request, $em, ['security_assignment' => $securityAssignment]);

            if ($formHandler->process()) {
                return $this->redirect($this->generateUrl('rx_admin_model_securities_list', ['model_id' => $model->getId()]));
            }
        }

        $securityAssignments = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findBy(['model_id' => $model->getId()]);

        return $this->render('WealthbotAdminBundle:Securities:model_securities_list_edit.html.twig', [
            'form' => $form->createView(),
            'security_assignment' => $securityAssignment,
            'security_assignments' => $securityAssignments,
        ]);
    }

    public function deleteModelSecurityAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $securityAssignments = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->find($request->get('id'));

        if (!$securityAssignments) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }
        $model = $securityAssignments->getModel();

        $em->remove($securityAssignments);
        $em->flush();

        return $this->redirect($this->generateUrl('rx_admin_model_securities_list', ['model_id' => $model->getId()]));
    }

    public function updateSubclassFormFieldAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        $form = $this->createForm(new SecurityFormType());
        $form->handleRequest($request);

        $result = [
            'content' => $this->renderView('WealthbotAdminBundle:Securities:_security_subclass_form_field.html.twig', [
                'form' => $form->createView(),
            ]),
        ];

        return $this->getJsonResponse($result);
    }

    public function priceHistoryAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WealthbotAdminBundle:Security');

        $securityId = $request->get('id');
        $security = $repository->find($securityId);
        if (!$security) {
            throw $this->createNotFoundException(sprintf('Security object with id: %s does not exist', $securityId));
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $this->renderView(
                    'WealthbotAdminBundle:Securities:_price_history_list.html.twig',
                    ['security' => $security]
                ),
            ]);
        }

        return $this->render(
            'WealthbotAdminBundle:Securities:price_history.html.twig',
            ['security' => $security]
        );
    }

    public function priceHistoryBatchAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WealthbotAdminBundle:Security');

        $securityId = $request->get('id');
        $security = $repository->find($securityId);
        if (!$security) {
            throw $this->createNotFoundException(sprintf('Security object with id: %s does not exist', $securityId));
        }

        $action = $request->get('action');
        $ids = $request->get('security_price_history_batch');

        if (is_array($ids) && count($ids)) {
            $qb = $em->createQueryBuilder();

            if ($action === 'delete') {
                $qb->delete('WealthbotAdminBundle:SecurityPrice', 'sp');
            } elseif ($action === 'unpost') {
                $qb->update('WealthbotAdminBundle:SecurityPrice', 'sp')
                    ->set('sp.is_posted', 0);
            } elseif ($action === 'repost') {
                $qb->update('WealthbotAdminBundle:SecurityPrice', 'sp')
                    ->set('sp.is_posted', 1);
            }

            $qb->where($qb->expr()->in('sp.id', $ids));
            $res = $qb->getQuery()->execute();
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView(
                'WealthbotAdminBundle:Securities:_price_history_list.html.twig',
                ['security' => $security]
            ),
        ]);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
