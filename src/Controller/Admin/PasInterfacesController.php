<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\FormErrorBag;
use App\Form\Type\TransactionFormType;
use App\PasInterfaces\DataInterface;

class PasInterfacesController extends Controller
{
    public function fileIndex(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_ALL_FILES, $request);

        return $this->render('Admin/Pas/File/index.html.twig', $params);
    }

    public function securityIndex(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_SECURITIES, $request);

        return $this->render('Admin/Pas/Security/index.html.twig', $params);
    }

    public function priceIndex(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_PRICES, $request);

        return $this->render('Admin/Pas/Price/index.html.twig', $params);
    }

    public function accountIndex(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_ACCOUNTS, $request);

        return $this->render('Admin/Pas/Account/index.html.twig', $params);
    }

    /**
     * Transaction list.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function transactionIndex(Request $request)
    {
        $form = $this->createForm(TransactionFormType::class, new Transaction());

        $params = $this->getLoadData(DataInterface::DATA_TYPE_TRANSACTIONS, $request);
        $params['form'] = $form->createView();

        return $this->render('Admin/Pas/Transaction/index.html.twig', $params);
    }

    /**
     * Create new transaction.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function transactionCreate(Request $request)
    {
        $transaction = new Transaction();

        $form = $this->createForm(TransactionFormType::class, $transaction);
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

    public function positionIndex(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_POSITIONS, $request);

        return $this->render('Admin/Pas/Position/index.html.twig', $params);
    }

    public function unrealizedGainIndex(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_UNREALIZED_GAINS, $request);

        return $this->render('Admin/Pas/UnrealizedGain/index.html.twig', $params);
    }

    public function realizedGainIndex(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_REALIZED_GAINS, $request);

        return $this->render('Admin/Pas/RealizedGain/index.html.twig', $params);
    }

    public function tradeReconIndex(Request $request)
    {
        $params = $this->getLoadData(DataInterface::DATA_TYPE_TRADE_RECON, $request);

        return $this->render('Admin/Pas/TradeRecon/index.html.twig', $params);
    }

    public function feeReconIndex(Request $request)
    {
        $params['date'] = $this->dateFormat($request->get('date'));
        $params['action'] = DataInterface::DATA_TYPE_FEE_RECON;
        $params['data'] = [];

        return $this->render('Admin/Pas/FeeRecon/index.html.twig', $params);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function advisorCodeAutocomplete(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('App\Entity\AdvisorCode', $request->query->get('query'), 'name'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function transactionTypeAutocomplete(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('App\Entity\TransactionType', $request->query->get('query'), 'name'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function securityAutocomplete(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('App\Entity\Security', $request->query->get('query'), 'symbol'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function securityTypeAutocomplete(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('App\Entity\SecurityType', $request->query->get('query'), 'name'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function accountNumberAutocomplete(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('App\Entity\SystemAccount', $request->query->get('query'), 'accountNumber'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function closingMethodAutocomplete(Request $request)
    {
        return new JsonResponse($this->getAutocompleteData('App\Entity\ClosingMethod', $request->query->get('query'), 'name'));
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
        if (null === $date) {
            $date = new \DateTime();
            $date->setTime(0, 0, 0);

            return $date;
        }

        if (true === $move) {
            list($m, $d, $y) = preg_split('/\//', $date);
            $date = sprintf('%4d-%02d-%02d', $y, $m, $d);
        }

        $date = str_replace('/', '-', $date);
        $date = new \DateTime($date);

        return $date;
    }
}
