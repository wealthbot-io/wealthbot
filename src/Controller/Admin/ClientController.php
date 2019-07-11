<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Manager\UserHistoryManager;
use App\Entity\AccountGroup;
use App\Repository\ClientAccountRepository;
use App\Repository\UserRepository;

class ClientController extends AclController
{
    public function index(Request $request)
    {
        $page = $request->get('page');
        if (!$page) {
            $page = 1;
        }
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $repository UserRepository */
        $repository = $em->getRepository('App\Entity\User');

        $order = $request->get('direction');
        $sort = $request->get('sort');

        $orderValue = (('desc' === $order) ? 'desc' : 'asc');
        switch ($sort) {
            case 'first_name':
                $sortFiled = 'p.first_name';
                break;
            case 'last_name':
                $sortFiled = 'p.last_name';
                break;
            case 'signed_up':
                $sortFiled = 'cu.created';
                break;
            case 'process_step':
                $sortFiled = 'p.registration_step';
                break;
            case 'ria':
                $sortFiled = 'rp.company';
                break;
            case 'email':
                $sortFiled = 'cu.email';
                break;
            case 'city':
                $sortFiled = 'p.city';
                break;
            case 'state':
                $sortFiled = 'p.state';
                break;
            case 'outside_account':
                $sortFiled = 'nb_funds';
                break;
            case 'last_login':
                $sortFiled = 'cu.lastLogin';
                break;
            default:
                $sortFiled = 'p.first_name';
                break;
        }

        $qb = $repository->createQueryBuilder('cu');
        $clients = $qb->select([
                'cu as user',
                '(SELECT COUNT(ca.id) FROM App\Entity\ClientAccount ca LEFT JOIN ca.groupType gt
                 LEFT JOIN gt.group g WHERE cu.id = ca.client_id AND g.name = :group) as nb_funds',
            ])
            ->leftJoin('cu.profile', 'p')
            ->leftJoin('p.ria', 'r')
            ->leftJoin('r.profile', 'rp')
            ->where('cu.roles LIKE :role')
            ->setParameters([
                'role' => '%"ROLE_CLIENT"%',
                'group' => AccountGroup::GROUP_EMPLOYER_RETIREMENT,
            ])
            ->orderBy($sortFiled, $orderValue)
            ->getQuery()
            ->getResult()
        ;

        /** @var $paginator KnpPaginatorBundle */
        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $clients,
            $page/*page number*/,
            $this->container->getParameter('pager_per_page')/*limit per page*/
        );

        return $this->render('/Admin/Client/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    public function specificDashboard(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository */
        /* @var UserHistoryManager $historyManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $historyManager = $this->get('wealthbot_admin.user_history.manager');

        /** @var $repository UserRepository */
        $repository = $em->getRepository('App\Entity\User');

        $client = $repository->find($request->get('id'));
        if (!$client) {
            throw $this->createNotFoundException('Client does not exist.');
        }

        $paginator = $this->get('knp_paginator');
        $historyPagination = $paginator->paginate(
            $historyManager->findBy(['user_id' => $client->getId()], ['created' => 'DESC']),
            $request->get('history_page', 1),
            $this->container->getParameter('pager_per_page'),
            ['pageParameterName' => 'history_page']
        );

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Admin/Ria/_history.html.twig', ['history_pagination' => $historyPagination]),
            ]);
        }

        $questionnaireAnswers = $em->getRepository('App\Entity\ClientQuestionnaireAnswer')
            ->findBy(['client_id' => $client->getId()]);

        $retirementAccounts = $repo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);

        return $this->render('/Admin/Client/specific_dashboard.html.twig', [
            'client' => $client,
            'questionnaire_answers' => $questionnaireAnswers,
            'retirement_accounts' => $retirementAccounts,
            'history_pagination' => $historyPagination,
        ]);
    }

    public function outsideFunds(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $repo ClientAccountRepository */
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $account = $repo->find($request->get('account_id'));
        if (!$account) {
            return $this->json(['status' => 'error', 'message' => 'Account does not exist.']);
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Admin/Client/_client_settings_accounts_funds_list.html.twig', [
                'account' => $account,
            ]),
        ]);
    }
}
