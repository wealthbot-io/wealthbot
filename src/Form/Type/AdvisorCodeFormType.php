<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 24.03.14
 * Time: 15:39.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvisorCodeFormType extends AbstractType
{
    protected $em;
    protected $custodian;
    protected $riaCompany;

    public function __construct($custodian = null, $riaCompany = null, $em = null)
    {
        $this->custodian = $custodian;
        $this->riaCompany = $riaCompany;
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
        $this->em = $options['em'];
        $this->custodian = $options['custodian'] ? $options['custodian'] : $this->custodian;
        $this->riaCompany = $options['ria'] ?$options['ria'] : $this->riaCompany ;

        $builder->add('name', TextType::class, ['attr' => ['class' => 'input-small']]);
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
            'data_class' => 'App\Entity\AdvisorCode',
            'em' => null,
            'ria' => null,
            'custodian' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_advisor_code';
    }
}
