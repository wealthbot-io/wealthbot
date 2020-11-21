<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.10.12
 * Time: 14:13
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Security;
use App\Entity\SecurityPrice;

class SecurityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('securityType', EntityType::class, [
                'class' => 'App\Entity\Security',
                'property_path' => 'name',
                'placeholder' => 'Choose an Option',
            ])
            ->add('name', TextType::class)
            ->add('symbol', TextType::class)
            ->add('expense_ratio')
            ->add('price', NumberType::class, [
                'data' => $this->getPriceData($builder->getData()),
                'mapped' => false,
                'grouping' => true,
            ]);
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

        return;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Security',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin_security';
    }

    public function __toString()
    {
        return self::class;
    }
}
