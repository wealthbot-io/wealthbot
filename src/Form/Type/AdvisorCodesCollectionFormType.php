<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 29.03.14
 * Time: 22:35.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvisorCodesCollectionFormType extends AbstractType
{
    public $em;

    public $custodian;

    public $riaCompany;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $this->custodian = $options['custodian'];
        $this->riaCompany = $options['riaCompany'];

        $builder
            ->add('advisorCodes', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_type' => AdvisorCodeFormType::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation' => true,
            'em' => null,
            'custodian' => null,
            'riaCompany' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_advisor_codes';
    }
}
