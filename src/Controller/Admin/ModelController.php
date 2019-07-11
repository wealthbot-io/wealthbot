<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\CeModel;
use App\Entity\CeModelEntity;
use App\Form\Handler\CeModelEntityFormHandler;
use App\Form\Handler\CeModelFormHandler;
use App\Form\Handler\ModelAssumptionFormHandler;
use App\Form\Handler\ParentCeModelFormHandler;
use App\Form\Type\CeModelEntityFormType;
use App\Form\Type\CeModelFormType;
use App\Form\Type\ModelAssumptionFormType;
use App\Form\Type\ParentCeModelFormType;
use App\Manager\CeModelManager;
use App\Model\Acl;

class ModelController extends AclController
{
    public function index(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        $parentModel = $modelManager->createStrategyModel();
        $form = $this->createForm(ParentCeModelFormType::class, $parentModel);

        if ($request->isMethod('post')) {
            $this->checkAccess(Acl::PERMISSION_EDIT);

            $formHandler = new ParentCeModelFormHandler($form, $request, $em);
            if ($formHandler->process()) {
                return $this->redirect($this->generateUrl('rx_admin_models'));
            }
        }

        return $this->render('/Admin/Model/index.html.twig', [
            'strategy_form' => $form->createView(),
        ]);
    }

    public function indexStrategy($slug)
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
        $form = $this->createForm(CeModelFormType::class, $model, [
            'em' => $em,
            'user' => $admin,
            'parent' => $parentModel,
        ]);

        return $this->render('/Admin/Model/index_strategy.html.twig', [
            'strategy_model_form' => $form->createView(),
            'selected_strategy' => $parentModel,
        ]);
    }

    public function deleteStrategy(Request $request)
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

    public function editStrategy(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var $model CeModel */
        $model = $modelManager->findCeModelBy(['id' => $request->get('id')]);
        $admin = $this->getUser();
        $form = $this->createForm(ParentCeModelFormType::class, $model, [
            'admin' => $admin,
        ]);

        if ($request->isMethod('post')) {
            $formHandler = new ParentCeModelFormHandler($form, $request, $em);

            if ($formHandler->process()) {
                return $this->json([
                    'status' => 'success',
                    'redirect_url' => $this->generateUrl('rx_admin_models_index_strategy', ['slug' => $model->getSlug()], true),
                ]);
            }

            return $this->json([
                'status' => 'error',
                'content' => $this->renderView('/Admin/Model/_third_party_edit_form.html.twig', [
                    'form' => $form->createView(),
                    'third_party' => $model,
                ]),
            ]);
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Admin/Model/_third_party_edit_form.html.twig', [
                'form' => $form->createView(),
                'third_party' => $model,
            ]),
        ]);
    }

    public function deleteModel(Request $request)
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

    public function editModel(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $model CeModel */
        $em = $this->get('doctrine.orm.entity_manager');
        $model = $em->getRepository('App\Entity\CeModel')->find($request->get('id'));

        if (!$model) {
            return $this->json($result = [
                'status' => 'error',
                'message' => sprintf('Portfolio Model with slug "%s" does not exist.', $request->get('modelSlug')),
            ]);
        }

        $parentModel = $model->getParent();
        $user = $this->getUser();

        $form = $this->createForm(CeModelFormType::class, $model, [
            'em' => $em,
            'user' => $user,
            'parent' => $parentModel,
            'isShowAssumption' => true,
        ]);

        if ($request->isMethod('post')) {
            $formHandler = new CeModelFormHandler($form, $request, $em, ['is_show_assumption' => true]);

            if ($formHandler->process()) {
                return $this->json([
                    'status' => 'success',
                    'redirect_url' => $this->generateUrl('rx_admin_model_schema', [
                        'strategy_slug' => $parentModel->getSlug(),
                        'model_slug' => $model->getSlug(),
                    ], true),
                ]);
            }

            return $this->json([
                'status' => 'error',
                'content' => $this->renderView('/Admin/Model/_third_party_model_edit_form.html.twig', [
                    'form' => $form->createView(),
                    'third_party_model' => $model,
                ]),
            ]);
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Admin/Model/_third_party_model_edit_form.html.twig', [
                'form' => $form->createView(),
                'third_party_model' => $model,
            ]),
        ]);
    }

    public function createModel(Request $request)
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

        $form = $this->createForm(CeModelFormType::class, $model, [
            'em' => $em,
            'user' => $admin,
            'parent' => $parentModel,
        ]);
        $formHandler = new CeModelFormHandler($form, $request, $em);

        if ($formHandler->process()) {
            return $this->render('/Admin/Model/_models_tab.html.twig', [
                'strategy_model_form' => $form->createView(),
                'selected_strategy' => $parentModel,
            ]);
        }

        return $this->render('/Admin/Model/_models_tab.html.twig', [
            'strategy_model_form' => $form->createView(),
            'selected_strategy' => $parentModel,
        ]);
    }

    public function editModelAssumption(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var $parentModel CeModel */
        $parentModel = $modelManager->findCeModelBy(['id' => $request->get('id')]);

        $isShowForecast = true;
        $form = $this->createForm(ModelAssumptionFormType::class, $isShowForecast, $parentModel);

        if ($request->isMethod('post')) {
            $formHandler = new ModelAssumptionFormHandler($form, $request, $em);

            if ($formHandler->process()) {
                return $this->json([
                    'status' => 'success',
                    'redirect_url' => $this->generateUrl('rx_admin_models_index_strategy', [
                        'slug' => $parentModel->getSlug(),
                    ], true),
                ]);
            }

            return $this->json([
                'status' => 'error',
                'content' => $this->renderView('/Admin/Model/_third_party_model_edit_model_assumption_form.html.twig', [
                    'form' => $form->createView(),
                    'action_url' => $this->generateUrl('rx_admin_models_edit_model_assumption', [
                        'id' => $parentModel->getId(),
                    ]),
                ]),
            ]);
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Admin/Model/_third_party_model_edit_model_assumption_form.html.twig', [
                'form' => $form->createView(),
                'action_url' => $this->generateUrl('rx_admin_models_edit_model_assumption', [
                    'id' => $parentModel->getId(),
                ]),
            ]),
        ]);
    }

    public function portfolioMenu(Request $request)
    {
        /** @var $modelManager CeModelManager */
        /* @var CeModel $model */
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');
        $models = $modelManager->getAdminStrategyParentModels();

        return $this->render('/Admin/Model/_portfolio_menu.html.twig', [
            'models' => $models,
            'selected_strategy_id' => $request->get('selected_strategy_id'),
        ]);
    }

    public function modelMenu($strategySlug  = null, $modelSlug = null)
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

        return $this->render('/Admin/Model/_model_menu.html.twig', [
            'models' => $models,
            'strategy_slug' => $strategySlug,
            'model_slug' => $modelSlug,
        ]);
    }

    public function model(Request $request)
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

        $form = $this->createForm(CeModelEntityFormType::class, null, ['model'=>$model,'em'=> $em,'user'=> $this->getUser()]);

        $modelEntities = $em->getRepository('App\Entity\CeModelEntity')->findBy([
            'modelId' => $model->getId(),
        ]);

        return $this->render('/Admin/Model/model.html.twig', [
            'form' => $form->createView(),
            'modelEntities' => $modelEntities,
            'selected_strategy' => $parentModel,
            'model' => $model,
        ]);
    }

    public function save(Request $request)
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
        $form = $this->createForm(CeModelEntityFormType::class, $modelEntity, ['model'=>$model,'em'=> $em,'user'=> $this->getUser()]);

        $formHandler = new CeModelEntityFormHandler($form, $request, $em, ['model' => $model]);

        if ($formHandler->process()) {
            $newForm =  $this->createForm(CeModelEntityFormType::class, $modelEntity, ['model'=>$model,'em'=> $em,'user'=> $this->getUser()]);

            return $this->json([
                'status' => 'success',
                'form' => $this->renderView('/Admin/Model/_form.html.twig', [
                    'form' => $newForm->createView(),
                    'model' => $model,
                ]),
                'content' => $this->renderView('/Admin/Model/_model_row.html.twig', ['modelEntity' => $modelEntity]),
            ]);
        }

        return $this->json([
            'status' => 'error',
            'form' => $this->renderView('/Admin/Model/_form.html.twig', [
                'form' => $form->createView(),
                'model' => $model,
            ]),
        ]);
    }

    public function updateForm(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $modelManager CeModelManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var CeModel $model */
        $model = $modelManager->findCeModelBySlugAndOwnerId($request->get('modelSlug'));
        if (!$model) {
            return $this->json($result = [
                'status' => 'error',
                'message' => sprintf('Portfolio Model with slug "%s" does not exist.', $request->get('modelSlug')),
            ]);
        }

        $form = $this->createForm(CeModelEntityFormType::class, null, ['model'=>$model,'em'=> $em,'user'=> $this->getUser()]);
        $form->handleRequest($request);

        $result = [
            'status' => 'success',
            'content' => $this->renderView('/Admin/Model/_form_fields.html.twig', ['form' => $form->createView()]),
        ];

        return $this->json($result);
    }

    public function delete($id)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $modelEntity = $em->getRepository('App\Entity\CeModelEntity')->find($id);
        if (!$modelEntity) {
            return $this->json([
                'status' => 'error',
                'message' => 'Model Entity with id: '.$id.' does not exist.',
            ]);
        }

        $em->remove($modelEntity);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    public function edit(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $id = $request->get('id');

        $modelEntity = $em->getRepository('App\Entity\CeModelEntity')->find($id);
        if (!$modelEntity) {
            return $this->json([
                'status' => 'error',
                'message' => 'Model Entity with id: '.$id.' does not exist.',
            ]);
        }

        $model = $modelEntity->getModel();

        $form = $this->createForm(CeModelEntityFormType::class, $modelEntity, ['model'=>$model,'em'=> $em,'user'=> $this->getUser()]);
        $formHandler = new CeModelFormHandler($form, $request, $em);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                $form = $this->createForm(CeModelEntityFormType::class, null, ['model'=>$model,'em'=> $em,'user'=> $this->getUser()]);

                return $this->json([
                    'status' => 'success',
                    'form' => $this->renderView('/Admin/Model/_form.html.twig', [
                        'form' => $form->createView(),
                        'model' => $model,
                    ]),
                    'content' => $this->renderView('/Admin/Model/_model_row.html.twig', ['modelEntity' => $modelEntity]),
                ]);
            }

            return $this->json([
                'status' => 'error',
                'form' => $this->renderView('/Admin/Model/_edit_form.html.twig', [
                    'form' => $form->createView(),
                    'modelEntity' => $modelEntity,
                ]),
            ]);
        }

        return $this->json([
            'status' => 'success',
            'form' => $this->renderView('/Admin/Model/_edit_form.html.twig', [
                'form' => $form->createView(),
                'modelEntity' => $modelEntity,
            ]),
        ]);
    }
}
