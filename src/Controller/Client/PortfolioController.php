<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 08.11.12
 * Time: 16:58
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Client;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Event\ClientEvents;
use App\Entity\ClientAccount;
use App\Entity\Workflow;
use App\Event\WorkflowEvent;
use App\Manager\PortfolioInformationManager;
use App\Model\AccountGroup;
use App\Repository\ClientAccountRepository;
use App\Entity\RiaCompanyInformation;
use App\Entity\Document;
use App\Entity\User;

class PortfolioController extends Controller
{
    use AclController;

    public function index(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $accountsRepo = $em->getRepository('App\Entity\ClientAccount');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        $workflowManager = $this->get('wealthbot.workflow.manager');

        /** @var $client User */
        $client = $this->getUser();
        $ria = $client->getRia();

        $clientPortfolio = $clientPortfolioManager->getCurrentPortfolio($client);

        if (!$clientPortfolio) {
            $clientPortfolio = $clientPortfolioManager->getActivePortfolio($client);
        }

        if (!$clientPortfolio) {
            throw $this->createNotFoundException();
        }

        /** @var $companyInformation RiaCompanyInformation */
        $companyInformation = $ria->getRiaCompanyInformation();
        $portfolio = $clientPortfolio->getPortfolio();
        $isQualified = $this->manageQualified($companyInformation, $request->get('is_qualified'));
        $isFinal = false;

        // If client has final portfolio
        if ($clientPortfolio->isAdvisorApproved()) {
            $isFinal = true;
            if ($client->getRegistrationStep() < 4) {
                $client->getProfile()->setRegistrationStep(4);
                $em->persist($client);
                $em->flush();
            }
        } elseif ($clientPortfolio->isProposed()) {
            $existWorkflow = $workflowManager->findOneByClientAndObjectAndType($client, $clientPortfolio, Workflow::TYPE_PAPERWORK);
            if (!$existWorkflow) {
                $event = new WorkflowEvent($client, $clientPortfolio, Workflow::TYPE_PAPERWORK);
                $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);
            }
        }

        /** @var PortfolioInformationManager $portfolioInformationManager */
        $portfolioInformationManager = $this->get('wealthbot_client.portfolio_information_manager');
        $clientAccounts = $accountsRepo->findConsolidatedAccountsByClientId($client->getId());
        $retirementAccounts = $accountsRepo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);
        $form = $this->createFormBuilder()->add('name', TextType::class)->getForm();

        $documentManager = $this->get('wealthbot_user.document_manager');

        $documents['ria_investment_management_agreement'] = $documentManager->getUserDocumentLinkByType(
            $client->getRia()->getId(),
            Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT
        );

        $data = [
            'is_final' => $isFinal,
            'client' => $client,
            'client_accounts' => $clientAccounts,
            'total' => $accountsRepo->getTotalScoreByClientId($client->getId()),
            'ria_company_information' => $companyInformation,
            'has_retirement_account' => count($retirementAccounts) ? true : false,
            'portfolio_information' => $portfolioInformationManager->getPortfolioInformation($client, $portfolio, $isQualified),
            'show_sas_cash' => $this->containsSasCash($clientAccounts),
            'is_use_qualified_models' => $companyInformation->getIsUseQualifiedModels(),
            'form' => $form->createView(),
            'signing_date' => new \DateTime('now'),
            'documents' => $documents,
            'action' => 'client_portfolio',
        ];

        return $this->render('/Client/Portfolio/index.html.twig', $data);
    }

    public function acceptPortfolio()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        /** @var $client User */
        $client = $this->getUser();
        /** @var \App\Entity\Profile */
        $profile = $client->getProfile();

        try {
            $clientPortfolioManager->acceptApprovedPortfolio($client);
        } catch (\RuntimeException $e) {
            $this->get('session')->getFlashBag()->add('error', 'Portfolio does not exist.');

            return $this->redirect($this->generateUrl('rx_user_homepage'));
        }

        $profile->setStatusClient();
        $profile->setRegistrationStep(5);

        $em->persist($profile);
        $em->flush();

        return $this->redirect($this->generateUrl('rx_client_transfer'));
    }

    public function outsideFunds(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $repo ClientAccountRepository */
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $account = $repo->findRetirementAccountById($request->get('account_id'));
        if (!$account) {
            return $this->json([
                'status' => 'error',
                'message' => 'Account does not exist or does not have a retirement type.',
            ]);
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/Portfolio/_outside_funds_list.html.twig', [
                'account' => $account,
            ]),
        ]);
    }

    public function consolidatedAccounts(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        /** @var EntityManager $em */
        /* @var ClientAccountRepository $repo */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        /** @var User $client */
        $client = $this->getUser();

        /** @var ClientAccount $account */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        $consolidatedAccounts = $account->getConsolidatedAccounts();

        if (!$account || !$consolidatedAccounts->count()) {
            $this->json([
                'status' => 'error',
                'message' => 'Account does not exist or does not have consolidated accounts.',
            ]);
        }

        $allConsolidatedAccounts = $this->arrayCollectionPrepend($consolidatedAccounts, $account);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/Portfolio/_consolidated_accounts_list.html.twig', [
                'client' => $client,
                'consolidated_accounts' => $allConsolidatedAccounts,
                'total' => $repo->getTotalScoreByClientId($client->getId(), $account->getid()),
                'with_edit' => false,
                'show_sas_cash' => $this->containsSasCash($allConsolidatedAccounts->toArray()),
            ]),
        ]);
    }

    /**
     * Add element to the beginning of the array collection.
     *
     * @param \App\Collection $collection
     * @param $element
     *
     * @return ArrayCollection
     */
    private function arrayCollectionPrepend(Collection $collection, $element)
    {
        $result = new ArrayCollection();
        $result->add($element);

        foreach ($collection as $item) {
            $result->add($item);
        }

        return $result;
    }

    /**
     * Returns true if array contains ClientAccount objects with sas cache property value more than 0
     * and false otherwise.
     *
     * @param array $accounts array of ClientAccount objects
     *
     * @return bool
     */
    private function containsSasCash(array $accounts = [])
    {
        foreach ($accounts as $account) {
            if ($account->getSasCash() && $account->getSasCash() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set what type of models RIA will be used (qualified or non-qualified).
     *
     * @param bool $value
     */
    protected function setIsQualifiedModel($value)
    {
        /** @var Session $session */
        $session = $this->get('session');
        $session->set('portfolio.is_qualified', (bool) $value);
    }

    /**
     * Set what type of models RIA will be used (qualified or non-qualified).
     *
     * @return bool
     */
    protected function getIsQualifiedModel()
    {
        /** @var Session $session */
        $session = $this->get('session');

        return (bool) $session->get('portfolio.is_qualified', false);
    }

    /**
     * Manage qualified parameters.
     *
     * @param $companyInformation
     * @param $isQualified
     *
     * @return bool
     */
    protected function manageQualified($companyInformation, $isQualified)
    {
        $isUseQualified = $companyInformation->getIsUseQualifiedModels();
        if ($isUseQualified) {
            if ('' !== $isQualified) {
                $this->setIsQualifiedModel($isQualified);
            }
            $isQualified = $this->getIsQualifiedModel();
        } else {
            $isQualified = false;
        }

        return $isQualified;
    }
}
