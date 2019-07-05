<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.05.13
 * Time: 15:41
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ClientAdditionalContact;

class OtherAccountOwnerFormType extends AbstractType
{
    private $default;

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $this->default = $options['default'] ?? null;

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

        $builder->add('first_name', TextType::class, ['attr' => ['value' => $firstName]])
            ->add('middle_name', TextType::class, ['attr' => ['value' => $middleName]])
            ->add('last_name', TextType::class, ['attr' => ['value' => $lastName]])
            ->add('relationship', TextType::class, ['attr' => ['value' => $relationship]]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data->setType(ClientAdditionalContact::TYPE_OTHER);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ClientAdditionalContact',
            'auto_initialize' => false,
            'default' => null
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
