<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.02.13
 * Time: 11:56
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

class PersonalInformation
{
    /**
     * @var string
     */
    protected $income_source;

    // ENUM values income_source column
    const INCOME_SOURCE_ALIMONY = 'Alimony';
    const INCOME_SOURCE_CONSULTING = 'Consulting';
    const INCOME_SOURCE_DISABILITY = 'Disability';
    const INCOME_SOURCE_DIVIDENDS = 'Dividends';
    const INCOME_SOURCE_INHERITANCE = 'Inheritance';
    const INCOME_SOURCE_INTEREST = 'Interest';
    const INCOME_SOURCE_REAL_ESTATE = 'Real Estate';
    const INCOME_SOURCE_RENTAL = 'Rental';
    const INCOME_SOURCE_SALES = 'Sales';
    const INCOME_SOURCE_SECURITIES = 'Securities';
    const INCOME_SOURCE_SETTLEMENT = 'Settlement';
    const INCOME_SOURCE_SEVERANCE = 'Severance';
    const INCOME_SOURCE_SPOUSE = 'Spouse';
    const INCOME_SOURCE_STIPEND = 'Stipend';
    const INCOME_SOURCE_TEACHING = 'Teaching';
    const INCOME_SOURCE_TRADING_AND_INVESTMENTS = 'Trading & Investments';
    const INCOME_SOURCE_UNEMPLOYMENT_BENEFITS = 'Unemployment Benefits';

    private static $_incomeSourceValues = null;
    private $broker_security_exchange_compliance_letter;
    private $compliance_letter_file;

    /**
     * Get array ENUM values income_source column.
     *
     * @static
     *
     * @return array
     */
    public static function getIncomeSourceChoices()
    {
        // Build $_incomeSourceValues if this is the first call
        if (null === self::$_incomeSourceValues) {
            self::$_incomeSourceValues = [];
            $oClass = new \ReflectionClass('\App\Model\PersonalInformation');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'INCOME_SOURCE_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_incomeSourceValues[$val] = $val;
                }
            }
        }

        return self::$_incomeSourceValues;
    }

    /**
     * Set income_source.
     *
     * @param string $incomeSource
     *
     * @return PersonalInformation
     *
     * @throws \InvalidArgumentException
     */
    public function setIncomeSource($incomeSource)
    {
        if (!is_null($incomeSource) && !in_array($incomeSource, self::getIncomeSourceChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for client_personal_information.income_source : %s.', $incomeSource)
            );
        }

        $this->income_source = $incomeSource;

        return $this;
    }

    /**
     * Get income_source.
     *
     * @return string
     */
    public function getIncomeSource()
    {
        return $this->income_source;
    }

    /**
     * Set broker_security_exchange_compliance_letter.
     *
     * @param string $brokerSecurityExchangeComplianceLetter
     *
     * @return PersonalInformation
     */
    public function setBrokerSecurityExchangeComplianceLetter($brokerSecurityExchangeComplianceLetter)
    {
        $this->broker_security_exchange_compliance_letter = $brokerSecurityExchangeComplianceLetter;

        return $this;
    }

    /**
     * Get broker_security_exchange_compliance_letter.
     *
     * @return string
     */
    public function getBrokerSecurityExchangeComplianceLetter()
    {
        return $this->broker_security_exchange_compliance_letter;
    }

    public function setComplianceLetterFile($complianceLetterFile)
    {
        $this->compliance_letter_file = $complianceLetterFile;

        return $this;
    }
}
