<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\Subclass;
use Wealthbot\AdminBundle\Form\Type\FundFormType;
use Wealthbot\AdminBundle\Repository\SecurityAssignmentRepository;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Wealthbot\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class OutsideFundAssociationFormType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    private $ria;

    /**
     * @var \Wealthbot\ClientBundle\Entity\ClientAccount
     */
    private $account;


    public function __construct(EntityManager $em, User $ria, ClientAccount $account)
    {
        $this->em = $em;
        $this->ria = $ria;
        $this->account = $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ria = $this->ria;
        $em = $this->em;
        $account = $this->account;
        $factory = $builder->getFormFactory();

        $data = $builder->getData();

        $isPreferred = false;
        if($data && $data->getid()){
            $association = $em->getRepository('WealthbotClientBundle:AccountOutsideFund')->findOneBy(array('account_id' => $account->getId(), 'security_assignment_id' => $data->getId()));
            if($association) {
                $isPreferred = $association->getIsPreferred();
            }
        }

        $selectedModel = $ria->getRiaCompanyInformation()->getPortfolioModel();

        $builder
            ->add('security', new FundFormType())
            ->add('subclasses', 'entity', array(
                'class'         => 'WealthbotAdminBundle:Subclass',
                'query_builder' => function(EntityRepository $er) use ($selectedModel) {

                    $q = $er->createQueryBuilder('s')
                        ->leftJoin('s.assetClass', 'ac')
                        ->where('ac.model_id=:model_id')
                        ->setParameter('model_id', $selectedModel->getId());

                    return $q;
                },
                'property_path' => 'subclass',
                'empty_value'   => 'Choose an Option',
                'required'      => false,
            ))
            ->add('is_preferred', 'checkbox', array(
                'property_path' => false,
                'required'      => false,
                'data'          => $isPreferred
            ))
            ->add('expense_ratio', 'hidden', array('data' => 0.0001))
        ;

        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) use ($em, $ria, $factory){

            $form = $event->getForm();
            $data = $event->getData();

            // Search fund with selected Name and Symbol in our DB
            $existSecurity = $em->getRepository('WealthbotAdminBundle:Security')->findOneBy(array('name' => $data['security']['name'], 'symbol' => $data['security']['symbol']));
            if($existSecurity) {

                $form->get('security')->setData($existSecurity);

                $existSecurityAssignment = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findOneBy(array(
                    'ria_user_id' => $ria->getId(),
                    'security_id'     => $existSecurity->getId()
                ));

                if($existSecurityAssignment) {

                    if($existSecurityAssignment->getSubclass()) {
                        $form->get('subclasses')->setData($existSecurityAssignment->getSubclass());
                    }

                    if(!$existSecurityAssignment->getSubclass() && isset($data['subclasses']) && $data['subclasses']) {
                        $form->setData($existSecurityAssignment);
                    }

                    if($existSecurityAssignment->getSubclass() && empty($data['subclasses'])){
                        $form->addError(new FormError('You have already associated this security with '. $existSecurityAssignment->getSubclass()->getName() .' and you can\'t remove association.'));
                    }

                    if($existSecurityAssignment->getSubclass() && $data['subclasses']
                        && ($existSecurityAssignment->getSubclass()->getId() != $data['subclasses'])
                        && (isset($data['is_preferred']) && $data['is_preferred'])
                    ) {
                        if(isset($data['is_override']) && $data['is_override']) {
                            $form->add($factory->createNamed('is_override', 'hidden', 1, array('property_path' => false)));
                            $form->setData($existSecurityAssignment);
                        } else {
                            $form->addError(new FormError('You have already associated this security with '. $existSecurityAssignment->getSubclass()->getName() .' . Please confirm that you want to override it ?'));
                            $form->add($factory->createNamed('is_override', 'hidden', 1, array('property_path' => false, 'attr' => array('value' => 1))));
                        }
                    }

                    if($existSecurityAssignment->getSubclass() && $data['subclasses'] && ($existSecurityAssignment->getSubclass()->getId() !== $data['subclasses'])){
                        $form->setData($existSecurityAssignment);
                    }
                }
            } else {

                $security = new Security();
                $security->setName($data['security']['name']);
                $security->setSymbol($data['security']['symbol']);

                $em->persist($security);
                $em->flush();

                $form->get('security')->setData($security);
            }

            if(isset($data['subclasses']) && $data['subclasses']) {
                $form->get('is_preferred')->setData(true);
            }
        });


        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) use ($em, $ria, $account, $factory){

            $form = $event->getForm();
            $data = $event->getData();

            $data->setRia($ria);

            if($data->getSubclass() && !$form->get('is_preferred')->getData()) {
                $form->get('is_preferred')->addError(new FormError('Required'));
            }

            // If RIA check security as preferred
            if($form->get('is_preferred')->getData()) {

                // In this case Subclass is required
                if(!$data->getSubclass()) {

                    $form->get('subclasses')->addError(new FormError('Subclass is required.'));

                } else {

                    $assetClass = $data->getSubclass()->getAssetClass();

                    $dql_security = "";
                    if($data->getId()){
                        $dql_security = "AND sec.id != :security_assignment_id";
                    }

                    $dql = "SELECT aof, sec, s
                                FROM WealthbotClientBundle:AccountOutsideFund aof
                                LEFT JOIN aof.securityAssignment sec
                                LEFT JOIN sec.subclass s
                                WHERE aof.account_id = :account_id AND sec.ria_user_id = :ria_id AND aof.is_preferred = 1
                                    AND s.asset_class_id = :asset_class_id $dql_security
                                GROUP BY s.asset_class_id";

                    $stmt = $em->createQuery($dql);

                    $stmt->setParameters(array(
                        'account_id'     => $account->getId(),
                        'ria_id'         => $ria->getId(),
                        'asset_class_id' => $assetClass->getId(),
                    ));

                    if($data->getId()){
                        $stmt->setParameter('security_assignment_id', $data->getId());
                    }

                    $results = $stmt->getResult();

                    if(count($results) > 0) {
                        $form->get('subclasses')->addError(new FormError('You cannot have two of the same asset classes being bought in one current retirement plan.'));
                    }
                }
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
