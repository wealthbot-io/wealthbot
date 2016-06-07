<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 21.12.12
 * Time: 15:07
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\UserBundle\Entity\User;

class AccountGroupsFormType extends AbstractType
{
    private $client;

    public function __construct(User $client)
    {
        $this->client = $client;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $riaCompanyInformation = $this->client->getProfile()->getRia()->getRiaCompanyInformation();
        $choices = AccountGroup::getGroupChoices();

        if (!$riaCompanyInformation->getIsAllowRetirementPlan()) {
            unset($choices[AccountGroup::GROUP_EMPLOYER_RETIREMENT]);
        }

        $builder->add('groups', 'choice', [
            'choices' => $choices,
            'multiple' => false,
            'expanded' => true,
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($choices) {
            $form = $event->getForm();
            $types = $form->get('groups')->getData();

            if (empty($types)) {
                $form->get('groups')->addError(new FormError('Select value.'));
            }

            if (is_array($types)) {
                foreach ($types as $type) {
                    if (!in_array($type, $choices)) {
                        $form->get('groups')->addError(new FormError('Invalid value.'));
                    }
                }
            } else {
                if (!in_array($types, $choices)) {
                    $form->get('groups')->addError(new FormError('Invalid value.'));
                }
            }

        });
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'client_account_types';
    }
}
