<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 29.03.14
 * Time: 22:35
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdvisorCodesCollectionFormType extends AbstractType
{
    protected $em, $riaCompany, $custodian;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function setRiaCompany($riaCompany)
    {
        $this->riaCompany = $riaCompany;
    }

    public function setCustodian($custodian)
    {
        $this->custodian = $custodian;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $advisorCodeFormType = new AdvisorCodeFormType($this->em);

        $advisorCodeFormType->setCustodian($this->custodian);
        $advisorCodeFormType->setRiaCompany($this->riaCompany);

        $builder
            ->add('advisorCodes', 'collection', array(
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'type' => $advisorCodeFormType
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'ria_advisor_codes';
    }
}