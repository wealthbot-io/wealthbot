<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 09.04.13
 * Time: 16:24
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;


use Wealthbot\ClientBundle\Model\ClientAdditionalContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientSpouseFormType extends AbstractType
{
    private $isPreSave;

    public function __construct($isPreSave = false)
    {
        $this->isPreSave = $isPreSave;
    }

    public function buildForm(FormBuilderInterface $builder, array $options = array())
    {
        $builder->add('first_name', 'text', array('required' => false))
            ->add('middle_name', 'text', array('required' => false))
            ->add('last_name', 'text', array('required' => false))
            ->add('birth_date', 'date', array(
                'widget' => 'single_text',
                'format' => 'MM-dd-yyyy',
                'required' => false,
                'attr' => array('class' => 'jq-date input-small')
            ))
        ;

        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) {
            $data = $event->getData();
            $data->setType(ClientAdditionalContact::TYPE_SPOUSE);
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAdditionalContact'
        ));
    }

    public function getName()
    {
        return 'spouse';
    }
}