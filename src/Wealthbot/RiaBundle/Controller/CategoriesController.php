<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.04.13
 * Time: 12:28
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\AdminBundle\Collection\AssetCollection;
use Wealthbot\AdminBundle\Controller\CategoriesController as BaseController;
use Wealthbot\AdminBundle\Form\Handler\CategoriesFormHandler;
use Wealthbot\AdminBundle\Form\Type\CategoriesFormType;
use Wealthbot\AdminBundle\Repository\AssetClassRepository;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Entity\User;

class CategoriesController extends BaseController
{
    public function indexAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var AssetClassRepository $repo */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotAdminBundle:AssetClass');

        /** @var User $ria */
        $ria = $this->getUser();
        $selectedModel = $ria->getRiaCompanyInformation()->getPortfolioModel();

        $riaCompanyInfo = $ria->getRiaCompanyInformation();
        $isShowExpectedAsset = $riaCompanyInfo->getIsShowClientExpectedAssetClass();
        $isShowAccountType = $this->isShowAccountType($riaCompanyInfo);
        $isShowPriority = false;//$riaCompanyInfo->isShowSubclassPriority();

        $accountTypes = $em->getRepository('WealthbotRiaBundle:SubclassAccountType')->findAll();

        $assetClasses = $repo->findByModelIdAndOwnerId($selectedModel->getId(), $ria->getId());
        //var_dump($selectedModel->getId(), $assetClasses);die;
        $assets = new AssetCollection($assetClasses, $selectedModel);

        $options = [
            'original_assets' => $assetClasses,
            'original_subclasses' => $this->collectOriginalSubclassesForAssets($assetClasses),
        ];

        $form = $this->createForm(new CategoriesFormType($ria, $em), $assets);
        $formHandler = new CategoriesFormHandler($form, $request, $em, $options);

        if ($request->isMethod('post') && $formHandler->process()) {
            if ($request->isXmlHttpRequest()) {
                return $this->getJsonResponse(
                    ['status' => 'success', 'success_url' => $this->generateUrl('rx_ria_dashboard_models_tab', ['tab' => 'categories'])]
                );
            }

            return $this->redirect($this->generateUrl('rx_ria_dashboard_models_tab', ['tab' => 'categories']));
        }

        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('WealthbotRiaBundle:Categories:index.html.twig', [
                'form' => $form->createView(),
                'is_show_expected_asset' => $isShowExpectedAsset,
                'is_show_account_type' => $isShowAccountType,
                'is_show_priority' => $isShowPriority,
                'is_show_tolerance_band' => $riaCompanyInfo->isRebalancedFrequencyToleranceBand(),
                'account_types' => $accountTypes,
            ]);

            return $this->getJsonResponse(['status' => 'form', 'content' => $content]);
        }

        return $this->render('WealthbotRiaBundle:Categories:index.html.twig', [
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
        if ($riaCompanyInfo->getAccountManaged() === 1 && !$riaCompanyInfo->getIsAllowRetirementPlan()) {
            return false;
        }

        return true;
    }
}
