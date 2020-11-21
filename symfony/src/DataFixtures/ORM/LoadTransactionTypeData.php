<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 22.08.13
 * Time: 13:35
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\TransactionType;

class LoadTransactionTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    private $data = [
        [
            'name' => 'BUY',
            'activity' => 'buy',
            'report_as' => null,
            'description' => 'Buy',
        ],
        [
            'name' => 'BUYO',
            'activity' => 'buy',
            'report_as' => null,
            'description' => 'Buy/Open (options only)',
        ],
        [
            'name' => 'BUYC',
            'activity' => 'cover',
            'report_as' => null,
            'description' => 'Buy/Close (options only)',
        ],
        [
            'name' => 'COVER',
            'activity' => 'cover',
            'report_as' => null,
            'description' => 'Cover (equities only)',
        ],
        [
            'name' => 'SELL',
            'activity' => 'sell',
            'report_as' => null,
            'description' => 'Sell',
        ],
        [
            'name' => 'SELLC',
            'activity' => 'sell',
            'report_as' => null,
            'description' => 'Sell/Close (options only)',
        ],
        [
            'name' => 'SELLO',
            'activity' => 'short',
            'report_as' => null,
            'description' => 'Sell/Open (options only)',
        ],
        [
            'name' => 'SHORT',
            'activity' => 'short',
            'report_as' => null,
            'description' => 'Short (equities only)',
        ],
        [
            'name' => 'MAT',
            'activity' => 'sell',
            'report_as' => null,
            'description' => 'Bond Redemption',
        ],
        [
            'name' => 'DIV',
            'activity' => 'income',
            'report_as' => 'Dividend',
            'description' => 'Dividend',
        ],
        [
            'name' => 'INT',
            'activity' => 'income',
            'report_as' => 'Interest',
            'description' => 'Interest',
        ],
        [
            'name' => 'SHTDV',
            'activity' => 'income',
            'report_as' => 'Dividend',
            'description' => 'Short Dividend',
        ],
        [
            'name' => 'SGAIN',
            'activity' => 'income',
            'report_as' => 'ShortGain',
            'description' => 'Short Term Capital Gain',
        ],

        [
            'name' => 'LGAIN',
            'activity' => 'income',
            'report_as' => 'LongGain',
            'description' => 'Long Term Capital Gain',
        ],
        [
            'name' => 'MGAIN',
            'activity' => 'income',
            'report_as' => 'MidGain',
            'description' => 'Mid Term Capital Gain',
        ],
        [
            'name' => 'UGAIN',
            'activity' => 'income',
            'report_as' => 'UnclassedGain',
            'description' => 'Unclassified Capital Gain',
        ],
        [
            'name' => 'QDV',
            'activity' => 'income',
            'report_as' => 'QualifiedDividend',
            'description' => 'Qualified Dividend',
        ],
        [
            'name' => 'NDV',
            'activity' => 'income',
            'report_as' => 'NonQualifiedDividend',
            'description' => 'Non-qualified Dividend',
        ],
        [
            'name' => 'PIL',
            'activity' => 'income',
            'report_as' => 'PaymentInLieu',
            'description' => 'Payment in Lieu',
        ],
        [
            'name' => '5YEAR',
            'activity' => 'income',
            'report_as' => '5YearGain',
            'description' => '5 Year Gain',
        ],
        [
            'name' => 'TCGC',
            'activity' => 'income',
            'report_as' => 'Pre5/6LongGain',
            'description' => 'Pre 5/6 Long Gain',
        ],
        [
            'name' => 'CLIEU',
            'activity' => null,
            'report_as' => null,
            'description' => 'Cash in Lieu Transaction',
        ],
        [
            'name' => 'MEXP',
            'activity' => 'expense',
            'report_as' => 'MarginExpense',
            'description' => 'Margin Expense',
        ],
        [
            'name' => 'MFEE',
            'activity' => 'expense',
            'report_as' => 'ManagementFee',
            'description' => 'Management Fee',
        ],
        [
            'name' => 'EXP',
            'activity' => 'expense',
            'report_as' => 'ExpenseFee',
            'description' => 'Other Expenses',
        ],
        [
            'name' => 'TEFRA',
            'activity' => 'expense',
            'report_as' => 'FedWithhold',
            'description' => 'Fed Withholding',
        ],
        [
            'name' => 'NRTAX',
            'activity' => 'expense',
            'report_as' => 'NonResTax',
            'description' => 'Non Resident Tax',
        ],
        [
            'name' => 'FRTAX',
            'activity' => 'expense',
            'report_as' => 'ForeignTaxPd',
            'description' => 'Foreign Tax Paid',
        ],
        [
            'name' => 'EXABP',
            'activity' => 'expense',
            'report_as' => 'ABPFee',
            'description' => 'ABP Fee',
        ],
        [
            'name' => 'EXACC',
            'activity' => 'expense',
            'report_as' => 'AccountingFee',
            'description' => 'Accounting Fee',
        ],
        [
            'name' => 'EXACT',
            'activity' => 'expense',
            'report_as' => 'ActuarialFee',
            'description' => 'Actuarial Fee',
        ],
        [
            'name' => 'EXAPP',
            'activity' => 'expense',
            'report_as' => 'AppraisalFee',
            'description' => 'Appraisal Fee',
        ],
        [
            'name' => 'EXCON',
            'activity' => 'expense',
            'report_as' => 'ContractFee',
            'description' => 'Contract Fee',
        ],
        [
            'name' => 'EXLEG',
            'activity' => 'expense',
            'report_as' => 'LegalFee',
            'description' => 'Legal Fee',
        ],
        [
            'name' => 'EXSAL',
            'activity' => 'expense',
            'report_as' => 'SalariesAllowance',
            'description' => 'Salaries Allowance',
        ],

        [
            'name' => 'EXTRU',
            'activity' => 'expense',
            'report_as' => 'TrusteeFee',
            'description' => 'Trustee Fee',
        ],
        [
            'name' => 'EXUD1',
            'activity' => 'expense',
            'report_as' => 'UserDef1',
            'description' => 'User Defined Expense 1',
        ],
        [
            'name' => 'EXUD2',
            'activity' => 'expense',
            'report_as' => 'UserDef2',
            'description' => 'User Defined Expense 2',
        ],
        [
            'name' => 'EXUD3',
            'activity' => 'expense',
            'report_as' => 'UserDef3',
            'description' => 'User Defined Expense 3',
        ],
        [
            'name' => 'EXUD4',
            'activity' => 'expense',
            'report_as' => 'UserDef4',
            'description' => 'User Defined Expense 4',
        ],
        [
            'name' => 'DEL',
            'activity' => 'SecTransfer',
            'report_as' => null,
            'description' => 'Transfer of Securities',
        ],
        [
            'name' => 'REC',
            'activity' => 'SecReceipt',
            'report_as' => null,
            'description' => 'Receipt of Securities',
        ],
        [
            'name' => 'ROP',
            'activity' => 'rop',
            'report_as' => null,
            'description' => 'Return of Principal',
        ],
        [
            'name' => 'SPLIT',
            'activity' => 'split',
            'report_as' => null,
            'description' => 'Split or Share Dividend',
        ],
        [
            'name' => 'INCBA',
            'activity' => 'amort',
            'report_as' => null,
            'description' => 'Increase Cost Basis',
        ],
        [
            'name' => 'DECBA',
            'activity' => 'amort',
            'report_as' => null,
            'description' => 'Decrease Cost Basis',
        ],
        [
            'name' => 'INCSH',
            'activity' => 'CreditSecurity',
            'report_as' => null,
            'description' => 'Increase Share Qty',
        ],
        [
            'name' => 'DECSH',
            'activity' => 'DebitSecurity',
            'report_as' => null,
            'description' => 'Decrease Share Qty',
        ],
        [
            'name' => 'DEP',
            'activity' => 'flow',
            'report_as' => null,
            'description' => 'Deposit of Funds',
        ],
        [
            'name' => 'WITH',
            'activity' => 'flow',
            'report_as' => null,
            'description' => 'Withdrawal of Funds',
        ],
        [
            'name' => 'CJOUR',
            'activity' => 'journal',
            'report_as' => null,
            'description' => 'Credit Journal Entry',
        ],
        [
            'name' => 'DJOUR',
            'activity' => 'journal',
            'report_as' => null,
            'description' => 'Debit Journal Entry',
        ],
        [
            'name' => 'XFER',
            'activity' => 'moneytransfer',
            'report_as' => null,
            'description' => 'Transfer of Funds',
        ],
        [
            'name' => 'OTH',
            'activity' => 'a_omit',
            'report_as' => null,
            'description' => 'Unknown',
        ],
    ];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $item) {
            $transactionType = new TransactionType();

            $transactionType->setName($item['name']);
            $transactionType->setActivity($item['activity']);
            $transactionType->setReportAs($item['report_as']);
            $transactionType->setDescription($item['description']);

            $manager->persist($transactionType);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
