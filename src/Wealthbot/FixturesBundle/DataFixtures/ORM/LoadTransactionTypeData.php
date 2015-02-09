<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 22.08.13
 * Time: 13:35
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wealthbot\AdminBundle\Entity\TransactionType;

class LoadTransactionTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    private $data = array(
        array(
            'name' => 'BUY',
            'activity' => 'buy',
            'report_as' => null,
            'description' => 'Buy'
        ),
        array(
            'name' => 'BUYO',
            'activity' => 'buy',
            'report_as' => null,
            'description' => 'Buy/Open (options only)'
        ),
        array(
            'name' => 'BUYC',
            'activity' => 'cover',
            'report_as' => null,
            'description' => 'Buy/Close (options only)'
        ),
        array(
            'name' => 'COVER',
            'activity' => 'cover',
            'report_as' => null,
            'description' => 'Cover (equities only)'
        ),
        array(
            'name' => 'SELL',
            'activity' => 'sell',
            'report_as' => null,
            'description' => 'Sell'
        ),
        array(
            'name' => 'SELLC',
            'activity' => 'sell',
            'report_as' => null,
            'description' => 'Sell/Close (options only)'
        ),
        array(
            'name' => 'SELLO',
            'activity' => 'short',
            'report_as' => null,
            'description' => 'Sell/Open (options only)'
        ),
        array(
            'name' => 'SHORT',
            'activity' => 'short',
            'report_as' => null,
            'description' => 'Short (equities only)'
        ),
        array(
            'name' => 'MAT',
            'activity' => 'sell',
            'report_as' => null,
            'description' => 'Bond Redemption'
        ),
        array(
            'name' => 'DIV',
            'activity' => 'income',
            'report_as' => 'Dividend',
            'description' => 'Dividend'
        ),
        array(
            'name' => 'INT',
            'activity' => 'income',
            'report_as' => 'Interest',
            'description' => 'Interest'
        ),
        array(
            'name' => 'SHTDV',
            'activity' => 'income',
            'report_as' => 'Dividend',
            'description' => 'Short Dividend'
        ),
        array(
            'name' => 'SGAIN',
            'activity' => 'income',
            'report_as' => 'ShortGain',
            'description' => 'Short Term Capital Gain'
        ),

        array(
            'name' => 'LGAIN',
            'activity' => 'income',
            'report_as' => 'LongGain',
            'description' => 'Long Term Capital Gain'
        ),
        array(
            'name' => 'MGAIN',
            'activity' => 'income',
            'report_as' => 'MidGain',
            'description' => 'Mid Term Capital Gain'
        ),
        array(
            'name' => 'UGAIN',
            'activity' => 'income',
            'report_as' => 'UnclassedGain',
            'description' => 'Unclassified Capital Gain'
        ),
        array(
            'name' => 'QDV',
            'activity' => 'income',
            'report_as' => 'QualifiedDividend',
            'description' => 'Qualified Dividend'
        ),
        array(
            'name' => 'NDV',
            'activity' => 'income',
            'report_as' => 'NonQualifiedDividend',
            'description' => 'Non-qualified Dividend'
        ),
        array(
            'name' => 'PIL',
            'activity' => 'income',
            'report_as' => 'PaymentInLieu',
            'description' => 'Payment in Lieu'
        ),
        array(
            'name' => '5YEAR',
            'activity' => 'income',
            'report_as' => '5YearGain',
            'description' => '5 Year Gain'
        ),
        array(
            'name' => 'TCGC',
            'activity' => 'income',
            'report_as' => 'Pre5/6LongGain',
            'description' => 'Pre 5/6 Long Gain'
        ),
        array(
            'name' => 'CLIEU',
            'activity' => null,
            'report_as' => null,
            'description' => 'Cash in Lieu Transaction'
        ),
        array(
            'name' => 'MEXP',
            'activity' => 'expense',
            'report_as' => 'MarginExpense',
            'description' => 'Margin Expense'
        ),
        array(
            'name' => 'MFEE',
            'activity' => 'expense',
            'report_as' => 'ManagementFee',
            'description' => 'Management Fee'
        ),
        array(
            'name' => 'EXP',
            'activity' => 'expense',
            'report_as' => 'ExpenseFee',
            'description' => 'Other Expenses'
        ),
        array(
            'name' => 'TEFRA',
            'activity' => 'expense',
            'report_as' => 'FedWithhold',
            'description' => 'Fed Withholding'
        ),
        array(
            'name' => 'NRTAX',
            'activity' => 'expense',
            'report_as' => 'NonResTax',
            'description' => 'Non Resident Tax'
        ),
        array(
            'name' => 'FRTAX',
            'activity' => 'expense',
            'report_as' => 'ForeignTaxPd',
            'description' => 'Foreign Tax Paid'
        ),
        array(
            'name' => 'EXABP',
            'activity' => 'expense',
            'report_as' => 'ABPFee',
            'description' => 'ABP Fee'
        ),
        array(
            'name' => 'EXACC',
            'activity' => 'expense',
            'report_as' => 'AccountingFee',
            'description' => 'Accounting Fee'
        ),
        array(
            'name' => 'EXACT',
            'activity' => 'expense',
            'report_as' => 'ActuarialFee',
            'description' => 'Actuarial Fee'
        ),
        array(
            'name' => 'EXAPP',
            'activity' => 'expense',
            'report_as' => 'AppraisalFee',
            'description' => 'Appraisal Fee'
        ),
        array(
            'name' => 'EXCON',
            'activity' => 'expense',
            'report_as' => 'ContractFee',
            'description' => 'Contract Fee'
        ),
        array(
            'name' => 'EXLEG',
            'activity' => 'expense',
            'report_as' => 'LegalFee',
            'description' => 'Legal Fee'
        ),
        array(
            'name' => 'EXSAL',
            'activity' => 'expense',
            'report_as' => 'SalariesAllowance',
            'description' => 'Salaries Allowance'
        ),

        array(
            'name' => 'EXTRU',
            'activity' => 'expense',
            'report_as' => 'TrusteeFee',
            'description' => 'Trustee Fee'
        ),
        array(
            'name' => 'EXUD1',
            'activity' => 'expense',
            'report_as' => 'UserDef1',
            'description' => 'User Defined Expense 1'
        ),
        array(
            'name' => 'EXUD2',
            'activity' => 'expense',
            'report_as' => 'UserDef2',
            'description' => 'User Defined Expense 2'
        ),
        array(
            'name' => 'EXUD3',
            'activity' => 'expense',
            'report_as' => 'UserDef3',
            'description' => 'User Defined Expense 3'
        ),
        array(
            'name' => 'EXUD4',
            'activity' => 'expense',
            'report_as' => 'UserDef4',
            'description' => 'User Defined Expense 4'
        ),
        array(
            'name' => 'DEL',
            'activity' => 'SecTransfer',
            'report_as' => null,
            'description' => 'Transfer of Securities'
        ),
        array(
            'name' => 'REC',
            'activity' => 'SecReceipt',
            'report_as' => null,
            'description' => 'Receipt of Securities'
        ),
        array(
            'name' => 'ROP',
            'activity' => 'rop',
            'report_as' => null,
            'description' => 'Return of Principal'
        ),
        array(
            'name' => 'SPLIT',
            'activity' => 'split',
            'report_as' => null,
            'description' => 'Split or Share Dividend'
        ),
        array(
            'name' => 'INCBA',
            'activity' => 'amort',
            'report_as' => null,
            'description' => 'Increase Cost Basis'
        ),
        array(
            'name' => 'DECBA',
            'activity' => 'amort',
            'report_as' => null,
            'description' => 'Decrease Cost Basis'
        ),
        array(
            'name' => 'INCSH',
            'activity' => 'CreditSecurity',
            'report_as' => null,
            'description' => 'Increase Share Qty'
        ),
        array(
            'name' => 'DECSH',
            'activity' => 'DebitSecurity',
            'report_as' => null,
            'description' => 'Decrease Share Qty'
        ),
        array(
            'name' => 'DEP',
            'activity' => 'flow',
            'report_as' => null,
            'description' => 'Deposit of Funds'
        ),
        array(
            'name' => 'WITH',
            'activity' => 'flow',
            'report_as' => null,
            'description' => 'Withdrawal of Funds'
        ),
        array(
            'name' => 'CJOUR',
            'activity' => 'journal',
            'report_as' => null,
            'description' => 'Credit Journal Entry'
        ),
        array(
            'name' => 'DJOUR',
            'activity' => 'journal',
            'report_as' => null,
            'description' => 'Debit Journal Entry'
        ),
        array(
            'name' => 'XFER',
            'activity' => 'moneytransfer',
            'report_as' => null,
            'description' => 'Transfer of Funds'
        ),
        array(
            'name' => 'OTH',
            'activity' => 'a_omit',
            'report_as' => null,
            'description' => 'Unknown'
        ),
    );

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
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
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 1;
    }

}