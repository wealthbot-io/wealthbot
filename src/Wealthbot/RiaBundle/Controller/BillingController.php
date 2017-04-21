<?php

namespace Wealthbot\RiaBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wealthbot\ClientBundle\Entity\Bill;
use Wealthbot\ClientBundle\Entity\BillItem;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\RiaBundle\Form\Handler\RiaCompanyInformationTwoFormHandler;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationTwoFormType;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Entity\User;

class BillingController extends Controller
{
    public function quarterIndexAction($year, $quarter)
    {
        return $this->forward('WealthbotRiaBundle:Billing:index', [], ['year' => $year, 'quarter' => $quarter]);
    }

    public function indexAction(Request $request)
    {
        $feeManager = $this->get('wealthbot.manager.fee');
        $ria = $this->getUser();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var \Wealthbot\RiaBundle\Repository\RiaCompanyInformationRepository $riaCompanyInfoRepo */
        $riaCompanyInfoRepo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation');
        /** @var RiaCompanyInformation $riaCompanyInfo */
        $riaCompanyInfo = $riaCompanyInfoRepo->findOneBy(['ria_user_id' => $ria->getId()]);

        $isPreSave = $request->isXmlHttpRequest();
        $form = $this->createForm(new RiaCompanyInformationTwoFormType($this->getUser(), $isPreSave), $riaCompanyInfo);
        $formHandler = new RiaCompanyInformationTwoFormHandler($form, $request, $em, ['ria' => $ria]);

        $adminFees = $feeManager->getAdminFee($ria);

        if ($request->getMethod() === 'POST') {
            $process = $formHandler->process();

            if ($process) {
                if (!$isPreSave) {
                    $profile = $this->getUser()->getProfile();
                    $profile->setRegistrationStep(3);

                    $em->persist($profile);
                    $em->flush();

                    return $this->redirect($this->generateUrl('rx_ria_profile_step_four'));
                } else {
                    return $this->getJsonResponse(['status' => 'success']);
                }
            } elseif ($isPreSave) {
                return $this->getJsonResponse(['status' => 'error']);
            }
        }

//        return $this->render('WealthbotRiaBundle:Profile:step_three.html.twig', array(
//            'form' => $form->createView(),
//            'admin_fees' => $adminFees
//        ));

        /* @var $ria User */
        $ria = $this->getUser();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $date = new \DateTime();
        $yearFrom = intval($ria->getCreated()->format('Y'));
        $yearNow = intval($date->format('Y'));
        $years = range($yearFrom, $yearNow);

        $data = [
            'form' => $form->createView(),
            'admin_fees' => $adminFees,
            'csrf' => $this->get('security.csrf.token_manager')->getToken('unknown')->getValue(),
            'straight_portfolio_processing' => ($riaCompanyInfo->getPortfolioProcessing() === RiaCompanyInformation::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH),
            'years' => $years,
            'riaCreatedAt' => $ria->getCreated()->format(\DateTime::ISO8601),
            'is_relation_type_tamp' => $riaCompanyInfo->isRelationTypeTamp(),
            'active_tab' => $request->get('tab', 'summary'),
        ];
        $currentYear = $request->get('year');
        $currentQuarter = $request->get('quarter');
        if ($currentYear) {
            $data['currentYear'] = $currentYear;
            $data['currentQuarter'] = ($currentQuarter !== null ? $currentQuarter : 1);
        }

        return $this->render('WealthbotRiaBundle:Billing:index.html.twig', $data);
    }

    /**
     * This method is returning data for summary tab by date interval.
     *
     * @param $from
     * @param $to
     *
     * @return Response
     */
    public function loadSummaryTabAction($year, $quarter)
    {
        $riaUser = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $infoManager = $this->get('wealthbot_ria.summary_information.manager');
        $periodManager = $this->get('wealthbot_ria.period.manager');

        $periods = $periodManager->getPeriod($year, $quarter);
        $clients = $em->getRepository('WealthbotUserBundle:User')->findRiaClientsByDate($riaUser, $periods['endDate']);

        $clientInfo = [];
        foreach ($clients as $client) {
            $data = $infoManager->getClientInformation($client, $year, $quarter);
            $data['accounts'] = $infoManager->getAccountsInformationByClient($client, $year, $quarter);
            $clientInfo[] = $data;
        }

        return new JsonResponse([
            'graphs' => $infoManager->getGraphData($clients, $year, $quarter),
            'clients' => $clientInfo,
        ]);
    }

    public function clientFeePreviewAction(Request $request)
    {
        $feeManager = $this->get('wealthbot.manager.fee');
        $riaUser = $this->getUser();

        /** @var $companyInformation RiaCompanyInformation */
        $companyInformation = $riaUser->getRiaCompanyInformation();

        $riaFees = $request->get('fees');

        foreach ($riaFees as $key => $fee) {
            $last = $key;
        }
        $riaFees[$last]['tier_top'] = 1000000000000;

        $clientFees = $feeManager->getClientFees($riaUser, $riaFees);

        return $this->render('WealthbotRiaBundle:Profile:client_fee_preview.html.twig', [
            'is_allow_retirement_plan' => $companyInformation->getIsAllowRetirementPlan(),
            'admin_fees' => $feeManager->getAdminFee($riaUser),
            'ria_fees' => $riaFees,
            'client_fees' => $clientFees,
        ]);
    }

    /**
     * Generate bill action.
     *
     * @SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     *
     * @return JsonResponse
     */
    public function generateBillAction(User $riaClient, $year, $quarter, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $billManager = $this->container->get('wealthbot.manager.bill');

        $content = $request->getContent();
        /** @var ClientAccount[] $accounts */
        $accounts = $em->getRepository('WealthbotClientBundle:ClientAccount')->getAccountsByIds($riaClient, json_decode($content));

        $flush = false;

        if (count($accounts) > 0) {
            $bill = $billManager->prepareBill($accounts[0]->getClient(), $year, $quarter);
            foreach ($accounts as $account) {
                $billManager->generateBill($account, $bill);
                $flush = true;
            }
        }

        $flush && $em->flush();

        return new JsonResponse([], 200);
    }

    public function generateCustodianFeeFileAction($year, $quarter, Request $request)
    {
        $selectedAccounts = $request->get('selectedAccounts');
        $reportManager = $this->get('wealthbot_ria.billing_report.manager');

        try {
            $accounts = $reportManager->getAccountsForCustodianFeeFile($this->getUser(), $selectedAccounts, $year, $quarter);
        } catch (\Exception $e) {
            //Todo: make new listener for Json exceptions.
            return new JsonResponse(['status' => $e->getMessage()], 400);
        }

        if ($request->isMethod('post')) {
            return new JsonResponse(['status' => 'success'], 200);
        }

        $csvFile = $reportManager->getCustodianFeeFile($accounts, $year, $quarter);

        $fileName = 'billing_custodian_fee_file_q'.$quarter.'_'.$year.'.csv';
        $response = new Response($csvFile, 200);
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-disposition', 'attachment;filename='.$fileName);

        return $response;
    }

    /**
     * @SecureParam(name="account", permissions="APPROVE_BILL")
     * @ParamConverter("account", class="WealthbotClientBundle:ClientAccount", options={"id" = "account_id"})
     *
     * @param ClientAccount $account
     * @param $year
     * @param $quarter
     *
     * @return JsonResponse
     */
    public function approveBillAction(ClientAccount $account, $year, $quarter)
    {
        $billManager = $this->container->get('wealthbot.manager.bill');

        $billManager->approveAccount($account, $year, $quarter, true);

        return new JsonResponse([], 200);
    }

    /**
     * No bill action.
     *
     * @SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     *
     * @return JsonResponse
     */
    public function noBillAction(User $riaClient, $year, $quarter, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $billManager = $this->container->get('wealthbot.manager.bill');

        $content = $request->getContent();
        $accounts = $em->getRepository('WealthbotClientBundle:ClientAccount')->getAccountsByIds($riaClient, json_decode($content));

        $flush = false;
        foreach ($accounts as $account) {
            if ($billItem = $em->getRepository('WealthbotClientBundle:BillItem')->getByAccountAndPeriod($account, $year, $quarter)) {
                $billManager->setNoBill($billItem);
                $flush = true;
            }
        }

        if ($flush) {
            $em->flush();
        }

        return new JsonResponse(['status' => 'success']);
    }

    /**
     * Generate billing summary report action.
     *
     * @return Response
     */
    public function summaryReportAction($year, $quarter)
    {
        $periodMananger = $this->get('wealthbot_ria.period.manager');
        $reportMananger = $this->get('wealthbot_ria.billing_report.manager');

        // Generate report
        $ria = $this->getUser();
        $periods = $periodMananger->getQuarterPeriod($year, $quarter);
        $excel = $reportMananger->generateSummary($ria, $periods);

        // Generate filename
        $periods = array_keys($periods);
        $quarter = empty($quarter) ? min($periods).'-'.max($periods) : $quarter;
        $filename = "billing_summary_q{$quarter}_{$year}";

        $response = new Response();
        $response->headers->add([
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="'.$filename.'.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);

        ob_start();

        $writer = new \PHPExcel_Writer_Excel2007($excel);
        $writer->save('php://output');

        $response->setContent(ob_get_clean());

        return $response;
    }

    /**
     * PDF Bill report action.
     *
     * @SecureParam(name="riaClient", permissions="VIEW")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     *
     * @return Response
     */
    public function pdfReportAction(User $riaClient, $year, $quarter, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $reportMananger = $this->get('wealthbot_ria.billing_report.manager');

        $content = $request->get('content', json_encode([]));
        $accounts = $em->getRepository('WealthbotClientBundle:ClientAccount')->getAccountsByIds($riaClient, json_decode($content));

        // Generate PDF bill report
        $html = $reportMananger->generateBillPdf($this->getUser(), $riaClient, $accounts, $year, $quarter);

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Email Bill report action.
     *
     * @SecureParam(name="riaClient", permissions="VIEW")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     *
     * @return JsonResponse
     */
    public function emailReportAction(User $riaClient, $year, $quarter, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $mailer = $this->get('wealthbot_ria.mailer');
        $reportMananger = $this->get('wealthbot_ria.billing_report.manager');

        $content = $request->getContent();
        $accounts = $em->getRepository('WealthbotClientBundle:ClientAccount')->getAccountsByIds($riaClient, json_decode($content));

        list($accounts, $total) = $reportMananger->getAccountsInfo($accounts, $year, $quarter);

        $result = $mailer->sendEmailBillMessage([
            'ria' => $this->getUser(),
            'client' => $riaClient,
            'feeTotal' => $total,
            'clientAccounts' => $accounts,
            'year' => $year,
            'quarter' => $quarter,
        ]);

        /*
        $content = $request->getContent();
        $clients = $em->getRepository('WealthbotUserBundle:User')->getRiaClientsByIds($ria, json_decode($content));

        foreach ($clients as $client) {
            list($clientAccounts, $feeTotal) = $reportMananger->getClientAccounts($client, $year, $quarter);

            $result = $mailer->sendEmailBillMessage(array(
                'ria'            => $ria,
                'client'         => $client,
                'feeTotal'       => $feeTotal,
                'clientAccounts' => $clientAccounts,
                'year'           => $year,
                'quarter'        => $quarter
            ));
        }
        */

        $clientEmail = $riaClient->getEmail();

        return new JsonResponse(['message' => $result ? "Email for client [{$clientEmail}] has been send." : 'An error occurred when sending email'], $result ? 200 : 500);
    }

    /**
     * @SecureParam(name="account", permissions="CHANGE_CONSOLIDATOR")
     * @ParamConverter("account", class="WealthbotClientBundle:ClientAccount")
     *
     * @param ClientAccount $account
     * @param Request       $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAccountsPaysForAction(ClientAccount $account, Request $request)
    {
        $number = $request->get('paysFor');
        $em = $this->getDoctrine()->getManager();

        if ($number === '') {
            $account->setConsolidator(null);
        } else {
            $consolidateAccount = $em->getRepository('WealthbotClientBundle:ClientAccount')->getByAccountNumber($number);
            $ria = $this->getUser();

            if ($consolidateAccount->getClient()->getRia() !== $ria) {
                throw new AccessDeniedException('Consolidate account do not belongs to Your RIA');
            }
            if ($consolidateAccount->getClient() !== $account->getClient()) {
                throw new AccessDeniedException('Consolidate account do not belongs to client');
            }
            $account->setConsolidator($consolidateAccount);
        }

        $em->flush();

        return new JsonResponse([]);
    }
}
