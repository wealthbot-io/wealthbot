<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 24.03.14
 * Time: 15:39.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\AdminBundle\Entity\Custodian;

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
        $builder->add('name', 'text', ['attr' => ['class' => 'input-small']]);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitData']);
    }

    public function onSubmitData(FormEvent $event)
    {
        $advisorCode = $event->getData();

        $advisorCode->setCustodian($this->custodian);
        $advisorCode->setCustodianId($this->custodian->getId());
        $advisorCode->setRiaCompany($this->riaCompany);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\RiaBundle\Entity\AdvisorCode',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_advisor_code';
    }
}
