<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.04.13
 * Time: 12:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Ria;

use App\Entity\Subclass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use App\Entity\CeModel;
use App\Entity\Security;
use App\Entity\SecurityAssignment;
use App\Entity\SecurityTransaction;
use App\Form\Handler\ModelSecurityFormHandler;
use App\Form\Type\ModelSecurityFormType;
use App\Form\Type\SecurityTransactionFormType;
use App\Entity\RiaCompanyInformation;
use App\Model\User;

class SecuritiesController extends BaseController
{
    public function modelSecuritiesList(Request $request)
    {
        $ria = $this->getUser();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $riaCompanyInformation RiaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();
        /** @var CeModel $selectedModel */
        $selectedModel = $riaCompanyInformation->getPortfolioModel();
        $request->request->set('model_id', $selectedModel->getId());
        $securityAssignment = new SecurityAssignment();
        $securityAssignment->setModel($selectedModel);

        $form = $this->createForm(ModelSecurityFormType::class, null, ['selected_model' =>  $selectedModel, 'em' => $em,'securityAssignment' =>$securityAssignment]);
        $formHandler = new ModelSecurityFormHandler($form, $request, $em, ['security_assignment' => $securityAssignment, 'selected_model' =>  $selectedModel, 'ria'=> $ria]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                return $this->redirect($this->generateUrl('rx_ria_model_securities_list', ['model_id' => $selectedModel->getId()]));
            }
        }

        $securityAssignments = $em->getRepository('App\Entity\SecurityAssignment')->findBy(['ria_user_id' => $this->getUser()]);


        return $this->render('/Ria/Securities/model_securities_list.html.twig', [
            'form' => $form->createView(),
            'is_show_municipal_bond' => $riaCompanyInformation->getUseMunicipalBond(),
            'security_assignments' => $securityAssignments,
            'is_show_transaction_edit' => $riaCompanyInformation->isShowTransactionEdit(),
        ]);
    }

    public function completeSubclasses(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $this->getUser();

        $assetClass = $em->getRepository("App\\Entity\\AssetClass")->find($request->get('asset_id'));

        if (!$assetClass) {
            throw $this->createNotFoundException(sprintf('AssetClass with id %d does not exist.', $request->get('asset_id')));
        }

        $subclasses = $em->getRepository("App\\Entity\\Subclass")->findBy(['assetClass' => $assetClass]);

        $info[] = [
            'id' => null,
            'name' => 'Choose an Option'
        ];
        foreach ($subclasses as $s) {
            /** @var Subclass $s */
            $info[] =
                ['id'=>$s->getId(),
                'name' => $s->getName()
                ];
        };

        $output = '';

        foreach ($info as $s) {
            $output .= "<option value='" . $s['id'] . "'>" . $s['name'] . "</option>";
        }

        return new Response($output, Response::HTTP_OK);
    }

    public function deleteModelSecurity(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $securityAssignment SecurityAssignment */
        $securityAssignment = $em->getRepository('App\Entity\SecurityAssignment')->find($request->get('id'));

        if (!$securityAssignment) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }
        $this->checkRiaEditSecurityAccess($securityAssignment);

        $model = $securityAssignment->getModel();

        $em->remove($securityAssignment);
        $em->flush();

        return $this->redirect($this->generateUrl('rx_ria_model_securities_list', ['model_id' => $model->getId()]));
    }

    public function editModelSecurity(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $this->getUser();
        /** @var $riaCompanyInformation RiaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();
        /** @var $securityAssignment SecurityAssignment */
        $securityAssignment = $em->getRepository('App\Entity\SecurityAssignment')->find($request->get('id'));

        if (!$securityAssignment) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }
        $this->checkRiaEditSecurityAccess($securityAssignment);

        $model = $securityAssignment->getModel();

        $securityAssignments = $em->getRepository('App\Entity\SecurityAssignment')->findBy(['model_id' => $model->getId()]);

        $form = $this->createForm(ModelSecurityFormType::class, $securityAssignment, [ 'selected_model'=> $model,'em'=> $em]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($securityAssignment);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_ria_model_securities_list', ['model_id' => $model->getId()]));
            }
        }

        return $this->render('/Ria/Securities/model_securities_list_edit.html.twig', [
            'form' => $form->createView(),
            'is_show_municipal_bond' => $riaCompanyInformation->getUseMunicipalBond(),
            'security_assignment' => $securityAssignment,
            'security_assignments' => $securityAssignments,
            'is_show_transaction_edit' => $riaCompanyInformation->isShowTransactionEdit(),
        ]);
    }

    public function editSecurityTransaction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $this->getUser();
        /** @var $riaCompanyInformation RiaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();

        /** @var $securityAssignment SecurityAssignment */
        $securityAssignment = $em->getRepository('App\Entity\SecurityAssignment')->find($request->get('security_id'));
        if (!$securityAssignment || $securityAssignment->getModel()->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException(sprintf('SecurityAssignment with id %d does not exist.', $request->get('id')));
        }

        $form = $this->createForm(SecurityTransactionFormType::class, $securityAssignment->getSecurityTransaction(), [
            'ria' => $riaCompanyInformation
        ]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $securityTransaction SecurityTransaction */
                $securityTransaction = $form->getData();

                $securityTransaction->setSecurityAssignment($securityAssignment);
                $em->persist($securityTransaction);
                $em->flush();

                return $this->json([
                    'status' => 'success',
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'message' => $form->getErrors(),
                    'content' => $this->renderView('/Ria/Securities/security_transaction_edit.html.twig', [
                        'form' => $form->createView(),
                        'security_assignment_id' => $securityAssignment->getId(),
                        'ria_company_information' => $riaCompanyInformation,
                    ]),
                ]);
            }
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Securities/security_transaction_edit.html.twig', [
                'form' => $form->createView(),
                'security_assignment_id' => $securityAssignment->getId(),
                'ria_company_information' => $riaCompanyInformation,
            ]),
        ]);
    }

    public function completeFunds(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $query = $request->get('query');

        $parentModel = $this->getUser()->getRiaCompanyInformation()->getPortfolioModel();

        $securities = $em->getRepository('App\Entity\Security')->findNotAssignedSecurityByModelIdAndSymbol($parentModel->getId(), $query);

        $output = [];

        /** @var Security $security */
        foreach ($securities as $security) {
            $card['id'] = $security->getId();
            $card['display_name'] = $security->getSymbol().' ('.$security->getName().')';
            $card['security_name'] = $security->getName();
            $card['security_id'] = $security->getId();
            $card['name'] = $security->getSymbol();
            $card['expense_ratio'] = $security->getExpenseRatio();
            $card['type'] = $security->getSecurityType()->getDescription();

            $output[] = $card;
        }

        return $this->json($output);
    }

    private function checkRiaEditSecurityAccess($securityAssignment, User $ria = null)
    {
        if (is_null($ria)) {
            $ria = $this->getUser();
        }

        if ($securityAssignment->getSubclass()->getOwnerId() !== $ria->getId()) {
            return $this->createAccessDeniedException();
        }
    }
}
