<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\CeModel;
use App\Entity\Security;
use App\Entity\SecurityAssignment;
use App\Form\Handler\ModelSecurityFormHandler;
use App\Form\Handler\SecurityFormHandler;
use App\Form\Type\ModelSecurityFormType;
use App\Form\Type\SecurityFormType as SecurityFormTypeAlias;
use App\Model\Acl;

class SecuritiesController extends AclController
{
    public function index(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\Security');

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $repository->getFindWithCurrentPriceQueryBuilder()->getQuery(),
            $request->query->get('page', 1),
            30
        );

        $form = $this->createForm(SecurityFormTypeAlias::class);
        $formHandler = $this->get('wealthbot_admin.securities.form.handler');

        if ($request->isMethod('post')) {
            $process = $formHandler->success();

            if ($process) {
                $form = $this->createForm(SecurityFormTypeAlias::class);
            }
        }

        return $this->render('/Admin/Securities/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'is_employer_account_owned' => false,
        ]);
    }

    public function new()
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        return $this->json([
            'status' => 'success',
            'form' => $this->renderView('/Admin/Securities/_form.html.twig', [
                'form' => $this->createForm(SecurityFormTypeAlias::class)->createView(),
            ]),
        ]);
    }

    // TODO: check!
    public function edit(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        $id = $request->get('id');

        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\Security');
        $priceRepository = $em->getRepository('App\Entity\SecurityPrice');

        $security = $repository->find($id);
        if (!$security) {
            return $this->json([
                'status' => 'error',
                'message' => 'Security object with id: '.$id.' does not exist.',
            ]);
        }

        $form = $this->createForm(SecurityFormTypeAlias::class, $security);
        $formHandler = new SecurityFormHandler($form, $this->get('request_stack'), $em, [
            'token_storage' => $this->get('security.token_storage'),
        ]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                $security = $form->getData();
                $currentPrice = $priceRepository->getCurrentBySecurityId($security->getId());

                $dql = 'SELECT COUNT(cra.id) FROM App\Entity\ClientAccount cra
                        LEFT JOIN cra.accountOutsideFunds aof LEFT JOIN aof.securityAssignment s LEFT JOIN s.security f
                        WHERE f.symbol=:symbol';
                $isEmployerAccountOwned = $em->createQuery($dql)->setParameter(
                    'symbol',
                    $security->getSymbol()
                )->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR);

                return $this->json([
                    'status' => 'success',
                    'id' => $security->getId(),
                    'content' => $this->renderView('/Admin/Securities/_securities_list_item.html.twig', [
                        'security' => $security,
                        'current_price' => ($currentPrice ? $currentPrice->getPrice() : null),
                        'is_employer_account_owned' => $isEmployerAccountOwned,
                    ]),
                    'form' => $this->renderView('/Admin/Securities/_form.html.twig', [
                        'form' => $this->createForm(SecurityFormTypeAlias::class)->createView(),
                    ]),
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'form' => $this->renderView('/Admin/Securities/_edit_form.html.twig', [
                        'form' => $form->createView(),
                        'security' => $security,
                    ]),
                ]);
            }
        }

        return $this->json([
            'status' => 'success',
            'form' => $this->renderView('/Admin/Securities/_edit_form.html.twig', [
                'form' => $form->createView(),
                'security' => $security,
            ]),
        ]);
    }

    public function delete($id)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $securityAssignment = $em->getRepository('App\Entity\Security')->find($id);

        if ($securityAssignment) {
            $em->remove($securityAssignment);
            $em->flush();

            return $this->json(['status' => 'success']);
        }

        return $this->json([
                'status' => 'error',
                'message' => 'Error.',
            ]);
    }

    public function modelSecuritiesList(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $model CeModel */
        $em = $this->get('doctrine.orm.entity_manager');
        $model = $em->getRepository('App\Entity\CeModel')->find($request->get('model_id'));

        $securityAssignments = $em->getRepository('App\Entity\SecurityAssignment')->findBy(['model_id' => $model->getId()]);

        $securityAssignment = new SecurityAssignment();
        $securityAssignment->setModel($model);

        $form = $this->createForm(ModelSecurityFormType::class, $securityAssignment, ['selected_model'=>$model, 'em'=>$em]);

        if ($request->isMethod('post')) {
            $formHandler = new ModelSecurityFormHandler($form, $request, $em, ['security_assignment' => $securityAssignment]);

            if ($formHandler->process()) {
                return $this->redirect($this->generateUrl('rx_admin_model_securities_list', ['model_id' => $model->getId()]));
            }
        }

        return $this->render('/Admin/Securities/model_securities_list.html.twig', [
            'form' => $form->createView(),
            'security_assignments' => $securityAssignments,
        ]);
    }

    public function completeFunds(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $query = $request->get('query');

        //TODO need check why NOT IN doesn't work in query
        $securities = $em->getRepository('App\Entity\Security')->createQueryBuilder('s')
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

        return $this->json($output);
    }

    public function completeSubclasses(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $assetClass = $em->getRepository('App\Entity\AssetClass')->find($request->get('asset_id'));

        if (!$assetClass) {
            throw $this->createNotFoundException(sprintf('AssetClass with id %d does not exist.', $request->get('asset_id')));
        }

        $subclasses = $em->getRepository('App\Entity\Subclass')->findDefaultsByAssetClass($assetClass->getId());

        $output = "<option value=''>Choose an option</option>";
        foreach ($subclasses as $subclass) {
            $output .= "<option value='".$subclass->getId()."'>".$subclass->getName().'</option>';
        }

        return new Response($output);
    }

    public function editModelSecurity(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $securityAssignment = $em->getRepository('App\Entity\SecurityAssignment')->find($request->get('id'));

        if (!$securityAssignment) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }

        $model = $securityAssignment->getModel();
        $form = $this->createForm(ModelSecurityFormType::class, $securityAssignment, ['selected_model'=>$model,'em'=> $em]);

        if ($request->isMethod('post')) {
            $formHandler = new ModelSecurityFormHandler($form, $request, $em, ['security_assignment' => $securityAssignment]);

            if ($formHandler->process()) {
                return $this->redirect($this->generateUrl('rx_admin_model_securities_list', ['model_id' => $model->getId()]));
            }
        }

        $securityAssignments = $em->getRepository('App\Entity\SecurityAssignment')->findBy(['model_id' => $model->getId()]);

        return $this->render('/Admin/Securities/model_securities_list_edit.html.twig', [
            'form' => $form->createView(),
            'security_assignment' => $securityAssignment,
            'security_assignments' => $securityAssignments,
        ]);
    }

    public function deleteModelSecurity(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $securityAssignments = $em->getRepository('App\Entity\SecurityAssignment')->find($request->get('id'));

        if (!$securityAssignments) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }
        $model = $securityAssignments->getModel();

        $em->remove($securityAssignments);
        $em->flush();

        return $this->redirect($this->generateUrl('rx_admin_model_securities_list', ['model_id' => $model->getId()]));
    }

    public function updateSubclassFormField(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        $form = $this->createForm(SecurityFormTypeAlias::class);
        $form->handleRequest($request);

        $result = [
            'content' => $this->renderView('/Admin/Securities/_security_subclass_form_field.html.twig', [
                'form' => $form->createView(),
            ]),
        ];

        return $this->json($result);
    }

    public function priceHistory(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\Security');

        $securityId = $request->get('id');
        $security = $repository->find($securityId);
        if (!$security) {
            throw $this->createNotFoundException(sprintf('Security object with id: %s does not exist', $securityId));
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView(
                    '/Admin/Securities/_price_history_list.html.twig',
                    ['security' => $security]
                ),
            ]);
        }

        return $this->render(
            '/Admin/Securities/price_history.html.twig',
            ['security' => $security]
        );
    }

    public function priceHistoryBatch(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\Security');

        $securityId = $request->get('id');
        $security = $repository->find($securityId);
        if (!$security) {
            throw $this->createNotFoundException(sprintf('Security object with id: %s does not exist', $securityId));
        }

        $action = $request->get('action');
        $ids = $request->get('security_price_history_batch');

        if (is_array($ids) && count($ids)) {
            $qb = $em->createQueryBuilder();

            if ('delete' === $action) {
                $qb->delete('App\Entity\SecurityPrice', 'sp');
            } elseif ('unpost' === $action) {
                $qb->update('App\Entity\SecurityPrice', 'sp')
                    ->set('sp.is_posted', 0);
            } elseif ('repost' === $action) {
                $qb->update('App\Entity\SecurityPrice', 'sp')
                    ->set('sp.is_posted', 1);
            }

            $qb->where($qb->expr()->in('sp.id', $ids));
            $res = $qb->getQuery()->execute();
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView(
                '/Admin/Securities/_price_history_list.html.twig',
                ['security' => $security]
            ),
        ]);
    }
}
