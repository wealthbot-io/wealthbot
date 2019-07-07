<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 26.10.12
 * Time: 14:34
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\CeModel;
use App\Entity\CeModelEntity;
use App\Form\EventListener\PortfolioModelEntityTypeEventsListener;
use App\RiskManagement\BaselinePortfolio;

class PortfolioModelEntityFormType extends AbstractType
{
    /** @var CeModel $portfolioModel */
    private $portfolioModel;

    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    private $user;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $model = $this->portfolioModel;
        $strategy = $model->getParent();

        $subscriber = new PortfolioModelEntityTypeEventsListener($builder->getFormFactory(), $this->em, $this->portfolioModel, $this->user);

        $builder->add('assetClass', EntityType::class, [
            'class' => 'App\\Entity\\AssetClass',
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
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $this->portfolioModel = $event->getForm()->getConfig()->getOption('portfolioModel');
            $this->em = $event->getForm()->getConfig()->getOption('em');
            $this->user = $event->getForm()->getConfig()->getOption('user');
        });
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var CeModelEntity $data */
        $data = $event->getData();

        $stock = 0;
        $bond = 0;

        /** @var \Repository\CeModelEntityRepository $modelRepo */
        $modelRepo = $this->em->getRepository('App\Entity\CeModel');
        $modelEntities = $this->em->getRepository('App\Entity\CeModelEntity')->findBy([
            'modelId' => $this->portfolioModel->getId(),
        ]);

        foreach ($modelEntities as $entity) {
            if (!$data->getId() || ($data->getId() !== $entity->getId())) {
                if ('Stocks' === $entity->getAssetClass()->getType()) {
                    $stock += $entity->getPercent();
                }

                if ('Bonds' === $entity->getAssetClass()->getType()) {
                    $bond += $entity->getPercent();
                }
            }
        }

        $overAll = $stock + $bond + $data->getPercent();

        if ($this->portfolioModel->isStrategy()) {
            $percentage = BaselinePortfolio::$modelPercentage[$this->portfolioModel->getParent()->getSlug()];

            $overAllStock = $data->getPercent() + $stock;
            $overAllBond = $data->getPercent() + $bond;

            if ('Stocks' === $data->getAssetClass()->getType()) {
                if ($overAllStock > $percentage['stock']) {
                    $form->get('percent')->addError(new \Symfony\Component\Form\FormError('Sum of the stocks percents must be equal :value.', [':value' => $percentage['stock']]));
                }
            }

            if ('Bonds' === $data->getAssetClass()->getType()) {
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
            'data_class' => 'App\Entity\CeModelEntity',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'rx_admin_model_entity_form';
    }
}
