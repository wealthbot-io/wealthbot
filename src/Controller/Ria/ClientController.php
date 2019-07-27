<?php

namespace App\Controller\Ria;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\Handler\ClientSasCashCollectionFormHandler;
use App\Form\Type\ClientSasCashCollectionFormType;

class ClientController extends Controller
{
    public function updateSasCash(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $workflowManager = $this->get('wealthbot.workflow.manager');

        $clientId = $request->get('client_id');
        $client = $em->getRepository('App\Entity\User')->find($clientId);

        if (!$client || $client->getRia() !== $this->getUser()) {
            return $this->createNotFoundException('client with id '.$clientId.' not found');
        }

        $accountValues = $systemAccountManager->getClientAccountsValues($client);
        $initRebalanceWorkflow = $workflowManager->findNotCompletedInitRebalanceWorkflow($client);
        if ($initRebalanceWorkflow) {
            $isInitRebalance = $systemAccountManager->isClientAccountsHaveInitRebalanceStatus($client);
        } else {
            $isInitRebalance = true;
        }


        $sasCashForm = $this->createForm(ClientSasCashCollectionFormType::class, null, [
            'client' => $client,
            'systemAccounts' => $systemAccountManager->getAccountsForClient($client)
        ]);

        $sasCashFormHandler = new ClientSasCashCollectionFormHandler($sasCashForm, $request, $em);

        return $this->json([
            'status' => $sasCashFormHandler->process() ? 'success' : 'error',
            'content' => $this->renderView('/Client/Dashboard/_index_content.html.twig', [
                'sas_cash_form' => $sasCashForm->createView(),
                'client' => $client,
                'is_client_view' => false,
                'account_values' => $accountValues,
                'is_init_rebalance' => $isInitRebalance,
                'client_portfolio_values_information' => $clientPortfolioValuesManager->prepareClientPortfolioValuesInformation($client),
            ]),
        ]);
    }
}
