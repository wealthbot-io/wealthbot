<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.09.12
 * Time: 16:01
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Type\FundFormType;
use App\Entity\ClientAccount;

/**
 * @Deprecated
 * Class OutsideFundFormType
 */
class OutsideFundFormType extends AbstractType
{
    private $account;

    private $em;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $this->account = $options['account'];


        $builder
            ->add('security', FundFormType::class)
        ;

        if ($this->account) {
            $builder->add('account_id', HiddenType::class, ['data' => $this->account->getId()]);
        }

        $em = $this->em;
        $ria = $this->account->getClient()->getProfile()->getRia();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($em, $ria) {
            $form = $event->getForm();
            $data = $event->getData();

            $existSecurity = $em->getRepository('App\Entity\Security')->findOneBy(['name' => $data['security']['name'], 'symbol' => $data['security']['symbol']]);
            if ($existSecurity) {
                $form->get('security')->setData($existSecurity);

                $existSecurityAssignment = $em->getRepository('App\Entity\SecurityAssignment')->findOneBy([
                    'ria_user_id' => $ria->getId(),
                    'security_id' => $existSecurity->getId(),
                ]);

                if ($existSecurityAssignment) {
                    $form->setData($existSecurityAssignment);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\SecurityAssignment',
            'em' => null,
            'account' => null
       ]);
    }

    public function getBlockPrefix()
    {
        return 'outside_fund';
    }
}
