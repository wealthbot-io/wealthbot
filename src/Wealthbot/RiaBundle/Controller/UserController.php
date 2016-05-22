<?php

namespace Wealthbot\RiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\RiaBundle\Form\Type\CreateUserFormType;
use Wealthbot\RiaBundle\Form\Type\UserGroupsFormType;
use Wealthbot\UserBundle\Entity\Group;
use Wealthbot\UserBundle\Model\User;

class UserController extends Controller
{
    public function indexAction()
    {
        /** @var User $ria */
        $ria = $this->getUser();

        $groupsForm = $this->createForm(new UserGroupsFormType('Wealthbot\UserBundle\Entity\Group'));
        $riaUsers = $this->getRiaUsers();

        $createUserForm = $this->createForm(new CreateUserFormType('\Wealthbot\UserBundle\Entity\User', $ria));
        $groups = $this->get('doctrine.orm.entity_manager')->getRepository('WealthbotUserBundle:Group')->getRiaGroups($ria);

        return $this->render('WealthbotRiaBundle:User:index.html.twig', [
            'create_user_form' => $createUserForm->createView(),
            'ria_users' => $riaUsers,
            'groups_form' => $groupsForm->createView(),
            'groups' => $groups,
            'ria' => $ria,
        ]);
    }

    public function resetSelfPasswordAction()
    {
        /** @var $form \FOS\UserBundle\Form\Type\ChangePasswordFormType */
        $form = $this->get('fos_user.change_password.form');
        /** @var $formHandler \FOS\UserBundle\Form\Handler\ChangePasswordFormHandler */
        $formHandler = $this->get('fos_user.change_password.form.handler.default');
        $process = $formHandler->process($this->getUser());

        if ($process) {
            $this->get('session')->getFlashBag()->add('success', 'Password successfully updated.');

            return $this->redirect($this->generateUrl('rx_ria_dashboard'));
        }

        return $this->render('WealthbotRiaBundle:User:reset_password.html.twig', ['selfForm' => $form->createView()]);
    }

    public function resetInternallyPasswordAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $ria = $this->getUser();

        $riaUser = $em->getRepository('WealthbotUserBundle:User')->getClientByIdAndRiaId($request->get('user_id'), $this->getUser());
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

    public function createAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository('WealthbotUserBundle:User');
        $ria = $this->getUser();
        $form = $this->createForm(new CreateUserFormType('Wealthbot\UserBundle\Entity\User', $ria));

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $user \Wealthbot\UserBundle\Entity\User */
                $user = $form->getData();

                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'User has been successfully created.');

                return $this->getEmptyUserManagement();
            }
        }

        $riaUsers = $this->getRiaUsers();

        return $this->render('WealthbotRiaBundle:User:edit.html.twig', [
            'form' => $form->createView(),
            'ria_users' => $riaUsers,
            'ria_user' => $riaUser,
            'ria' => $ria,
        ]);
    }

    public function editAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository('WealthbotUserBundle:User');
        $ria = $this->getUser();

        $riaUser = $userRepository->getClientByIdAndRiaId($request->get('user_id'), $this->getUser());

        $form = $this->createForm(new CreateUserFormType('Wealthbot\UserBundle\Entity\User', $ria), $riaUser);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $user \Wealthbot\UserBundle\Entity\User */
                $user = $form->getData();

                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'User successfully updated');

                return $this->getEmptyUserManagement();
            }
        }

        $riaUsers = $this->getRiaUsers();

        return $this->render('WealthbotRiaBundle:User:edit.html.twig', [
            'form' => $form->createView(),
            'ria_users' => $riaUsers,
            'ria_user' => $riaUser,
            'ria' => $ria,
        ]);
    }

    public function deleteAction($user_id)
    {
        $riaUser = $this->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('WealthbotUserBundle:User')->getUserByRiaIdAndUserId($riaUser->getId(), $user_id);

        if (!$user) {
            $this->get('session')->getFlashBag()->add('error', 'User was not found');
        } else {
            $em->remove($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'User deleted successfully');
        }

        return $this->getEmptyUserManagement();
    }

    public function cancelAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $ria = $this->getUser();

        $riaUsers = $em->getRepository('WealthbotUserBundle:User')->getUsersByRiaId($ria->getId());

        $createUserForm = $this->createForm(new CreateUserFormType('\Wealthbot\UserBundle\Entity\User', $ria));

        return $this->render('WealthbotRiaBundle:User:create.html.twig', [
            'form' => $createUserForm->createView(),
            'ria_users' => $riaUsers,
            'ria' => $ria,
        ]);
    }

    public function createGroupAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $ria = $this->getUser();

        if ($groupId = $request->get('group_id')) {
            $group = $em->getRepository('WealthbotUserBundle:Group')->find($groupId);
            if ($group->isAll() || $group->getOwner() !== $this->getUser()) {
                throw $this->createNotFoundException('You haven\'t permission to edit this group');
            }

            $message = 'Group updated successfully.';
        } else {
            $group = new Group();
            $group->setOwner($ria);
            $message = 'Group created successfully.';
        }

        $groupsForm = $this->createForm(new UserGroupsFormType('Wealthbot\UserBundle\Entity\Group'), $group);

        if ($request->isMethod('post')) {
            $groupsForm->handleRequest($request);

            if ($groupsForm->isValid()) {
                $group = $groupsForm->getData();

                if ($group->isAll()) {
                    throw $this->createNotFoundException('You haven\'t permission to edit this group');
                }

                //$group->setOwner($ria);
                $em->persist($group);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->getEmptyGroupManagement();
            }
        }

        $em->refresh($group);
        $groups = $em->getRepository('WealthbotUserBundle:Group')->getRiaGroups($ria);

        return $this->render('WealthbotRiaBundle:User:groups.html.twig', [
            'form' => $groupsForm->createView(),
            'groups' => $groups,
            'group' => $group,
            'ria' => $ria,
        ]);
    }

    public function deleteGroupAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $group = $em->getRepository('WealthbotUserBundle:Group')->find($request->get('group_id'));

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
        $createUserForm = $this->createForm(new CreateUserFormType('\Wealthbot\UserBundle\Entity\User', $ria));
        $riaUsers = $this->getRiaUsers();

        return $this->render('WealthbotRiaBundle:User:create.html.twig', [
            'ria_users' => $riaUsers,
            'form' => $createUserForm->createView(),
            'ria' => $ria,
        ]);
    }

    private function getEmptyGroupManagement()
    {
        $ria = $this->getUser();
        $groupsForm = $this->createForm(new UserGroupsFormType('Wealthbot\UserBundle\Entity\Group'));
        $groups = $this->get('doctrine.orm.entity_manager')->getRepository('WealthbotUserBundle:Group')->getRiaGroups($ria);

        return $this->render('WealthbotRiaBundle:User:groups.html.twig', [
            'form' => $groupsForm->createView(),
            'groups' => $groups,
            'ria' => $ria,
        ]);
    }

    private function getRiaUsers()
    {
        $ria = $this->getUser();
        $userRepository = $this->get('doctrine.orm.entity_manager')->getRepository('WealthbotUserBundle:User');
        if ($ria->hasRole('ROLE_RIA_ADMIN')) {
            $riaUsers = $userRepository->getUsersForRiaAdmin($ria);
        } else {
            $riaUsers = $userRepository->getUsersByRiaId($ria->getId());
        }

        return $riaUsers;
    }
}
