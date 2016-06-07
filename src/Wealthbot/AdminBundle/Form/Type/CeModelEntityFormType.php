<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 26.10.12
 * Time: 14:34
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Form\EventListener\CeModelEntityTypeEventsListener;
use Wealthbot\AdminBundle\Model\CeModelInterface;
use Wealthbot\UserBundle\Entity\User;

class CeModelEntityFormType extends AbstractType
{
    /** @var CeModel $ceModel */
    private $ceModel;

    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    private $user;

    private $isQualifiedModel;

    public function __construct(CeModelInterface $ceModel, EntityManager $em, User $user, $isQualifiedModel = false)
    {
        $this->ceModel = $ceModel;
        $this->em = $em;
        $this->user = $user;
        $this->isQualifiedModel = $isQualifiedModel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $model = $this->ceModel;
        $parentModel = $model->getParent();

        $subscriber = new CeModelEntityTypeEventsListener($builder->getFormFactory(), $this->em, $this->ceModel, $this->user, $this->isQualifiedModel);

        $builder->add('assetClass', 'entity', [
            'class' => 'Wealthbot\\AdminBundle\\Entity\\AssetClass',
            'placeholder' => 'Choose Asset Class',
            'query_builder' => $this->em->getRepository('WealthbotAdminBundle:AssetClass')->getAssetClassesForModelQB($parentModel->getId()),
        ]);

        $builder->addEventSubscriber($subscriber);
        $builder->add('percent');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\CeModelEntity',
        ]);
    }

    public function getBlockPrefix()
    {
        if ($this->user->hasRole('ROLE_RIA')) {
            return 'rx_ria_model_entity_form';
        } else {
            return 'rx_admin_model_entity_form';
        }
    }
}
