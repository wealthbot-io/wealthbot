<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.10.12
 * Time: 15:25
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class RiaCompanyInformationFourType extends AbstractType
{
    /** @param \App\Entity\User $user */
    protected $user;

    /** @var bool $isPreSave */
    protected $isPreSave;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->isPreSave = $options['isPreSave'];


        //$riaCompanyInformation = $this->user->getRiaCompanyInformation();
        /*if ($riaCompanyInformation) {
            $isSearchable = is_null($riaCompanyInformation->getIsSearchableDb()) ? true : $riaCompanyInformation->getIsSearchableDb();
        } else {
            $isSearchable = true;
        }*/

        $builder
            ->add('is_searchable_db', HiddenType::class, [
//                'choices' => array( true => 'Yes', false => 'No'),
//                'required' => false,
//                'expanded' => true,
                'data' => true,
//                'hidden' => true
            ])
            ->add('min_asset_size', NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'required' => false,
            ])
            ->add('logo_file', FileType::class, ['required' => false])
        ;

        if (!$this->isPreSave) {
            $this->addOnSubmitValidator($builder);
        }
    }

    protected function addOnSubmitValidator(FormBuilderInterface $builder)
    {
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
            $form = $event->getForm();

            /** @param \App\Entity\RiaCompanyInformation $data */
            $data = $event->getData();

            /*$isSearchable = $form->get('is_searchable_db')->getData();
            if (!$isSearchable) {
                $data->setMinAssetSize(null);
            } else
            */
            if (!is_numeric($data->getMinAssetSize())) {
                $form->get('min_asset_size')->addError(new \Symfony\Component\Form\FormError('Required.'));
            }

            // If ria already have logo do not show error for him
            if (!$data->getLogo() && !is_object($form->get('logo_file')->getData())) {
                $form->get('logo_file')->addError(new FormError('Required.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiaCompanyInformation',
            'user' => null,
            'isPreSave' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_riacompanyinformationtype';
    }
}
