<?php

namespace Wealthbot\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\ClientBundle\Entity\TransferCustodian;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $form = $this->createFormBuilder()
            ->add('is_not_locate', 'checkbox', ['required' => false])
            ->add('state', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->getForm();

        return $this->render('WealthbotUserBundle:Default:index.html.twig', ['form' => $form->createView()]);
    }

    public function searchRiaAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $form = $this->createFormBuilder()
            ->add('is_not_locate', 'checkbox', ['required' => false])
            ->add('state', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->getForm();

        $form->handleRequest($request);
        $values = $form->getData();

        $query = $em->getRepository('WealthbotUserBundle:User')->createQueryBuilder('r')
            ->leftJoin('r.riaCompanyInformation', 'rci')
            ->where('r.roles LIKE :role')
            ->andWhere('rci.activated = :activated')
            ->andWhere('rci.is_searchable_db = :searchable')
            ->setParameters([
                'role' => '%"ROLE_RIA"%',
                'activated' => 1,
                'searchable' => 1,
            ]);

        if ($values['is_not_locate']) {
            $rias = $query->getQuery()->getResult();

            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $this->renderView('WealthbotUserBundle:Default:_rias_list.html.twig', ['rias' => $rias]),
            ]);
        } elseif ($values['state']) {
            $rias = $query->andWhere('rci.state_id = :state')
                ->setParameter('state', $values['state'])
                ->getQuery()
                ->getResult()
            ;

            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $this->renderView('WealthbotUserBundle:Default:_rias_list.html.twig', ['rias' => $rias]),
            ]);
        } else {
            return $this->getJsonResponse([
                'status' => 'error',
                'content' => 'Missing parameters.',
            ]);
        }
    }

    public function switchToAdminAction(Request $request)
    {
        return $this->redirect($this->generateUrl('rx_admin_homepage'));
    }

    public function completeTransferCustodianAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $query = $request->get('query');

        $transferCustodians = $em->getRepository('WealthbotClientBundle:TransferCustodian')->createQueryBuilder('tc')
            ->where('tc.name LIKE :name')
            ->setParameter('name', '%'.$query.'%')
            ->getQuery()
            ->execute();

        $result = [];

        /** @var TransferCustodian $item */
        foreach ($transferCustodians as $item) {
            $card = [
                'id' => $item->getId(),
                'name' => $item->getName(),
            ];

            $result[] = $card;
        }

        return $this->getJsonResponse($result);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
