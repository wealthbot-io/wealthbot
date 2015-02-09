<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 09.01.13
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Controller;

use Doctrine\ORM\EntityManager;
use Wealthbot\AdminBundle\Form\Type\CreateAdminUserType;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Wealthbot\AdminBundle\Model\Acl;

class UserController extends AclController
{
    public function indexAction()
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $repo UserRepository */
        $repo = $em->getRepository('WealthbotUserBundle:User');

        $users = array(
            'Master' => $repo->getUsersByRole('ROLE_ADMIN_MASTER'),
            'Manager' => $repo->getUsersByRole('ROLE_ADMIN_PM'),
            'CSR' => $repo->getUsersByRole('ROLE_ADMIN_CSR')
        );

        return $this->render('WealthbotAdminBundle:User:index.html.twig', array(
            'allUsers' => $users
        ));
    }

    public function createAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_CREATE_USER);

        $user = new User();
        $form = $this->createForm(new CreateAdminUserType($user));

        if ($request->isMethod('post')) {
            $form->bind($request);

            if ($form->isValid()) {
                $userAccount = $form->getData();

                /** @var $em EntityManager */
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($userAccount);
                $em->flush();

                $mailer = $this->get('wealthbot_admin.mailer');
                $mailer->sendCreatedAdminUserMessage(
                    $userAccount,
                    $form->get('plainPassword')->getData(),
                    $form->get('level')->getData()
                );

                $this->get('session')->setFlash('success', 'User has been successfully created.');
                return $this->redirect($this->generateUrl('rx_admin_users'));
            }
        }

        return $this->render('WealthbotAdminBundle:User:create_user.html.twig', array(
            'createAdminUserForm' => $form->createView()
        ));
    }

    public function editAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_CREATE_USER);

        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('WealthbotUserBundle:User')->getUserByIdAndRoles($request->get('id'), array(
            'ROLE_ADMIN_MASTER', 'ROLE_ADMIN_PM', 'ROLE_ADMIN_CSR'
        ));

        if (!$user) {
            throw $this->createNotFoundException('User does not exist.');
        }
        $form = $this->createForm(new CreateAdminUserType($user), $user);

        if ($request->isMethod('post')) {
            $form->bind($request);

            if ($form->isValid()) {
                $userAccount = $form->getData();

                /** @var $em EntityManager */
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($userAccount);
                $em->flush();

                $this->get('session')->setFlash('success', 'User has been successfully updated.');
                return $this->redirect($this->generateUrl('rx_admin_users'));
            }
        }

        return $this->render('WealthbotAdminBundle:User:edit_user.html.twig', array(
            'createAdminUserForm' => $form->createView(),
            'user' => $user
        ));
    }

    public function deleteAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_CREATE_USER);

        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('WealthbotUserBundle:User')->getUserByIdAndRoles($request->get('id'), array(
            'ROLE_ADMIN_MASTER', 'ROLE_ADMIN_PM', 'ROLE_ADMIN_CSR'
        ));

        if (!$user) {
            throw $this->createNotFoundException('User does not exist.');
        }

        $em->remove($user);
        $em->flush();

        $this->get('session')->setFlash('success', 'User has been successfully deleted.');
        return $this->redirect($this->generateUrl('rx_admin_users'));
    }

}