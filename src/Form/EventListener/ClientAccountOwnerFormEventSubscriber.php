<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.05.13
 * Time: 15:48
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use App\Entity\ClientAccountOwner;
use App\Form\Type\OtherAccountOwnerFormType;
use App\Model\ClientAdditionalContact;
use App\Repository\ClientAccountOwnerRepository;
use App\Entity\User;

class ClientAccountOwnerFormEventSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $client;
    private $em;
    private $isJoint;

    public function __construct(FormFactoryInterface $factory, User $client, EntityManager $em, $isJoint = false)
    {
        $this->factory = $factory;
        $this->client = $client;
        $this->em = $em;
        $this->isJoint = (bool) $isJoint;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preBind',
            FormEvents::SUBMIT => 'bind',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();

        $this->updateFields($form);
    }

    public function preBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $this->updateFields($form);

        if (is_array($data['owner_types']) && in_array(ClientAdditionalContact::TYPE_OTHER, $data['owner_types'])) {
            $this->addOtherContact($form);
        }
    }

    public function bind(FormEvent $event)
    {
        $form = $event->getForm();
        $ownerTypes = $form->get('owner_types')->getData();

        if ($ownerTypes && $this->isJoint) {
            $form->addError(new FormError('You should select two owners of the account.'));
        }

        if (!$this->isJoint && !$ownerTypes) {
            $form->addError(new FormError('Select owner of the account.'));
        }
    }

    private function updateFields(FormInterface $form)
    {
        $choices = ClientAccountOwner::getOwnerTypeChoices();

        if ($this->isJoint) {
            if (!$this->client->isMarried()) {
                unset($choices['spouse']);
            }

            $form->add($this->factory->createNamed('owner_types', ChoiceType::class, null, [
                'mapped' => false,
                'choices' => $choices,
                'expanded' => true,
                'multiple' => true,
                'auto_initialize' => false,
            ]));
        } else {
            unset($choices['other']);

            $form->add($this->factory->createNamed('owner_types', ChoiceType::class, null, [
                'mapped' => false,
                'auto_initialize' => false,
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
            ]));
        }
    }

    private function addOtherContact(FormInterface $form)
    {
        /** @var ClientAccountOwnerRepository $repo */
        $repo = $this->em->getRepository('App\Entity\ClientAccountOwner');

        $data = null;
        $lastOtherOwner = $repo->findLastOtherOwnerByClientId($this->client->getId());
        if ($lastOtherOwner) {
            $data = $lastOtherOwner->getContact();
        }

        $form->add(
            $this->factory->createNamed('other_contact', OtherAccountOwnerFormType::class, $data)
        );
    }
}
