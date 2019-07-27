<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.10.12
 * Time: 18:00
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ModelAssumption;
use App\Entity\PortfolioModel;
use App\Entity\User;

class StrategyModelFormType extends AbstractType
{
    /** @var EntityManager */
    private $em;

    /** @var \App\Entity\PortfolioModel */
    private $thirdParty;

    /** @var User */
    private $user;

    /** @var ModelAssumption */
    private $assumption;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = range(0, 10);

        $thirdParty = $this->thirdParty;
        $existsRatings = [];
        $riskRating = null;
        $assumption = $this->assumption;

        if ($thirdParty) {
            $data = $builder->getData();
            if ($data instanceof PortfolioModel && $data->getId()) {
                $ownerId = null;
                if ($data->getParent()->getOwner()) {
                    $ownerId = $data->getParent()->getOwner();
                } else {
                    $ownerId = $this->user->getId();
                }

                $modelRiskRating = $this->em->getRepository('App\Entity\ModelRiskRating')->findOneBy([
                    'owner_id' => $ownerId,
                    'model_id' => $data->getId(),
                ]);

                if ($modelRiskRating) {
                    $riskRating = $modelRiskRating->getRating();
                }
            }
            $existsRatings = $this->getExistsRatings($riskRating);
            $modelsCount = $this->getModelsCount();
            $choices = range(0, $modelsCount);
        }

        $builder->add('name', TextType::class);

        if ($this->user->isSuperAdmin() || $assumption) {
            $builder->add('assumption', ModelAssumptionFormType::class, [
                'mapped' => false,
                'data' => $assumption,
                'em' => $this->em
            ]);
        }

        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory, $choices, $riskRating) {
            $data = $event->getData();
            $form = $event->getForm();

            $this->thirdParty = $form->getConfig()->getOption('thirdParty');
            $this->user = $form->getConfig()->getOption('user');
            $this->em = $form->getConfig()->getOption('em');
            $this->assumption = $form->getConfig()->getOption('assumption');

            if (null === $data) {
                return;
            }
            // check if the product object is not "new"
            if ($data->getId()) {
                $form->add($factory->createNamed('risk_rating', ChoiceType::class, $riskRating, [
                    'placeholder' => 'Select Risk Rating',
                    'choices' => $choices,
                    'auto_initialize' => false,
                ]));
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($existsRatings) {
            $form = $event->getForm();

            if ($form->has('risk_rating')) {
                $riskRating = $form->get('risk_rating')->getData();
                if (in_array($riskRating, $existsRatings)) {
                    $form->get('risk_rating')->addError(new FormError('The risk with parameter :risk is already exists.', $riskRating));
                }
            }
        });
    }

    protected function getExistsRatings($exclude)
    {
        $query = $this->em->getRepository('App\Entity\ModelRiskRating')->createQueryBuilder('mrr')
            ->leftJoin('mrr.model', 'pm')
            ->leftJoin('pm.parent', 'p')
            ->where('mrr.owner_id = :owner_id')
            ->andWhere('p.id = :parent_id')
            ->setParameter('owner_id', $this->user->getId())
            ->setParameter('parent_id', $this->thirdParty->getId())
            ->getQuery();

        $riaRiskRatings = $query->getResult();

        $existsRatings = [];
        foreach ($riaRiskRatings as $object) {
            if ($object->getRating() !== $exclude) {
                $existsRatings[] = $object->getRating();
            }
        }

        return $existsRatings;
    }

    protected function getModelsCount()
    {
        $qb = $this->em->getRepository('App\Entity\PortfolioModel')->createQueryBuilder('pm')
            ->select('count(pm.id)')
            ->leftJoin('pm.parent', 'p')
            ->andWhere('p.id = :parent_id')
            ->setParameter('parent_id', $this->thirdParty->getId())
            ->getQuery();

        return $qb->getSingleScalarResult();
    }

    protected function validateUniqueChildrenName(PortfolioModel $parent, $name)
    {
        $q = $this->em->getRepository('App\Entity\PortfolioModel')
            ->createQueryBuilder('pm')
            ->where('pm.parent_id = :parent_id')
            ->andWhere('pm.name = :name')
            ->setParameters([
                'parent_id' => $parent->getId(),
                'name' => $name,
            ])->getQuery();

        return $q->getFirstResult() ? true : false;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\PortfolioModel',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'third_party_model';
    }
}
