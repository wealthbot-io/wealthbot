<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 09.04.13
 * Time: 16:24
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Model\ClientAdditionalContact;

class ClientSpouseFormType extends AbstractType
{
    private $isPreSave;

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $this->isPreSave = $options['is_pre_save'];

        $builder->add('first_name', TextType::class, ['required' => false])
            ->add('middle_name', TextType::class, ['required' => false])
            ->add('last_name', TextType::class, ['required' => false])
            ->add('birth_date', DateType::class, [
                'widget' => 'single_text',
                'format' => 'MM-dd-yyyy',
                'required' => false,
                'attr' => ['class' => 'jq-date input-small'],
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data->setType(ClientAdditionalContact::TYPE_SPOUSE);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ClientAdditionalContact',
            'is_pre_save' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'spouse';
    }
}
