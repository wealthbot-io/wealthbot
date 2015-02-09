<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.10.12
 * Time: 17:56
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PortfolioModelFormType extends AbstractType
{
    private $parent;

    private $owner;

    public function __construct(CeModel $parent, User $owner)
    {
        $this->parent = $parent;
        $this->owner  = $owner;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $owner = $this->owner;
        $parent = $this->parent;

        $builder->add('name', 'text');

        // Add Event - when we create a model then we will need to set owner and parent
        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event) use ($owner, $parent){

            $data = $event->getData();

            if($data && !$data->getId()) {

                $data->setOwner($owner);
                $data->setParent($parent);
            }
        });
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\CeModel'
        ));
    }

    public function getName()
    {
        return 'strategy';
    }
}
