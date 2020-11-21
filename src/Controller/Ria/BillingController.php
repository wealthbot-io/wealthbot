<?php

namespace App\Controller\Ria;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\Bill;
use App\Entity\ClientAccount;
use App\Entity\RiaCompanyInformation;
use App\Form\Handler\RiaCompanyInformationTwoFormHandler;
use App\Form\Type\RiaCompanyInformationTwoFormType;
use App\Entity\Profile;
use App\Entity\User;
use PhpOffice\PhpSpreadsheet\Writer as Writer;

class BillingController extends Controller
{
    public function quarterIndex($year, $quarter)
    {
        return $this->forward('App\Entity\Billing:index', [], ['year' => $year, 'quarter' => $quarter]);
    }

    public function index(Request $request)
    {
        $feeManager = $this->get('wealthbot.manager.fee');
        $ria = $this->getUser();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var \App\Repository\RiaCompanyInformationRepository $riaCompanyInfoRepo */
        $riaCompanyInfoRepo = $em->getRepository('App\Entity\RiaCompanyInformation');
        /** @var RiaCompanyInformation $riaCompanyInfo */
        $riaCompanyInfo = $riaCompanyInfoRepo->findOneBy(['ria_user_id' => $ria->getId()]);

        $isPreSave = $request->isXmlHttpRequest();
        $form = $this->createForm(RiaCompanyInformationTwoFormType::class, $riaCompanyInfo, ['user' => $this->getUser(), 'is_pre_save' => $isPreSave]);
        $formHandler = new RiaCompanyInformationTwoFormHandler($form, $request, $em, ['ria' => $ria]);

        $adminFees = $feeManager->getAdminFee($ria);

        if ('POST' === $request->getMethod()) {
            $process = $formHandler->process();

            if ($process) {
                if (!$isPreSave) {
                    $profile = $this->getUser()->getProfile();
                    $profile->setRegistrationStep(3);

                    $em->persist($profile);
                    $em->flush();

                    return $this->redirect($this->generateUrl('rx_ria_profile_step_four'));
                } else {
                    return $this->json(['status' => 'success']);
                }
            } elseif ($isPreSave) {
                return $this->json(['status' => 'error']);
            }
        }

//        return $this->render('/Ria/Profile/step_three.html.twig', array(
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
            'straight_portfolio_processing' => (RiaCompanyInformation::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH === $riaCompanyInfo->getPortfolioProcessing()),
            'years' => $years,
            'riaCreatedAt' => $ria->getCreated()->format(\DateTime::ISO8601),
            'is_relation_type_tamp' => $riaCompanyInfo->isRelationTypeTamp(),
            'active_tab' => $request->get('tab', 'summary'),
        ];
        $currentYear = $request->get('year');
        $currentQuarter = $request->get('quarter');
        if ($currentYear) {
            $data['currentYear'] = $currentYear;
            $data['currentQuarter'] = (null !== $currentQuarter ? $currentQuarter : 1);
        }

        return $this->render('/Ria/Billing/index.html.twig', $data);
    }

    /**
     * This method is returning data for summary tab by date interval.
     *
     * @param $from
     * @param $to
     *
     * @return Response
     */
    public function loadSummaryTab($year, $quarter)
    {
        $riaUser = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $infoManager = $this->get('wealthbot_ria.summary_information.manager');
        $periodManager = $this->get('wealthbot_ria.period.manager');

        $periods = $periodManager->getPeriod($year, $quarter);
        $clients = $em->getRepository('App\Entity\User')->findRiaClientsByDate($riaUser, $periods['endDate']);

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

    public function clientFeePreview(Request $request)
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

        return $this->render('/Ria/Profile/client_fee_preview.html.twig', [
            'is_allow_retirement_plan' => $companyInformation->getIsAllowRetirementPlan(),
            'admin_fees' => $feeManager->getAdminFee($riaUser),
            'ria_fees' => $riaFees,
            'client_fees' => $clientFees,
        ]);
    }

    /**
     * Generate bill action.
     *
     * SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     *
     * @return JsonResponse
     */
    public function generateBill(User $riaClient, $year, $quarter, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $billManager = $this->container->get('wealthbot.manager.bill');

        $content = $request->getContent();
        /** @var ClientAccount[] $accounts */
        $accounts = $em->getRepository('App\Entity\ClientAccount')->getAccountsByIds($riaClient, json_decode($content));

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

    public function generateCustodianFeeFile($year, $quarter, Request $request)
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
     * SecureParam(name="account", permissions="APPROVE_BILL")
     * @ParamConverter("account", class="App\Entity\ClientAccount", options={"id" = "account_id"})
     *
     * @param ClientAccount $account
     * @param $year
     * @param $quarter
     *
     * @return JsonResponse
     */
    public function approveBill(ClientAccount $account, $year, $quarter)
    {
        $billManager = $this->container->get('wealthbot.manager.bill');

        $billManager->approveAccount($account, $year, $quarter, true);

        return new JsonResponse([], 200);
    }

    /**
     * No bill action.
     *
     * SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     *
     * @return JsonResponse
     */
    public function noBill(User $riaClient, $year, $quarter, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $billManager = $this->container->get('wealthbot.manager.bill');

        $content = $request->getContent();
        $accounts = $em->getRepository('App\Entity\ClientAccount')->getAccountsByIds($riaClient, json_decode($content));

        $flush = false;
        foreach ($accounts as $account) {
            if ($billItem = $em->getRepository('App\Entity\BillItem')->getByAccountAndPeriod($account, $year, $quarter)) {
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
    public function summaryReport($year, $quarter)
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

        $writer = new Writer\Xls($excel);

        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="ExportScan.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    /**
     * PDF Bill report action.
     *
     * SecureParam(name="riaClient", permissions="VIEW")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     *
     * @return Response
     */
    public function pdfReport(User $riaClient, $year, $quarter, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $reportMananger = $this->get('wealthbot_ria.billing_report.manager');

        $content = $request->get('content', json_encode([]));
        $accounts = $em->getRepository('App\Entity\ClientAccount')->getAccountsByIds($riaClient, json_decode($content));

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
     * SecureParam(name="riaClient", permissions="VIEW")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     *
     * @return JsonResponse
     */
    public function emailReport(User $riaClient, $year, $quarter, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $mailer = $this->get('wealthbot.mailer');
        $reportMananger = $this->get('wealthbot_ria.billing_report.manager');

        $content = $request->getContent();
        $accounts = $em->getRepository('App\Entity\ClientAccount')->getAccountsByIds($riaClient, json_decode($content));

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
        $clients = $em->getRepository('App\Entity\User')->getRiaClientsByIds($ria, json_decode($content));

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
     * SecureParam(name="account", permissions="CHANGE_CONSOLIDATOR")
     * @ParamConverter("account", class="App\Entity\ClientAccount")
     *
     * @param ClientAccount $account
     * @param Request       $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAccountsPaysFor(ClientAccount $account, Request $request)
    {
        $number = $request->get('paysFor');
        $em = $this->getDoctrine()->getManager();

        if ('' === $number) {
            $account->setConsolidator(null);
        } else {
            $consolidateAccount = $em->getRepository('App\Entity\ClientAccount')->getByAccountNumber($number);
            $ria = $this->getUser();

            if ($consolidateAccount->getClient()->getRia() !== $ria) {
                return $this->createAccessDeniedException('Consolidate account do not belongs to Your RIA');
            }
            if ($consolidateAccount->getClient() !== $account->getClient()) {
                return $this->createAccessDeniedException('Consolidate account do not belongs to client');
            }
            $account->setConsolidator($consolidateAccount);
        }

        $em->flush();

        return new JsonResponse([]);
    }
}
