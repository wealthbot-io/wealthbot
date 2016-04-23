<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 24.03.14
 * Time: 15:39
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\Custodian;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdvisorCodeFormType extends AbstractType
{
    protected $em, $custodian, $riaCompany;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function setCustodian($custodian)
    {
        $this->custodian = $custodian;
    }

    public function setRiaCompany($riaCompany)
    {
        $this->riaCompany = $riaCompany;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('attr' => array('class' => 'input-small')));
        $builder->addEventListener(FormEvents::BIND, array($this, 'onBindData'));
    }

    public function onBindData(FormEvent $event)
    {
        $advisorCode = $event->getData();

        $advisorCode->setCustodian($this->custodian);
        $advisorCode->setCustodianId($this->custodian->getId());
        $advisorCode->setRiaCompany($this->riaCompany);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Entity\AdvisorCode'
        ));
    }

    public function getName()
    {
        return 'ria_advisor_code';
    }
}
