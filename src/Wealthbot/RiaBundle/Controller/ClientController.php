<?php

namespace Wealthbot\RiaBundle\Controller;

use JMS\Serializer\SerializationContext;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\ClientBundle\Repository\ClientAccountRepository;
use Wealthbot\RiaBundle\Form\Handler\ClientSasCashCollectionFormHandler;
use Wealthbot\RiaBundle\Form\Handler\CreateClientFormHandler;
use Wealthbot\RiaBundle\Form\Handler\RiaClientAccountFormHandler;
use Wealthbot\RiaBundle\Form\Type\ClientSasCashCollectionFormType;
use Wealthbot\RiaBundle\Form\Type\CreateClientType;
use Wealthbot\RiaBundle\Form\Type\RiaClientAccountFormType;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\UserBundle\Entity\Profile;

class ClientController extends Controller
{
    public function updateSasCashAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $workflowManager = $this->get('wealthbot.workflow.manager');

        $clientId = $request->get('client_id');
        $client = $em->getRepository('WealthbotUserBundle:User')->find($clientId);

        if (!$client || $client->getRia() != $this->getUser()) {
            return $this->createNotFoundException('client with id '.$clientId.' not found');
        }

        $accountValues = $systemAccountManager->getClientAccountsValues($client);
        $initRebalanceWorkflow = $workflowManager->findNotCompletedInitRebalanceWorkflow($client);
        if ($initRebalanceWorkflow) {
            $isInitRebalance = $systemAccountManager->isClientAccountsHaveInitRebalanceStatus($client);
        } else {
            $isInitRebalance = true;
        }

        $sasCashForm = $this->createForm(new ClientSasCashCollectionFormType($client));
        $sasCashFormHandler = new ClientSasCashCollectionFormHandler($sasCashForm, $request, $em);

        return $this->getJsonResponse(array(
            'status' => $sasCashFormHandler->process() ? 'success' : 'error',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_index_content.html.twig', array(
                'sas_cash_form' => $sasCashForm->createView(),
                'client' => $client,
                'is_client_view' => false,
                'account_values' => $accountValues,
                'is_init_rebalance' => $isInitRebalance,
                'client_portfolio_values_information' => $clientPortfolioValuesManager->prepareClientPortfolioValuesInformation($client)
            ))
        ));
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, array('Content-Type'=> 'application/json'));
    }
}
