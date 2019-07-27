<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 26.10.12
 * Time: 14:34
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\CeModel;
use App\Form\EventListener\CeModelEntityTypeEventsListener;

class CeModelEntityFormType extends AbstractType
{
    /** @var CeModel $ceModel */
    private $ceModel;

    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    private $user;

    private $isQualifiedModel;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->ceModel = $options['model'];
        $this->em = $options['em'];
        $this->isQualifiedModel = $options['isQualifiedModel'];


        $model = $this->ceModel;
        $parentModel = $model->getParent();

        $subscriber = new CeModelEntityTypeEventsListener($builder->getFormFactory(), $this->em, $this->ceModel, $this->user, $this->isQualifiedModel);

        $builder->add('assetClass', EntityType::class, [
            'class' => 'App\\Entity\\AssetClass',
            'placeholder' => 'Choose Asset Class',
            //'query_builder' => $this->em->getRepository('App\Entity\AssetClass')->getAssetClassesForModelQB($parentModel->getId()),
        ]);

        $builder->addEventSubscriber($subscriber);
        $builder->add('percent', null, [
            'attr' => [
                'placeholder' => 'Percent',
                'class' => 'col-1'
            ]
        ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $this->ceModel = $event->getForm()->getConfig()->getOption('ceModel');
            $this->em = $event->getForm()->getConfig()->getOption('em');
            $this->user = $event->getForm()->getConfig()->getOption('user');
            $this->isQualifiedModel = $event->getForm()->getConfig()->getOption('isQualifiedModel');
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\CeModelEntity',
            'user' => null,
            'em' => null,
            'model' => null,
            'isQualifiedModel' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'rx_ria_model_entity_form';
    }
}
