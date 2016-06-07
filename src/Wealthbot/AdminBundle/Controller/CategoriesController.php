<?php

namespace Wealthbot\AdminBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Collection\AssetCollection;
use Wealthbot\AdminBundle\Entity\AssetClass;
use Wealthbot\AdminBundle\Form\Handler\CategoriesFormHandler;
use Wealthbot\AdminBundle\Form\Type\CategoriesFormType;
use Wealthbot\AdminBundle\Repository\AssetClassRepository;

class CategoriesController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var AssetClassRepository $repo */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotAdminBundle:AssetClass');

        $user = $this->getUser();
        $model = $em->getRepository('WealthbotAdminBundle:CeModel')->find($request->get('model_id'));
        $accountTypes = $em->getRepository('WealthbotRiaBundle:SubclassAccountType')->findAll();

        $assetClasses = $repo->findWithSubclassesByModelIdAndOwnerId($model->getId(), null);
        $assets = new AssetCollection($assetClasses, $model);

        $options = [
            'original_assets' => $assetClasses,
            'original_subclasses' => $this->collectOriginalSubclassesForAssets($assetClasses),
        ];

        $form = $this->createForm(new CategoriesFormType($user, $em), $assets);
        $formHandler = new CategoriesFormHandler($form, $request, $em, $options);

        if ($request->isMethod('post') && $formHandler->process()) {
            if ($request->isXmlHttpRequest()) {
                return $this->getJsonResponse(['status' => 'success', 'success_url' => $this->generateUrl(
                    'rx_admin_models_index_strategy',
                    ['slug' => $model->getSlug()]
                )]);
            }

            return $this->redirect($this->generateUrl('rx_admin_models_index_strategy', ['slug' => $model->getSlug()]));
        }

        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('WealthbotAdminBundle:Categories:index.html.twig', [
                'form' => $form->createView(),
                'is_show_expected_asset' => true,
                'is_show_account_type' => true,
                'is_show_priority' => false,
                'account_types' => $accountTypes,
            ]);

            return $this->getJsonResponse(['status' => 'form', 'content' => $content]);
        }

        return $this->render('WealthbotAdminBundle:Categories:index.html.twig', [
            'form' => $form->createView(),
            'is_show_expected_asset' => true,
            'is_show_account_type' => true,
            'is_show_priority' => false,
            'account_types' => $accountTypes,
        ]);
    }

    public function assetDeleteAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $asset = $em->getRepository('WealthbotAdminBundle:AssetClass')->find($request->get('id'));

        if (!$asset) {
            throw $this->createNotFoundException(sprintf('Asset with ID %s does not exist.', $request->get('id')));
        }

        $em->remove($asset);
        $em->flush();

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function subclassDeleteAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $subclass = $em->getRepository('WealthbotAdminBundle:Subclass')->find($request->get('id'));

        if (!$subclass) {
            throw $this->createNotFoundException(sprinf('Subclass with ID %s does not exist.', $request->get('id')));
        }

        $em->remove($subclass);
        $em->flush();

        return $this->getJsonResponse(['status' => 'success']);
    }

    protected function collectOriginalSubclassesForAssets($assetClasses)
    {
        $originalSubclasses = [];
        foreach ($assetClasses as $asset) {
            $records = [];
            foreach ($asset->getSubclasses() as $subclass) {
                $records[$subclass->getId()] = $subclass;
            }
            $originalSubclasses[$asset->getId()] = $records;
        }

        return $originalSubclasses;
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
