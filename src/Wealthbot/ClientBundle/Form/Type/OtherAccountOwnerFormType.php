<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.05.13
 * Time: 15:41
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\ClientBundle\Entity\ClientAdditionalContact;

class OtherAccountOwnerFormType extends AbstractType
{
    private $default;

    public function __construct(ClientAdditionalContact $default = null)
    {
        $this->default = $default;
    }

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        if ($this->default) {
            $firstName = $this->default->getFirstName();
            $middleName = $this->default->getMiddleName();
            $lastName = $this->default->getLastName();
            $relationship = $this->default->getRelationship();
        } else {
            $firstName = null;
            $middleName = null;
            $lastName = null;
            $relationship = null;
        }

        $builder->add('first_name', 'text', ['attr' => ['value' => $firstName]])
            ->add('middle_name', 'text', ['attr' => ['value' => $middleName]])
            ->add('last_name', 'text', ['attr' => ['value' => $lastName]])
            ->add('relationship', 'text', ['attr' => ['value' => $relationship]]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data->setType(ClientAdditionalContact::TYPE_OTHER);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAdditionalContact',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'other_account_owner';
    }
}
