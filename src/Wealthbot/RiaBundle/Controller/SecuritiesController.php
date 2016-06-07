<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.04.13
 * Time: 12:05
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wealthbot\AdminBundle\Controller\SecuritiesController as BaseController;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Entity\SecurityTransaction;
use Wealthbot\AdminBundle\Form\Handler\ModelSecurityFormHandler;
use Wealthbot\AdminBundle\Form\Type\ModelSecurityFormType;
use Wealthbot\AdminBundle\Form\Type\SecurityTransactionFormType;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Model\User;

class SecuritiesController extends BaseController
{
    public function modelSecuritiesListAction(Request $request)
    {
        $ria = $this->getUser();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $riaCompanyInformation RiaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();
        /** @var CeModel $selectedModel */
        $selectedModel = $riaCompanyInformation->getPortfolioModel();
        $request->request->set('model_id', $selectedModel->getId());

        $securityAssignments = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findBy(['model_id' => $selectedModel->getId()]);

        $securityAssignment = new SecurityAssignment();
        $securityAssignment->setModel($selectedModel);

        $form = $this->createForm(new ModelSecurityFormType($selectedModel, $em), $securityAssignment);
        $formHandler = new ModelSecurityFormHandler($form, $request, $em, ['security_assignment' => $securityAssignment]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                return $this->redirect($this->generateUrl('rx_ria_model_securities_list', ['model_id' => $selectedModel->getId()]));
            }
        }

        return $this->render('WealthbotRiaBundle:Securities:model_securities_list.html.twig', [
            'form' => $form->createView(),
            'is_show_municipal_bond' => $riaCompanyInformation->getUseMunicipalBond(),
            'security_assignments' => $securityAssignments,
            'is_show_transaction_edit' => $riaCompanyInformation->isShowTransactionEdit(),
        ]);
    }

    public function completeSubclassesAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $this->getUser();

        $assetClass = $em->getRepository('WealthbotAdminBundle:AssetClass')->find($request->get('asset_id'));

        if (!$assetClass) {
            throw $this->createNotFoundException(sprintf('AssetClass with id %d does not exist.', $request->get('asset_id')));
        }

        $subclasses = $em->getRepository('WealthbotAdminBundle:Subclass')->findBy(['asset_class_id' => $assetClass->getId(), 'owner_id' => $ria->getId()]);

        $output = "<option value=''>Choose an Option</option>";
        foreach ($subclasses as $subclass) {
            $output .= "<option value='".$subclass->getId()."'>".$subclass->getName().'</option>';
        }

        return new Response($output);
    }

    public function deleteModelSecurityAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $securityAssignment SecurityAssignment */
        $securityAssignment = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->find($request->get('id'));

        if (!$securityAssignment) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }
        $this->checkRiaEditSecurityAccess($securityAssignment);

        $model = $securityAssignment->getModel();

        $em->remove($securityAssignment);
        $em->flush();

        return $this->redirect($this->generateUrl('rx_ria_model_securities_list', ['model_id' => $model->getId()]));
    }

    public function editModelSecurityAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $this->getUser();
        /** @var $riaCompanyInformation RiaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();
        /** @var $securityAssignment SecurityAssignment */
        $securityAssignment = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->find($request->get('id'));

        if (!$securityAssignment) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }
        $this->checkRiaEditSecurityAccess($securityAssignment);

        $model = $securityAssignment->getModel();

        $securityAssignments = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findBy(['model_id' => $model->getId()]);

        $form = $this->createForm(new ModelSecurityFormType($model, $em), $securityAssignment);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($securityAssignment);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_ria_model_securities_list', ['model_id' => $model->getId()]));
            }
        }

        return $this->render('WealthbotRiaBundle:Securities:model_securities_list_edit.html.twig', [
            'form' => $form->createView(),
            'is_show_municipal_bond' => $riaCompanyInformation->getUseMunicipalBond(),
            'security_assignment' => $securityAssignment,
            'security_assignments' => $securityAssignments,
            'is_show_transaction_edit' => $riaCompanyInformation->isShowTransactionEdit(),
        ]);
    }

    public function editSecurityTransactionAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $this->getUser();
        /** @var $riaCompanyInformation RiaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();

        /** @var $securityAssignment SecurityAssignment */
        $securityAssignment = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->find($request->get('security_id'));
        if (!$securityAssignment || $securityAssignment->getModel()->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }

        $form = $this->createForm(new SecurityTransactionFormType($riaCompanyInformation), $securityAssignment->getSecurityTransaction());

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $securityTransaction SecurityTransaction */
                $securityTransaction = $form->getData();

                $securityTransaction->setSecurityAssignment($securityAssignment);
                $em->persist($securityTransaction);
                $em->flush();

                return $this->getJsonResponse([
                    'status' => 'success',
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'message' => $form->getErrors(),
                    'content' => $this->renderView('WealthbotRiaBundle:Securities:security_transaction_edit.html.twig', [
                        'form' => $form->createView(),
                        'security_assignment_id' => $securityAssignment->getId(),
                        'ria_company_information' => $riaCompanyInformation,
                    ]),
                ]);
            }
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotRiaBundle:Securities:security_transaction_edit.html.twig', [
                'form' => $form->createView(),
                'security_assignment_id' => $securityAssignment->getId(),
                'ria_company_information' => $riaCompanyInformation,
            ]),
        ]);
    }

    public function completeFundsAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $query = $request->get('query');

        $parentModel = $this->getUser()->getRiaCompanyInformation()->getPortfolioModel();

        $securities = $em->getRepository('WealthbotAdminBundle:Security')->findNotAssignedSecurityByModelIdAndSymbol($parentModel->getId(), $query);

        $output = [];

        /** @var Security $security */
        foreach ($securities as $security) {
            $card['id'] = $security->getId();
            $card['display_name'] = $security->getSymbol().' ('.$security->getName().')';
            $card['security_name'] = $security->getName();
            $card['name'] = $security->getSymbol();
            $card['expense_ratio'] = $security->getExpenseRatio();
            $card['type'] = $security->getSecurityType()->getDescription();

            $output[] = $card;
        }

        return $this->getJsonResponse($output);
    }

    private function checkRiaEditSecurityAccess($securityAssignment, User $ria = null)
    {
        if (is_null($ria)) {
            $ria = $this->getUser();
        }

        if ($securityAssignment->getSubclass()->getOwnerId() !== $ria->getId()) {
            throw new AccessDeniedException();
        }
    }
}
