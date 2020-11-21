<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.05.13
 * Time: 12:01
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ClientAccountOwner;
use App\Entity\ClientAdditionalContact;
use Repository\ClientAdditionalContactRepository;
use App\Entity\User;

class ClientAccountOwnerFormHandler
{
    private $form;
    private $request;
    private $em;
    private $client;

    public function __construct(FormInterface $form, Request $request, EntityManager $em, User $client)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
        $this->client = $client;
    }

    /**
     * Returns array of account owners.
     * Example: array('self' => array('owner_type' => 'self', 'owner_client_id' => 1), ...).
     *
     * @return array
     */
    public function process()
    {
        $this->form->handleRequest($this->request);
        $result = [];

        if ($this->form->isValid()) {
            $data = $this->form->getData();
            $ownerTypes = $this->form->get('owner_types')->getData();

            if (is_array($ownerTypes)) {
                foreach ($ownerTypes as $type) {
                    if (ClientAccountOwner::OWNER_TYPE_OTHER === $type) {
                        $result[$type] = $this->createOtherAccountOwner($data['other_contact']);
                    } else {
                        $result[$type] = $this->createAccountOwnerByType($type);
                    }
                }
            } else {
                $result[$ownerTypes] = $this->createAccountOwnerByType($ownerTypes);
            }
        }

        return $result;
    }

    /**
     * Create account owner with type = 'other'
     * and returns array.
     *
     * @param ClientAdditionalContact $otherContact
     *
     * @return array
     */
    private function createOtherAccountOwner(ClientAdditionalContact $otherContact)
    {
        /** @var ClientAdditionalContactRepository $repo */
        $repo = $this->em->getRepository('App\Entity\ClientAdditionalContact');

        $exist = $repo->findOneBy(
            [
                'client_id' => $this->client->getId(),
                'first_name' => $otherContact->getFirstName(),
                'middle_name' => $otherContact->getMiddleName(),
                'last_name' => $otherContact->getLastName(),
                'relationship' => $otherContact->getRelationship(),
                'type' => ClientAdditionalContact::TYPE_OTHER,
            ]
        );

        if ($exist) {
            $otherContact = $exist;
        }

        $otherContact->setType(ClientAccountOwner::OWNER_TYPE_OTHER);
        $otherContact->setClient($this->client);

        $this->em->persist($otherContact);
        $this->em->flush();

        $owner = [
            'owner_type' => ClientAccountOwner::OWNER_TYPE_OTHER,
            'owner_contact_id' => $otherContact->getId(),
        ];

        return $owner;
    }

    /**
     * Create account owner by type and returns array.
     *
     * @param string $type
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function createAccountOwnerByType($type)
    {
        $owner = ['owner_type' => $type];

        switch ($type) {
            case ClientAccountOwner::OWNER_TYPE_SELF:
                $owner['owner_client_id'] = $this->client->getId();
                break;

            case ClientAccountOwner::OWNER_TYPE_SPOUSE:
                /** @var ClientAdditionalContactRepository $repo */
                $repo = $this->em->getRepository('App\Entity\ClientAdditionalContact');
                $spouseContact = $repo->findOneBy([
                    'client_id' => $this->client->getId(),
                    'type' => ClientAdditionalContact::TYPE_SPOUSE,
                ]);

                $owner['owner_contact_id'] = $spouseContact->getId();
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Invalid value for type argument : %s', $type));
                break;
        }

        return $owner;
    }
}
