<?php

namespace App\Controller\Ria;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Type\CreateUserFormType;
use App\Form\Type\UserGroupsFormType;
use App\Entity\Group;
use App\Model\User;

class UserController extends Controller
{
    public function index()
    {
        /** @var User $ria */
        $ria = $this->getUser();

        $groupsForm = $this->createForm(UserGroupsFormType::class);
        $riaUsers = $this->getRiaUsers();

        $createUserForm = $this->createForm(CreateUserFormType::class, null, ['class' => 'App\Entity\User', 'ria' => $ria]);
        $groups = $this->get('doctrine.orm.entity_manager')->getRepository('App\Entity\Group')->getRiaGroups($ria);

        return $this->render('/Ria/User/index.html.twig', [
            'create_user_form' => $createUserForm->createView(),
            'ria_users' => $riaUsers,
            'groups_form' => $groupsForm->createView(),
            'groups' => $groups,
            'ria' => $ria,
        ]);
    }

    public function resetSelfPassword()
    {
        /** @var $form \FOS\UserBundle\Form\Type\ChangePasswordFormType */
        $form = $this->createForm(\FOS\UserBundle\Form\Type\ChangePasswordFormType::class);
        /** @var $formHandler \FOS\UserBundle\Form\Handler\ChangePasswordFormHandler */
        $formHandler = $this->get('fos_user.change_password.form.handler.default');
        $process = $formHandler->process($this->getUser());

        if ($process) {
            $this->get('session')->getFlashBag()->add('success', 'Password successfully updated.');

            return $this->redirect($this->generateUrl('rx_ria_dashboard'));
        }

        return $this->render('/Ria/User/reset_password.html.twig', ['selfForm' => $form->createView()]);
    }

    public function resetInternallyPassword(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $ria = $this->getUser();

        $riaUser = $em->getRepository('App\Entity\User')->getClientByIdAndRiaId($request->get('user_id'), $this->getUser());
        if (!$riaUser || $riaUser->getRia() !== $ria) {
            throw $this->createNotFoundException('This user does not exist');
        }

        $newPassword = $riaUser->generateTemporaryPassword();
        $riaUser->setPlainPassword($newPassword);
        $riaUser->setIsPasswordReset(true);

        $em->persist($riaUser);
        $em->flush();

        $this->get('wealthbot.mailer')->sendRiaUserResetPasswordEmail($ria, $riaUser, $newPassword);

        $this->get('session')->getFlashBag()->add('success', 'New password was sent to the email.');

        return $this->redirect($this->generateUrl('rx_ria_user_management'));
    }

    public function create(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository('App\Entity\User');
        $ria = $this->getUser();
        $form = $this->createForm(CreateUserFormType::class, $ria, [
            'ria' => $ria
        ]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $user \Entity\User */
                $user = $form->getData();

                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'User has been successfully created.');

                return $this->getEmptyUserManagement();
            }
        }

        $riaUsers = $this->getRiaUsers();

        return $this->render('/Ria/User/edit.html.twig', [
            'form' => $form->createView(),
            'ria_users' => $riaUsers,
            'ria_user' => $ria,
            'ria' => $ria,
        ]);
    }

    public function edit(Request $request, \App\Entity\User $user_id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository("App\\Entity\\User");

        $ria = $user_id;

        $form = $this->createForm(CreateUserFormType::class, $ria, ['class'=>'App\\Entity\\User']);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $user \Entity\User */
                $user = $form->getData();

                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'User successfully updated');

                return $this->getEmptyUserManagement();
            }
        }
        $riaUsers = $this->getRiaUsers();

        return $this->render('/Ria/User/edit.html.twig', [
            'form' => $form->createView(),
            'ria_users' => $riaUsers,
            'ria_user' => $ria,
            'ria' => $ria,
        ]);
    }

    public function delete($user_id)
    {
        $riaUser = $this->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('App\Entity\User')->getUserByRiaIdAndUserId($riaUser->getId(), $user_id);

        if (!$user) {
            $this->get('session')->getFlashBag()->add('error', 'User was not found');
        } else {
            $em->remove($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'User deleted successfully');
        }

        return $this->getEmptyUserManagement();
    }

    public function cancel()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $ria = $this->getUser();

        $riaUsers = $em->getRepository('App\Entity\User')->getUsersByRiaId($ria->getId());

        $createUserForm = $this->createForm(CreateUserFormType::class, null, [
            'class' => 'App\Entity\User',
            'ria' => $ria
        ]);

        return $this->render('/Ria/User/create.html.twig', [
            'form' => $createUserForm->createView(),
            'ria_users' => $riaUsers,
            'ria' => $ria,
        ]);
    }

    public function createGroup(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $ria = $this->getUser();
        $groupsForm = $this->createForm(UserGroupsFormType::class);

        if ($request->isMethod('post')) {
            $groupsForm->handleRequest($request);

            if ($groupsForm->isValid()) {
                $group = $groupsForm->getData();

                if ($group->isAll()) {
                    throw $this->createNotFoundException('You haven\'t permission to edit this group');
                }

                $group->setOwner($ria);
                $em->persist($group);
                $em->flush();

                $em->refresh($group);

                $this->get('session')->getFlashBag()->add('success', 'Group Created Successfully');

                return $this->getEmptyGroupManagement();
            }
        }



        $groups = $em->getRepository('App\Entity\Group')->getRiaGroups($ria);

        return $this->render('/Ria/User/groups.html.twig', [
            'form' => $groupsForm->createView(),
            'groups' => $groups,
            'ria' => $ria,
        ]);
    }

    public function deleteGroup(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $group = $em->getRepository('App\Entity\Group')->find($request->get('group_id'));

        if ($group) {
            if ($group->getUsers()->count()) {
                $this->get('session')->getFlashBag()->add('error', 'Group can\'t be deleted, it contains a users');

                return $this->getEmptyGroupManagement();
            }
            $em->remove($group);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Group deleted successfully');

            return $this->getEmptyGroupManagement();
        }

        $this->get('session')->getFlashBag()->add('error', 'Group was not found');

        return $this->getEmptyGroupManagement();
    }

    private function getEmptyUserManagement()
    {
        $ria = $this->getUser();
        $createUserForm = $this->createForm(CreateUserFormType::class, null, ['class'=>'App\Entity\User', 'ria' => $ria]);
        $riaUsers = $this->getRiaUsers();

        return $this->render('/Ria/User/create.html.twig', [
            'ria_users' => $riaUsers,
            'form' => $createUserForm->createView(),
            'ria' => $ria,
        ]);
    }

    private function getEmptyGroupManagement()
    {
        $ria = $this->getUser();
        $groupsForm = $this->createForm(UserGroupsFormType::class);
        $groups = $this->getDoctrine()->getRepository('App\Entity\Group')->getRiaGroups($ria);
        return $this->render('/Ria/User/groups.html.twig', [
            'form' => $groupsForm->createView(),
            'groups' => $groups,
            'ria' => $ria,
        ]);
    }

    private function getRiaUsers()
    {
        $ria = $this->getUser();
        $userRepository = $this->get('doctrine.orm.entity_manager')->getRepository('App\Entity\User');
        if ($ria->hasRole('ROLE_RIA_ADMIN')) {
            $riaUsers = $userRepository->getUsersForRiaAdmin($ria);
        } else {
            $riaUsers = $userRepository->getUsersByRiaId($ria->getId());
        }

        return $riaUsers;
    }
}
