<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 02.04.13
 * Time: 15:46
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\ClientBundle\Entity\OneTimeContribution;
use Wealthbot\ClientBundle\Form\Validator\BankInformationFormValidator;

class OneTimeContributionFormType extends AccountContributionFormType
{
    protected function updateData(FormInterface $form, $data)
    {
        if ($data->getType() !== OneTimeContribution::TYPE_FUNDING_BANK) {
            $bankInformation = $data->getBankInformation();
            if ($bankInformation && $bankInformation->getId()) {
                $this->em->remove($bankInformation);
            }

            $data->setBankInformation(null);

            if ($form->has('start_transfer_date_month') && $form->has('start_transfer_date_day')) {
                $data->setStartTransferDate(null);
            }
            if ($form->has('amount')) {
                $data->setAmount(null);
            }
        }
    }

    protected function validateFields(FormInterface $form, $data)
    {
        if (!in_array($data->getType(), OneTimeContribution::getTypeChoices())) {
            $form->get('type')->addError(new FormError('Choose an option.'));
        } else {
            if ($data->getType() === OneTimeContribution::TYPE_FUNDING_BANK) {
                $bankInformationValidator = new BankInformationFormValidator(
                    $form->get('bankInformation'),
                    $data->getBankInformation()
                );

                $bankInformationValidator->validate();

                if (!($data->getStartTransferDate() instanceof \DateTime)) {
                    $form->get('start_transfer_date_month')->addError(new FormError('Enter correct date.'));
                } else {
                    $minDate = new \DateTime('+5 days');

                    if ($data->getStartTransferDate() < $minDate) {
                        $form->get('start_transfer_date_month')->addError(
                            new FormError(
                                'The start of your transfer should be at least 5 days after today’s date.'
                            )
                        );
                    }
                }

                if (!$data->getAmount()) {
                    $form->get('amount')->addError(new FormError('Required.'));
                }

                if ($form->has('transaction_frequency')) {
                    $frequency = $form->get('transaction_frequency')->getData();

                    if (null === $frequency || !is_numeric($frequency)) {
                        $form->get('transaction_frequency')->addError(new FormError('Choose an option.'));
                    }
                }

                if ($form->has('contribution_year')) {
                    $contributionYear = $data->getContributionYear();

                    if (null === $contributionYear || !is_numeric($contributionYear)) {
                        $form->get('contribution_year')->addError(new FormError('Enter valid year.'));
                    } else {
                        $currDate = new \DateTime();
                        $currYear = $currDate->format('Y');
                        $minDate = new \DateTime($currYear.'-01-01');
                        $maxDate = new \DateTime($currYear.'-04-15');

                        $startTransferDate = $data->getStartTransferDate();
                        if (($startTransferDate < $minDate) || ($startTransferDate > $maxDate)) {
                            if ($contributionYear !== $currYear) {
                                $form->get('contribution_year')->addError(
                                    new FormError(sprintf('Value should be equal %s', $currYear))
                                );
                            }
                        } else {
                            $prevYear = $currDate->add(\DateInterval::createFromDateString('-1 year'))->format('Y');

                            if ($contributionYear !== $currYear && $contributionYear !== $prevYear) {
                                $form->get('contribution_year')->addError(
                                    new FormError(sprintf('Value should be equal %s or %s', $prevYear, $currYear))
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\OneTimeContribution',
        ]);
    }
}
