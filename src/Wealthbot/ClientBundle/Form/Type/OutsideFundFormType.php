<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.09.12
 * Time: 16:01
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Wealthbot\AdminBundle\Form\Type\FundFormType;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @Deprecated
 * Class OutsideFundFormType
 *
 * @package Wealthbot\ClientBundle\Form\Type
 */
class OutsideFundFormType extends AbstractType
{
    private $account;

    private $em;

    public function __construct(EntityManager $em, ClientAccount $account)
    {
        $this->em = $em;
        $this->account = $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('security', new FundFormType())
        ;

        if($this->account) {
            $builder->add('account_id', 'hidden', array( 'data' => $this->account->getId(), 'property_path' => false ));
        }

        $em = $this->em;
        $ria = $this->account->getClient()->getProfile()->getRia();

        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) use ($em, $ria){

            $form = $event->getForm();
            $data = $event->getData();

            $existSecurity = $em->getRepository('WealthbotAdminBundle:Security')->findOneBy(array('name' => $data['security']['name'], 'symbol' => $data['security']['symbol']));
            if($existSecurity) {
                $form->get('security')->setData($existSecurity);

                $existSecurityAssignment = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findOneBy(array(
                    'ria_user_id' => $ria->getId(),
                    'security_id'     => $existSecurity->getId()
                ));

                if($existSecurityAssignment){
                    $form->setData($existSecurityAssignment);
                }
            }
        });

        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) use ($ria){

            $data = $event->getData();

            if($data){
//                $data->setRia($ria); Deprecated
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
       $resolver->setDefaults(array(
           'data_class' => 'Wealthbot\AdminBundle\Entity\SecurityAssignment'
       ));
    }

    public function getName()
    {
       return 'outside_fund';
    }
}
