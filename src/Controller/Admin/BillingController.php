<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Writer as Writer;

class BillingController extends AclController
{
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ria = $em->getRepository('App\Entity\User')->getFirstRia();

        $date = new \DateTime();
        $fromDate = empty($ria) ? $date : $ria->getCreated();

        $years = range($fromDate->format('Y'), $date->format('Y'));

        return $this->render('/Admin/Billing/index.html.twig', [
            'years' => $years,
            'fromDate' => $fromDate->format(\DateTime::ISO8601),
        ]);
    }

    public function clientList($year, $quarter)
    {
        $em = $this->getDoctrine()->getManager();

        $periodManager = $this->get('wealthbot_ria.period.manager');
        $informationManager = $this->get('wealthbot_ria.summary_information.manager');

        $periods = $periodManager->getPeriod($year, $quarter);
        $clients = $em->getRepository('App\Entity\User')->getAllClientsByDate($periods['endDate']);

        $clientAccounts = [];
        foreach ($clients as $client) {
            $riaName = $client->getRia()->getFullName();
            $accounts = $em->getRepository('App\Entity\ClientAccount')->findByClient($client);

            foreach ($accounts as $account) {
                $billItem = $em->getRepository('App\Entity\BillItem')->getByAccountAndPeriod($account, $year, $quarter);

                $data = [
                    'id' => $account->getId(),
                    'name' => $account->getOwnerNames(),
                    'riaName' => $riaName,
                    'number' => $informationManager->getAccountNumber($account),
                    'billItemId' => $billItem ? $billItem->getId() : 0,
                    'feeBilled' => $billItem ? $billItem->getFeeBilled() : 0,
                    'feeCollected' => $billItem ? $billItem->getFeeCollected() : 0,
                    'riaFee' => $billItem ? $billItem->getRiaFee() : 0,
                    'adminFee' => $billItem ? $billItem->getAdminFee() : 0,
                ];

                $clientAccounts[] = $data;
            }
        }

        return $this->json($clientAccounts);
    }

    public function custodianFeeFile($year, $quarter)
    {
        $container = $this->container;
        $response = new StreamedResponse(function () use ($container, $quarter, $year) {
            $em = $container->get('doctrine')->getManager();
            $periodManager = $container->get('wealthbot_ria.period.manager');
            $informationManager = $container->get('wealthbot_ria.summary_information.manager');

            $periods = $periodManager->getPeriod($year, $quarter);
            $clients = $em->getRepository('App\Entity\User')->getAllClientsByDate($periods['endDate']);

            $handle = fopen('php://output', 'r+');

            foreach ($clients as $client) {
                $accounts = $em->getRepository('App\Entity\ClientAccount')->findByClient($client);

                foreach ($accounts as $account) {
                    $billItem = $em->getRepository('App\Entity\BillItem')->getByAccountAndPeriod($account, $year, $quarter);

                    $fields = [$informationManager->getAccountNumber($account), 'Q', empty($billItem) ? 0 : $billItem->getFeeBilled()];

                    fputcsv($handle, $fields, ',');
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="fees.csv"');

        return $response;
    }

    public function summaryReport($year, $quarter)
    {
        $periodMananger = $this->get('wealthbot_ria.period.manager');
        $reportMananger = $this->get('wealthbot_ria.billing_report.manager');

        // Generate report
        $periods = $periodMananger->getQuarterPeriod($year, $quarter);
        $excel = $reportMananger->generateSummary($periods);

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
}
