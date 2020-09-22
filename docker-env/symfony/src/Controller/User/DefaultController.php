<?php

namespace App\Controller\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\TransferCustodian;

class DefaultController extends Controller
{
    public function index()
    {
        die('here');

        /// todo: make ria users selection
        $form = $this->createFormBuilder()
            ->add('is_not_locate', CheckboxType::class, ['required' => false])
            ->add('state', EntityType::class, [
                'class' => 'App\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->getForm();

        $ria = $this->getDoctrine()->getRepository('App\Entity\User')->findOneByEmail('raiden@wealthbot.io');


        return $this->render('/User/Default/index.html.twig', [
            'form' => $form->createView(),
            'ria' => $ria
        ]);
    }

    public function searchRia(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $form = $this->createFormBuilder()
            ->add('is_not_locate', CheckboxType::class, ['required' => false])
            ->add('state', EntityType::class, [
                'class' => 'App\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->getForm();

        $form->handleRequest($request);
        $values = $form->getData();

        $query = $em->getRepository('App\Entity\User')->createQueryBuilder('r')
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

            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/User/Default/_rias_list.html.twig', ['rias' => $rias]),
            ]);
        } elseif ($values['state']) {
            $rias = $query->andWhere('rci.state_id = :state')
                ->setParameter('state', $values['state'])
                ->getQuery()
                ->getResult()
            ;

            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/User/Default/_rias_list.html.twig', ['rias' => $rias]),
            ]);
        } else {
            return $this->json([
                'status' => 'error',
                'content' => 'Missing parameters.',
            ]);
        }
    }

    public function switchToAdmin(Request $request)
    {
        return $this->redirect($this->generateUrl('rx_admin_homepage'));
    }

    public function completeTransferCustodian(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $query = $request->get('query');

        $transferCustodians = $em->getRepository('App\Entity\TransferCustodian')->createQueryBuilder('tc')
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

        return $this->json($result);
    }
}
