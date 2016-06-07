<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 08.02.13
 * Time: 13:21
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\SignatureBundle\Manager\DocumentSignatureManager;

class TransferReviewFormType extends AbstractType
{
    private $manager;
    private $account;

    public function __construct(DocumentSignatureManager $manager, ClientAccount $account)
    {
        $this->manager = $manager;
        $this->account = $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('is_agree', 'checkbox', [
            'label' => 'I agree.',
            'constraints' => [
                new NotBlank(['message' => 'You have to agree to the terms.']),
            ],
        ]);

        $manager = $this->manager;
        $account = $this->account;
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($manager, $account) {
            $form = $event->getForm();

            if (!$manager->isApplicationSigned($account->getId())) {
                $form->get('is_agree')->addError(
                    new FormError('You have not signed applications. Please sign all applications.')
                );
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'transfer_review';
    }
}
