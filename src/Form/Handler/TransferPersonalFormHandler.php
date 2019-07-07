<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.02.13
 * Time: 12:32
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\ClientAccount;
use App\Entity\ClientAdditionalContact;
use App\Model\AccountOwnerInterface;
use App\Model\UserAccountOwnerAdapter;
use App\Entity\Profile;
use App\Entity\User;

class TransferPersonalFormHandler
{
    protected $request;
    protected $form;
    protected $em;
    protected $options;

    public function __construct(Form $form, Request $request, EntityManager $em, array $options = [])
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
        $this->options = $options;
    }

    public function process(ClientAccount $account, $withMaritalStatus = false)
    {
        if ($this->request->isMethod('post')) {
            $this->form->handleRequest($this->request);

            /** @var AccountOwnerInterface $data */
            $data = $this->form->getData();
            $isValid = $this->form->isValid();
            $objectToSave = $data->getObjectToSave();

            // Validate client ssn_tin field
            if ($isValid && ($objectToSave instanceof User)) {
                /** @var Validator $validator */
                $validator = $this->getOption('validator');
                if (null === $validator || !($validator instanceof ValidatorInterface)) {
                    throw new \InvalidArgumentException('validator option must be instance of ValidatorInterface.');
                }

                $errors = $validator->validate($objectToSave->getClientPersonalInformation());

                /** @var ConstraintViolation $error */
                foreach ($errors as $error) {
                    if ('ssn_tin' === $error->getPropertyPath()) {
                        $this->form->get('ssn_tin_1')->addError(new FormError($error->getMessage()));
                    }
                }

                $isValid = $this->form->isValid();
            }

            if ($isValid) {
                $this->onSuccess($account, $withMaritalStatus);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(ClientAccount $account, $withMaritalStatus)
    {
        /** @var AccountOwnerInterface $data */
        $data = $this->form->getData();
        $isPrimaryApplicant = ($data instanceof UserAccountOwnerAdapter);

        if (true === $withMaritalStatus && $isPrimaryApplicant) {
            /** @var $profile Profile */
            $profile = $account->getClient()->getProfile();
            $spouse = $account->getClient()->getSpouse();

            $maritalStatus = $this->form->get('marital_status')->getData();

            if (Profile::CLIENT_MARITAL_STATUS_MARRIED === $maritalStatus) {
                if (!$spouse) {
                    $spouse = new ClientAdditionalContact();
                    $spouse->setClient($account->getClient());
                    $spouse->setType(ClientAdditionalContact::TYPE_SPOUSE);
                }

                if ($this->form->has('spouse_first_name')) {
                    $spouse->setFirstName($this->form->get('spouse_first_name')->getData());
                }
                if ($this->form->has('spouse_middle_name')) {
                    $spouse->setMiddleName($this->form->get('spouse_middle_name')->getData());
                }
                if ($this->form->has('spouse_last_name')) {
                    $spouse->setLastName($this->form->get('spouse_last_name')->getData());
                }
                if ($this->form->has('spouse_birth_date')) {
                    $spouse->setBirthDate($this->form->get('spouse_birth_date')->getData());
                }
            }

            $profile->setMaritalStatus($maritalStatus);
            $this->em->persist($profile);
        }

        $account->setStepAction(
            $isPrimaryApplicant ? ClientAccount::STEP_ACTION_PERSONAL : ClientAccount::STEP_ACTION_ADDITIONAL_PERSONAL
        );
        $account->setIsPreSaved($this->request->isXmlHttpRequest());

        $this->em->persist($data->getObjectToSave());
        $this->em->persist($account);
        $this->em->flush();
    }

    protected function getOption($name)
    {
        if ($this->hasOption($name)) {
            return $this->options[$name];
        }

        return;
    }

    protected function hasOption($name)
    {
        return isset($this->options[$name]);
    }
}
