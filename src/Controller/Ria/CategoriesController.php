<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.04.13
 * Time: 12:28
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Ria;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use App\Collection\AssetCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use App\Form\Handler\CategoriesFormHandler;
use App\Form\Type\CategoriesFormType;
use App\Repository\AssetClassRepository;
use App\Entity\RiaCompanyInformation;
use App\Entity\User;

class CategoriesController extends BaseController
{
    public function index(Request $request)
    {
        /** @var $em EntityManager */
        /* @var AssetClassRepository $repo */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\AssetClass');

        /** @var User $ria */
        $ria = $this->getUser();
        $selectedModel = $ria->getRiaCompanyInformation()->getPortfolioModel();

        $riaCompanyInfo = $ria->getRiaCompanyInformation();
        $isShowExpectedAsset = $riaCompanyInfo->getIsShowClientExpectedAssetClass();
        $isShowAccountType = $this->isShowAccountType($riaCompanyInfo);
        $isShowPriority = false; //$riaCompanyInfo->isShowSubclassPriority();

        $accountTypes = $em->getRepository('App\Entity\SubclassAccountType')->findAll();

        $assetClasses = $repo->findByModelIdAndOwnerId($selectedModel->getId(), $ria->getId());

        $assets = new AssetCollection($assetClasses, $selectedModel);

        $options = [
            'original_assets' => $assetClasses,
          //  'original_subclasses' => $this->collectOriginalSubclassesForAssets($assetClasses),
        ];

        $form = $this->createForm(CategoriesFormType::class, null, ['user' => $this->getUser(), 'ria'=>$ria,'em'=> $em,'assets'=> $assets]);
        $formHandler = new CategoriesFormHandler($form, $request, $em, $options);

        if ($request->isMethod('post') && $formHandler->process()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(
                    ['status' => 'success', 'success_url' => $this->generateUrl('rx_ria_dashboard_models_tab', ['tab' => 'categories'])]
                );
            }

            return $this->redirect($this->generateUrl('rx_ria_dashboard_models_tab', ['tab' => 'categories']));
        }

        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('/Ria/Categories/index.html.twig', [
                'form' => $form->createView(),
                'is_show_expected_asset' => $isShowExpectedAsset,
                'is_show_account_type' => $isShowAccountType,
                'is_show_priority' => $isShowPriority,
                'is_show_tolerance_band' => $riaCompanyInfo->isRebalancedFrequencyToleranceBand(),
                'account_types' => $accountTypes,
            ]);

            return $this->json(['status' => 'form', 'content' => $content]);
        }

        return $this->render('/Ria/Categories/index.html.twig', [
            'form' => $form->createView(),
            'is_show_expected_asset' => $isShowExpectedAsset,
            'is_show_account_type' => $isShowAccountType,
            'is_show_priority' => $isShowPriority,
            'is_show_tolerance_band' => $riaCompanyInfo->isRebalancedFrequencyToleranceBand(),
            'account_types' => $accountTypes,
        ]);
    }

    protected function isShowAccountType(RiaCompanyInformation $riaCompanyInfo)
    {
        if (1 === $riaCompanyInfo->getAccountManaged() && !$riaCompanyInfo->getIsAllowRetirementPlan()) {
            return false;
        }

        return true;
    }
}
