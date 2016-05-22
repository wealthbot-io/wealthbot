<?php

namespace Wealthbot\AdminBundle\Controller;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Document\Transaction;
use Wealthbot\AdminBundle\Form\FormErrorBag;
use Wealthbot\AdminBundle\Form\Type\TransactionFormType;
use Wealthbot\AdminBundle\PasInterfaces\DataInterface;

class PasInterfacesController extends Controller
{
    public function fileIndexAction(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_ALL_FILES, $request);

        return $this->render('WealthbotAdminBundle:Pas:File/index.html.twig', $params);
    }

    public function securityIndexAction(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_SECURITIES, $request);

        return $this->render('WealthbotAdminBundle:Pas:Security/index.html.twig', $params);
    }

    public function priceIndexAction(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_PRICES, $request);

        return $this->render('WealthbotAdminBundle:Pas:Price/index.html.twig', $params);
    }

    public function accountIndexAction(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_ACCOUNTS, $request);

        return $this->render('WealthbotAdminBundle:Pas:Account/index.html.twig', $params);
    }

    /**
     * Transaction list.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function transactionIndexAction(Request $request)
    {
        $form = $this->createForm(new TransactionFormType(), new Transaction());

        $params = $this->getLoadData(DataInterface::DATA_TYPE_TRANSACTIONS, $request);
        $params['form'] = $form->createView();

        return $this->render('WealthbotAdminBundle:Pas:Transaction/index.html.twig', $params);
    }

    /**
     * Create new transaction.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function transactionCreateAction(Request $request)
    {
        $transaction = new Transaction();

        $form = $this->createForm(new TransactionFormType(), $transaction);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $now = new \DateTime();
            $date = $this->dateFormat($request->get('date'));

            $txDate = $this->dateFormat($transaction->getTxDate(), true);
            $settleDate = $this->dateFormat($transaction->getSettleDate(), true);

            $transaction->setStatus(Transaction::STATUS_NOT_POSTED);
            $transaction->setImportDate($date->format('Y-m-d'));
            $transaction->setCreated($now->format('Y-m-d'));
            $transaction->setTxDate($txDate->format('Y-m-d'));
            $transaction->setSettleDate($settleDate->format('Y-m-d'));

            $user = $this->getUser();
            $user instanceof UserInterface && $transaction->setUsername($user->getUsername());

            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($transaction);
            $dm->flush();

            return new JsonResponse($this->get('wealthbot_admin.pas_interface.transactions')->toArray($transaction));
        } else {
            $errors = new FormErrorBag($form);

            return new Response($errors->toJson(), 400);
        }
    }

    public function positionIndexAction(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_POSITIONS, $request);

        return $this->render('WealthbotAdminBundle:Pas:Position/index.html.twig', $params);
    }

    public function unrealizedGainIndexAction(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_UNREALIZED_GAINS, $request);

        return $this->render('WealthbotAdminBundle:Pas:UnrealizedGain/index.html.twig', $params);
    }

    public function realizedGainIndexAction(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_REALIZED_GAINS, $request);

        return $this->render('WealthbotAdminBundle:Pas:RealizedGain/index.html.twig', $params);
    }

    public function tradeReconIndexAction(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_TRADE_RECON, $request);

        return $this->render('WealthbotAdminBundle:Pas:TradeRecon/index.html.twig', $params);
    }

    public function feeReconIndexAction(Request $request)
    {
        $params['date'] = $this->dateFormat($request->get('date'));
        $params['action'] = DataInterface::DATA_TYPE_FEE_RECON;
        $params['data'] = [];

        return $this->render('WealthbotAdminBundle:Pas:FeeRecon/index.html.twig', $params);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function advisorCodeAutocompleteAction(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('WealthbotRiaBundle:AdvisorCode', $request->query->get('query'), 'name'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function transactionTypeAutocompleteAction(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('WealthbotAdminBundle:TransactionType', $request->query->get('query'), 'name'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function securityAutocompleteAction(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('WealthbotAdminBundle:Security', $request->query->get('query'), 'symbol'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function securityTypeAutocompleteAction(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('WealthbotAdminBundle:SecurityType', $request->query->get('query'), 'name'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function accountNumberAutocompleteAction(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('WealthbotClientBundle:SystemAccount', $request->query->get('query'), 'accountNumber'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function closingMethodAutocompleteAction(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('WealthbotAdminBundle:ClosingMethod', $request->query->get('query'), 'name'));
    }

    /**
     * @param string $repository
     * @param string $query
     * @param string $getter
     *
     * @return array
     */
    protected function getAutocompleteData($repository, $query, $getter)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository($repository)->getAll($query);
        $getter = 'get'.ucfirst($getter);
        $data = [];

        foreach ($result as $row) {
            $data[] = $row->{$getter}();
        }

        return $data;
    }

    protected function getLoadData($action, Request $request)
    {
        $date = $this->dateFormat($request->get('date'));

        $params = $this->get('wealthbot_admin.pas_interface.information')->loadInformation($action, $date, (int) $request->get('page', 1));
        $params['date'] = $date;
        $params['action'] = $action;

        return $params;
    }

    /**
     * @param string $date
     *
     * @return \DateTime
     */
    protected function dateFormat($date, $move = false)
    {
        if ($date === null) {
            $date = new \DateTime();
            $date->setTime(0, 0, 0);

            return $date;
        }

        if ($move === true) {
            list($m, $d, $y) = preg_split('/\//', $date);
            $date = sprintf('%4d-%02d-%02d', $y, $m, $d);
        }

        $date = str_replace('/', '-', $date);
        $date = new \DateTime($date);

        return $date;
    }
}
