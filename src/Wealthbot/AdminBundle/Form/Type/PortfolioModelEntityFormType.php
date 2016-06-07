<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 26.10.12
 * Time: 14:34
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Entity\CeModelEntity;
use Wealthbot\AdminBundle\Form\EventListener\PortfolioModelEntityTypeEventsListener;
use Wealthbot\RiaBundle\RiskManagement\BaselinePortfolio;
use Wealthbot\UserBundle\Entity\User;

class PortfolioModelEntityFormType extends AbstractType
{
    /** @var CeModel $portfolioModel */
    private $portfolioModel;

    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    private $user;

    public function __construct(CeModel $portfolioModel, \Doctrine\ORM\EntityManager $em, User $user)
    {
        $this->portfolioModel = $portfolioModel;
        $this->em = $em;
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $model = $this->portfolioModel;
        $strategy = $model->getParent();

        $subscriber = new PortfolioModelEntityTypeEventsListener($builder->getFormFactory(), $this->em, $this->portfolioModel, $this->user);

        $builder->add('assetClass', 'entity', [
            'class' => 'Wealthbot\\AdminBundle\\Entity\\AssetClass',
            'placeholder' => 'Choose Asset Class',
            'query_builder' => function (EntityRepository $er) use ($strategy) {
                return $er->createQueryBuilder('ac')
                    ->andWhere('ac.model_id = :model_id')
                    ->setParameter('model_id', $strategy->getId())
                    ->orderBy('ac.id', 'ASC');
            },
        ]);
        $builder->addEventSubscriber($subscriber);
        $builder->add('percent');

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var CeModelEntity $data */
        $data = $event->getData();

        $stock = 0;
        $bond = 0;

        /** @var \Wealthbot\AdminBundle\Repository\CeModelEntityRepository $modelRepo */
        $modelRepo = $this->em->getRepository('WealthbotAdminBundle:CeModel');
        $modelEntities = $this->em->getRepository('WealthbotAdminBundle:CeModelEntity')->findBy([
            'modelId' => $this->portfolioModel->getId(),
        ]);

        foreach ($modelEntities as $entity) {
            if (!$data->getId() || ($data->getId() !== $entity->getId())) {
                if ($entity->getAssetClass()->getType() === 'Stocks') {
                    $stock += $entity->getPercent();
                }

                if ($entity->getAssetClass()->getType() === 'Bonds') {
                    $bond += $entity->getPercent();
                }
            }
        }

        $overAll = $stock + $bond + $data->getPercent();

        if ($this->portfolioModel->isStrategy()) {
            $percentage = BaselinePortfolio::$modelPercentage[$this->portfolioModel->getParent()->getSlug()];

            $overAllStock = $data->getPercent() + $stock;
            $overAllBond = $data->getPercent() + $bond;

            if ($data->getAssetClass()->getType() === 'Stocks') {
                if ($overAllStock > $percentage['stock']) {
                    $form->get('percent')->addError(new \Symfony\Component\Form\FormError('Sum of the stocks percents must be equal :value.', [':value' => $percentage['stock']]));
                }
            }

            if ($data->getAssetClass()->getType() === 'Bonds') {
                if ($overAllBond > $percentage['bond']) {
                    $form->get('percent')->addError(new \Symfony\Component\Form\FormError('Sum of the bonds percents must be equal :value.', [':value' => $percentage['bond']]));
                }
            }
        }

        if (($overAll) > 100) {
            $form->get('percent')->addError(new \Symfony\Component\Form\FormError('Sum of the percents must be equal 100.'));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\CeModelEntity',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'rx_admin_model_entity_form';
    }
}
