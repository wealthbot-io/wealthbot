<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Collection\AssetCollection;
use App\Form\Handler\CategoriesFormHandler;
use App\Form\Type\CategoriesFormType;
use App\Repository\AssetClassRepository;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        /** @var $em EntityManager */
        /* @var AssetClassRepository $repo */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\AssetClass');

        $user = $this->getUser();
        $model = $em->getRepository('App\Entity\CeModel')->find($request->get('model_id'));
        $accountTypes = $em->getRepository('App\Entity\SubclassAccountType')->findAll();

        $assetClasses = $repo->findWithSubclassesByModelIdAndOwnerId($model->getId(), null);
        $assets = new AssetCollection($assetClasses, $model);

        $options = [
            'user' => $user,
            'em' => $em,
            'original_assets' => $assetClasses,
            'original_subclasses' => $this->collectOriginalSubclassesForAssets($assetClasses),
        ];

        $form = $this->createForm(CategoriesFormType::class, null, ['em'=>$em,'user'=>$user,'assets'=> $assets]);
        $formHandler = new CategoriesFormHandler($form, $request, $em, $options);

        if ($request->isMethod('post') && $formHandler->process()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['status' => 'success', 'success_url' => $this->generateUrl(
                    'rx_admin_models_index_strategy',
                    ['slug' => $model->getSlug()]
                )]);
            }

            return $this->redirect($this->generateUrl('rx_admin_models_index_strategy', ['slug' => $model->getSlug()]));
        }

        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('/Admin/Categories/index.html.twig', [
                'form' => $form->createView(),
                'is_show_expected_asset' => true,
                'is_show_account_type' => true,
                'is_show_priority' => false,
                'is_show_tolerance_band' => true,
                'account_types' => $accountTypes,
            ]);

            return $this->json(['status' => 'form', 'content' => $content]);
        }

        return $this->render('/Admin/Categories/index.html.twig', [
            'form' => $form->createView(),
            'is_show_expected_asset' => true,
            'is_show_account_type' => true,
            'is_show_priority' => false,
            'is_show_tolerance_band' => true,
            'account_types' => $accountTypes,
        ]);
    }

    public function assetDelete(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $asset = $em->getRepository('App\Entity\AssetClass')->find($request->get('id'));

        if (!$asset) {
            throw $this->createNotFoundException(sprintf('Asset with ID %s does not exist.', $request->get('id')));
        }

        $em->remove($asset);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    public function subclassDelete(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $subclass = $em->getRepository('App\Entity\Subclass')->find($request->get('id'));

        if (!$subclass) {
            throw $this->createNotFoundException(sprintf('Subclass with ID %s does not exist.', $request->get('id')));
        }

        $em->remove($subclass);
        $em->flush();

        return $this->json(['status' => 'success']);
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
}
