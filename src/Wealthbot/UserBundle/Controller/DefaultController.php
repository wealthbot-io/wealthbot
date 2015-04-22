<?php
namespace Wealthbot\UserBundle\Controller;

use Wealthbot\ClientBundle\Entity\TransferCustodian;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $form = $this->createFormBuilder()
            ->add('is_not_locate', 'checkbox', array('required' => false))
            ->add('state', 'entity', array(
                'class' => 'WealthbotAdminBundle:State',
                'label' => 'State',
                'empty_value' => 'Select a State',
                'required' => false
            ))
            ->getForm();

        return $this->render('WealthbotUserBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    public function searchRiaAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $form = $this->createFormBuilder()
            ->add('is_not_locate', 'checkbox', array('required' => false))
            ->add('state', 'entity', array(
                'class' => 'WealthbotAdminBundle:State',
                'label' => 'State',
                'empty_value' => 'Select a State',
                'required' => false
            ))
            ->getForm();

        $form->bind($request);
        $values = $form->getData();

        $query = $em->getRepository('WealthbotUserBundle:User')->createQueryBuilder('r')
            ->leftJoin('r.riaCompanyInformation', 'rci')
            ->where('r.roles LIKE :role')
            ->andWhere('rci.activated = :activated')
            ->andWhere('rci.is_searchable_db = :searchable')
            ->setParameters(array(
                'role' => '%"ROLE_RIA"%',
                'activated' => 1,
                'searchable' => 1
            ));

        if ($values['is_not_locate']) {
            $rias = $query->getQuery()->getResult();

            return $this->getJsonResponse(array(
                'status' => 'success',
                'content' => $this->renderView('WealthbotUserBundle:Default:_rias_list.html.twig', array('rias' => $rias))
            ));
        } elseif ($values['state']) {
            $rias = $query->andWhere('rci.state_id = :state')
                ->setParameter('state', $values['state'])
                ->getQuery()
                ->getResult()
            ;

            return $this->getJsonResponse(array(
                'status' => 'success',
                'content' => $this->renderView('WealthbotUserBundle:Default:_rias_list.html.twig', array('rias' => $rias))
            ));
        } else {
            return $this->getJsonResponse(array(
                'status' => 'error',
                'content' => 'Missing parameters.'
            ));
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
            ->setParameter('name', '%' . $query . '%')
            ->getQuery()
            ->execute();

        $result = array();

        /** @var TransferCustodian $item */
        foreach($transferCustodians as $item) {
            $card = array(
                'id' => $item->getId(),
                'name' => $item->getName()
            );

            $result[] = $card;
        }

        return $this->getJsonResponse($result);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, array('Content-Type'=>'application/json'));
    }
}
