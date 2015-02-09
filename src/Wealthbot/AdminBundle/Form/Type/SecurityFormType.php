<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.10.12
 * Time: 14:13
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\AssetClass;
use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Entity\SecurityPrice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SecurityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('securityType', 'entity', array(
                'class' => 'Wealthbot\AdminBundle\Entity\SecurityType',
                'property' => 'description',
                'empty_value' => 'Choose an Option'
            ))
            ->add('name', 'text')
            ->add('symbol', 'text')
            ->add('expense_ratio')
            ->add('price', 'number', array(
                'data' => $this->getPriceData($builder->getData()),
                'mapped' => false,
                'grouping' => true
            ));
    }

    private function getPriceData(Security $security = null)
    {
        if ($security) {
            /** @var SecurityPrice $price */
            foreach ($security->getSecurityPrices() as $price) {
                if ($price->getIsCurrent()) {
                    return $price->getPrice();
                }
            }
        }

        return null;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\Security'
        ));
    }

    public function getName()
    {
        return 'admin_security';
    }
}
