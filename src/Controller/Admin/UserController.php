<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 09.01.13
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Type\CreateAdminUserType;
use App\Model\Acl;
use App\Entity\User;
use App\Repository\UserRepository;

class UserController extends AclController
{
    public function index()
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $repo UserRepository */
        $repo = $em->getRepository('App\Entity\User');

        $users = [
            'Master' => $repo->getUsersByRole('ROLE_ADMIN_MASTER'),
            'Manager' => $repo->getUsersByRole('ROLE_ADMIN_PM'),
            'CSR' => $repo->getUsersByRole('ROLE_ADMIN_CSR'),
        ];

        return $this->render('/Admin/User/index.html.twig', [
            'allUsers' => $users,
        ]);
    }

    public function create(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_CREATE_USER);

        $user = new User();
        $form = $this->createForm(CreateAdminUserType::class);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $userAccount = $form->getData();

                /** @var $em EntityManager */
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($userAccount);
                $em->flush();

                $mailer = $this->get('wealthbot.mailer');
                $mailer->sendCreatedAdminUserMessage(
                    $userAccount,
                    $form->get('plainPassword')->getData(),
                    $form->get('level')->getData()
                );

                $this->get('session')->getFlashBag()->add('success', 'User has been successfully created.');

                return $this->redirect($this->generateUrl('rx_admin_users'));
            }
        }

        return $this->render('/Admin/User/create_user.html.twig', [
            'createAdminUserForm' => $form->createView(),
        ]);
    }

    public function edit(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_CREATE_USER);

        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('App\Entity\User')->getUserByIdAndRoles($request->get('id'), [
            'ROLE_ADMIN_MASTER', 'ROLE_ADMIN_PM', 'ROLE_ADMIN_CSR',
        ]);

        if (!$user) {
            throw $this->createNotFoundException('User does not exist.');
        }
        $form = $this->createForm(CreateAdminUserType::class, $user);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $userAccount = $form->getData();

                /** @var $em EntityManager */
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($userAccount);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'User has been successfully updated.');

                return $this->redirect($this->generateUrl('rx_admin_users'));
            }
        }

        return $this->render('/Admin/User/edit_user.html.twig', [
            'createAdminUserForm' => $form->createView(),
            'user' => $user,
        ]);
    }

    public function delete(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_CREATE_USER);

        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('App\Entity\User')->getUserByIdAndRoles($request->get('id'), [
            'ROLE_ADMIN_MASTER', 'ROLE_ADMIN_PM', 'ROLE_ADMIN_CSR',
        ]);

        if (!$user) {
            throw $this->createNotFoundException('User does not exist.');
        }

        $em->remove($user);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'User has been successfully deleted.');

        return $this->redirect($this->generateUrl('rx_admin_users'));
    }
}
