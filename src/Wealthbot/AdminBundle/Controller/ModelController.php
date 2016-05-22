<?php

namespace Wealthbot\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Entity\CeModelEntity;
use Wealthbot\AdminBundle\Form\Handler\CeModelEntityFormHandler;
use Wealthbot\AdminBundle\Form\Handler\CeModelFormHandler;
use Wealthbot\AdminBundle\Form\Handler\ModelAssumptionFormHandler;
use Wealthbot\AdminBundle\Form\Handler\ParentCeModelFormHandler;
use Wealthbot\AdminBundle\Form\Type\CeModelEntityFormType;
use Wealthbot\AdminBundle\Form\Type\CeModelFormType;
use Wealthbot\AdminBundle\Form\Type\ModelAssumptionFormType;
use Wealthbot\AdminBundle\Form\Type\ParentCeModelFormType;
use Wealthbot\AdminBundle\Manager\CeModelManager;
use Wealthbot\AdminBundle\Model\Acl;

class ModelController extends AclController
{
    public function indexAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        $parentModel = $modelManager->createStrategyModel();
        $form = $this->createForm(new ParentCeModelFormType(), $parentModel);

        if ($request->isMethod('post')) {
            $this->checkAccess(Acl::PERMISSION_EDIT);

            $formHandler = new ParentCeModelFormHandler($form, $request, $em);
            if ($formHandler->process()) {
                return $this->redirect($this->generateUrl('rx_admin_models'));
            }
        }

        return $this->render('WealthbotAdminBundle:Model:index.html.twig', [
            'strategy_form' => $form->createView(),
        ]);
    }

    public function indexStrategyAction($slug)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var $parentModel CeModel */
        $parentModel = $modelManager->findCeModelBySlugAndOwnerId($slug);
        if (!$parentModel || ($parentModel && $parentModel->getIsDeleted())) {
            throw $this->createNotFoundException(sprintf('Model with slug %s does not exist', $slug));
        }

        $admin = $this->getUser();
        $model = $modelManager->createChild($parentModel);
        $form = $this->createForm(new CeModelFormType($em, $admin, $parentModel), $model);

        return $this->render('WealthbotAdminBundle:Model:index_strategy.html.twig', [
            'strategy_model_form' => $form->createView(),
            'selected_strategy' => $parentModel,
        ]);
    }

    public function deleteStrategyAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var $parentModel CeModel */
        $parentModel = $modelManager->findCeModelBy(['id' => $request->get('id')]);
        if ($parentModel) {
            $models = $modelManager->getChildModels($parentModel);

            foreach ($models as $model) {
                $model->setIsDeleted(true);
                $em->persist($model);
            }

            $parentModel->setIsDeleted(true);

            $em->persist($parentModel);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('rx_admin_models'));
    }

    public function editStrategyAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var $model CeModel */
        $model = $modelManager->findCeModelBy(['id' => $request->get('id')]);
        $admin = $this->getUser();
        $form = $this->createForm(new ParentCeModelFormType($admin), $model);

        if ($request->isMethod('post')) {
            $formHandler = new ParentCeModelFormHandler($form, $request, $em);

            if ($formHandler->process()) {
                return $this->getJsonResponse([
                    'status' => 'success',
                    'redirect_url' => $this->generateUrl('rx_admin_models_index_strategy', ['slug' => $model->getSlug()], true),
                ]);
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'content' => $this->renderView('WealthbotAdminBundle:Model:_third_party_edit_form.html.twig', [
                    'form' => $form->createView(),
                    'third_party' => $model,
                ]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotAdminBundle:Model:_third_party_edit_form.html.twig', [
                'form' => $form->createView(),
                'third_party' => $model,
            ]),
        ]);
    }

    public function deleteModelAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var $model CeModel */
        $model = $modelManager->findCeModelBy(['id' => $request->get('id')]);
        if ($model) {
            $selectedStrategy = $model->getParent();
            $model->setIsDeleted(true);
            $em->persist($model);
            $em->flush();

            return $this->redirect($this->generateUrl('rx_admin_models_index_strategy', [
                'slug' => $selectedStrategy->getSlug(),
            ]));
        }

        return $this->redirect($this->generateUrl('rx_admin_models'));
    }

    public function editModelAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $model CeModel */
        $em = $this->get('doctrine.orm.entity_manager');
        $model = $em->getRepository('WealthbotAdminBundle:CeModel')->find($request->get('id'));

        if (!$model) {
            return $this->getJsonResponse($result = [
                'status' => 'error',
                'message' => sprintf('Portfolio Model with slug "%s" does not exist.', $request->get('modelSlug')),
            ]);
        }

        $parentModel = $model->getParent();
        $user = $this->getUser();

        $form = $this->createForm(new CeModelFormType($em, $user, $parentModel, true), $model);

        if ($request->isMethod('post')) {
            $formHandler = new CeModelFormHandler($form, $request, $em, ['is_show_assumption' => true]);

            if ($formHandler->process()) {
                return $this->getJsonResponse([
                    'status' => 'success',
                    'redirect_url' => $this->generateUrl('rx_admin_model_schema', [
                        'strategy_slug' => $parentModel->getSlug(),
                        'model_slug' => $model->getSlug(),
                    ], true),
                ]);
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'content' => $this->renderView('WealthbotAdminBundle:Model:_third_party_model_edit_form.html.twig', [
                    'form' => $form->createView(),
                    'third_party_model' => $model,
                ]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotAdminBundle:Model:_third_party_model_edit_form.html.twig', [
                'form' => $form->createView(),
                'third_party_model' => $model,
            ]),
        ]);
    }

    public function createModelAction(Request $request)
    {
        $admin = $this->getUser();

        $this->checkAccess(Acl::PERMISSION_EDIT, $admin);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var CeModel $parentModel */
        $parentModel = $modelManager->findCeModelBySlugAndOwnerId($request->get('slug'));
        $model = $modelManager->createChild($parentModel);

        $form = $this->createForm(new CeModelFormType($em, $admin, $parentModel), $model);
        $formHandler = new CeModelFormHandler($form, $request, $em);

        if ($formHandler->process()) {
            return $this->render('WealthbotAdminBundle:Model:_models_tab.html.twig', [
                'strategy_model_form' => $form->createView(),
                'selected_strategy' => $parentModel,
            ]);
        }

        return $this->render('WealthbotAdminBundle:Model:_models_tab.html.twig', [
            'strategy_model_form' => $form->createView(),
            'selected_strategy' => $parentModel,
        ]);
    }

    public function editModelAssumptionAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var $parentModel CeModel */
        $parentModel = $modelManager->findCeModelBy(['id' => $request->get('id')]);

        $isShowForecast = true;
        $form = $this->createForm(new ModelAssumptionFormType($isShowForecast), $parentModel);

        if ($request->isMethod('post')) {
            $formHandler = new ModelAssumptionFormHandler($form, $request, $em);

            if ($formHandler->process()) {
                return $this->getJsonResponse([
                    'status' => 'success',
                    'redirect_url' => $this->generateUrl('rx_admin_models_index_strategy', [
                        'slug' => $parentModel->getSlug(),
                    ], true),
                ]);
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'content' => $this->renderView('WealthbotAdminBundle:Model:_third_party_model_edit_model_assumption_form.html.twig', [
                    'form' => $form->createView(),
                    'action_url' => $this->generateUrl('rx_admin_models_edit_model_assumption', [
                        'id' => $parentModel->getId(),
                    ]),
                ]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotAdminBundle:Model:_third_party_model_edit_model_assumption_form.html.twig', [
                'form' => $form->createView(),
                'action_url' => $this->generateUrl('rx_admin_models_edit_model_assumption', [
                    'id' => $parentModel->getId(),
                ]),
            ]),
        ]);
    }

    public function portfolioMenuAction(Request $request)
    {
        /** @var $modelManager CeModelManager */
        /* @var CeModel $model */
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');
        $models = $modelManager->getAdminStrategyParentModels();

        return $this->render('WealthbotAdminBundle:Model:_portfolio_menu.html.twig', [
            'models' => $models,
            'selected_strategy_id' => $request->get('selected_strategy_id'),
        ]);
    }

    public function modelMenuAction($strategySlug, $modelSlug = null)
    {
        $models = [];
        if (isset($strategySlug)) {
            /** @var $modelManager CeModelManager */
            $modelManager = $this->get('wealthbot_admin.ce_model_manager');

            /** @var CeModel $parentModel */
            $parentModel = $modelManager->findCeModelBySlugAndOwnerId($strategySlug);
            if ($parentModel) {
                $models = $modelManager->getChildModels($parentModel);
            }
        }

        return $this->render('WealthbotAdminBundle:Model:_model_menu.html.twig', [
            'models' => $models,
            'strategy_slug' => $strategySlug,
            'model_slug' => $modelSlug,
        ]);
    }

    public function modelAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var CeModel $model */
        /* @var $parentModel CeModel */
        $model = $modelManager->findCeModelBySlugAndOwnerId($request->get('model_slug'));
        $parentModel = $model->getParent();

        if (!$parentModel || !$model) {
            throw $this->createNotFoundException('Strategy and model are required.');
        }

        $form = $this->createForm(new CeModelEntityFormType($model, $em, $this->getUser()));

        $modelEntities = $em->getRepository('WealthbotAdminBundle:CeModelEntity')->findBy([
            'modelId' => $model->getId(),
        ]);

        return $this->render('WealthbotAdminBundle:Model:model.html.twig', [
            'form' => $form->createView(),
            'modelEntities' => $modelEntities,
            'selected_strategy' => $parentModel,
            'model' => $model,
        ]);
    }

    public function saveAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var CeModel $model */
        $model = $modelManager->findCeModelBySlugAndOwnerId($request->get('modelSlug'));
        if (!$model || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $modelEntity = new CeModelEntity();
        $form = $this->createForm(new CeModelEntityFormType($model, $em, $this->getUser()), $modelEntity);
        $formHandler = new CeModelEntityFormHandler($form, $request, $em, ['model' => $model]);

        if ($formHandler->process()) {
            $newForm = $this->createForm(new CeModelEntityFormType($model, $em, $this->getUser()));

            return $this->getJsonResponse([
                'status' => 'success',
                'form' => $this->renderView('WealthbotAdminBundle:Model:_form.html.twig', [
                    'form' => $newForm->createView(),
                    'model' => $model,
                ]),
                'content' => $this->renderView('WealthbotAdminBundle:Model:_model_row.html.twig', ['modelEntity' => $modelEntity]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'error',
            'form' => $this->renderView('WealthbotAdminBundle:Model:_form.html.twig', [
                'form' => $form->createView(),
                'model' => $model,
            ]),
        ]);
    }

    public function updateFormAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var CeModel $model */
        $model = $modelManager->findCeModelBySlugAndOwnerId($request->get('modelSlug'));
        if (!$model) {
            return $this->getJsonResponse($result = [
                'status' => 'error',
                'message' => sprintf('Portfolio Model with slug "%s" does not exist.', $request->get('modelSlug')),
            ]);
        }

        $form = $this->createForm(new CeModelEntityFormType($model, $em, $this->getUser()));
        $form->handleRequest($request);

        $result = [
            'status' => 'success',
            'content' => $this->renderView('WealthbotAdminBundle:Model:_form_fields.html.twig', ['form' => $form->createView()]),
        ];

        return $this->getJsonResponse($result);
    }

    public function deleteAction($id)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $modelEntity = $em->getRepository('WealthbotAdminBundle:CeModelEntity')->find($id);
        if (!$modelEntity) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Model Entity with id: '.$id.' does not exist.',
            ]);
        }

        $em->remove($modelEntity);
        $em->flush();

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function editAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $id = $request->get('id');

        $modelEntity = $em->getRepository('WealthbotAdminBundle:CeModelEntity')->find($id);
        if (!$modelEntity) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Model Entity with id: '.$id.' does not exist.',
            ]);
        }

        $model = $modelEntity->getModel();

        $form = $this->createForm(new CeModelEntityFormType($model, $em, $this->getUser()), $modelEntity);
        $formHandler = new CeModelFormHandler($form, $request, $em);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                $form = $this->createForm(new CeModelEntityFormType($model, $em, $this->getUser()));

                return $this->getJsonResponse([
                    'status' => 'success',
                    'form' => $this->renderView('WealthbotAdminBundle:Model:_form.html.twig', [
                        'form' => $form->createView(),
                        'model' => $model,
                    ]),
                    'content' => $this->renderView('WealthbotAdminBundle:Model:_model_row.html.twig', ['modelEntity' => $modelEntity]),
                ]);
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'form' => $this->renderView('WealthbotAdminBundle:Model:_edit_form.html.twig', [
                    'form' => $form->createView(),
                    'modelEntity' => $modelEntity,
                ]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'form' => $this->renderView('WealthbotAdminBundle:Model:_edit_form.html.twig', [
                'form' => $form->createView(),
                'modelEntity' => $modelEntity,
            ]),
        ]);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
