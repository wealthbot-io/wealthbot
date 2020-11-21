<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\AssetClass;
use App\Entity\CeModelEntity;
use App\Entity\Custodian;
use App\Entity\Security;
use App\Entity\SecurityAssignment;
use App\Entity\SecurityType;
use App\Entity\Subclass;
use App\Manager\CeModelManager;
use App\Entity\AccountContribution;
use App\Entity\AccountOutsideFund;
use App\Entity\Beneficiary;
use App\Entity\ClientAccount;
use App\Entity\ClientAccountOwner;
use App\Entity\ClientAdditionalContact;
use App\Entity\ClientQuestionnaireAnswer;
use App\Entity\ClientSettings;
use App\Entity\PersonalInformation;
use App\Entity\SystemAccount;
use App\Entity\Workflow;
use App\Entity\RiaCompanyInformation;
use App\Entity\RiskAnswer;
use App\Entity\RiskQuestion;
use App\Entity\Document;
use App\Entity\Group;
use App\Entity\Profile;
use App\Entity\User;

class LoadCecRiaData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    private $riskProfiling = [
        1 => [
            'question_index' => 1,
            'sequence' => 2,
            'answers' => [
                1 => ['answer_index' => 1, 'point' => -5],
                ['answer_index' => 2, 'point' => 0],
                ['answer_index' => 3, 'point' => 2],
                ['answer_index' => 4, 'point' => 3],
            ],
        ],
        [
            'question_index' => 2,
            'sequence' => 1,
            'answers' => [
                1 => ['answer_index' => 1, 'point' => 6],
                ['answer_index' => 2, 'point' => -3],
                ['answer_index' => 3, 'point' => -5],
            ],
        ],
        [
            'question_index' => 3,
            'sequence' => 0,
            'answers' => [
                1 => ['answer_index' => 1, 'point' => -3],
                ['answer_index' => 2, 'point' => 3],
            ],
        ],
        [
            'question_index' => 4,
            'sequence' => 3,
            'answers' => [
                1 => ['answer_index' => 1, 'point' => -5],
                ['answer_index' => 2, 'point' => 5],
            ],
        ],
    ];

    private $strategy = [
        'commission_min' => 10,
        'commission_max' => 10,
        'forecast_min' => 30,
        'generous_market_return' => 1.2,
        'low_market_return' => 0.8,
        'is_assumption_locked' => 1,
        'models' => [
            [
                'name' => 'Webo 100% Stocks',
                'index' => 'webo_100_stocks',
                'risk_rating' => 4,
                'is_assumption_locked' => 0,
                'entities' => [
                    ['asset_class_index' => 0, 'subclass_index' => 0, 'security' => 'VTI', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => 'IVV', 'percent' => 30],     // id: 46
                    ['asset_class_index' => 0, 'subclass_index' => 1, 'security' => 'VTV', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 10],  // id: 47
                    ['asset_class_index' => 0, 'subclass_index' => 2, 'security' => 'IJR', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 15],  // id: 48
                    ['asset_class_index' => 2, 'subclass_index' => 1, 'security' => 'VNQ', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 5],  // id: 49
                    ['asset_class_index' => 1, 'subclass_index' => 0, 'security' => 'VEA', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 20],  // id: 50
                    ['asset_class_index' => 1, 'subclass_index' => 4, 'security' => 'VWO', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 10], // id: 51
                    ['asset_class_index' => 1, 'subclass_index' => 2, 'security' => 'VSS', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 10],  // id: 52
                ],
            ],
            [
                'name' => 'Webo 30% Stocks',
                'index' => 'webo_30_stocks',
                'risk_rating' => 1,
                'is_assumption_locked' => 1,
                'entities' => [
                    ['asset_class_index' => 0, 'subclass_index' => 0, 'security' => 'VTI', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => 'IVV', 'percent' => 20],     // id: 53
                    ['asset_class_index' => 3, 'subclass_index' => 0, 'security' => 'BND', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => 'VCIT', 'percent' => 40],   // id: 54
                    ['asset_class_index' => 3, 'subclass_index' => 1, 'security' => 'BSV', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 30], // id: 55
                    ['asset_class_index' => 1, 'subclass_index' => 0, 'security' => 'VEA', 'muni_substitution_security' => 'SHM', 'tax_loss_harvesting_security' => null, 'percent' => 10],    // id: 56
                ],
            ],
            [
                'name' => 'Webo 80/20',
                'index' => 'webo_80_20',
                'risk_rating' => 3,
                'is_assumption_locked' => 0,
                'entities' => [
                    ['asset_class_index' => 3, 'subclass_index' => 0, 'security' => 'BND', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 20],  // id: 156
                    ['asset_class_index' => 2, 'subclass_index' => 0, 'security' => 'DBC', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 4],   // id: 157
                    ['asset_class_index' => 1, 'subclass_index' => 4, 'security' => 'VWO', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 5.6], // id: 158
                    ['asset_class_index' => 1, 'subclass_index' => 0, 'security' => 'VEA', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 6],    // id: 159
                    ['asset_class_index' => 1, 'subclass_index' => 1, 'security' => 'EFV', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 7.2],  // id: 160
                    ['asset_class_index' => 2, 'subclass_index' => 2, 'security' => 'RWX', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 6],   // id: 161
                    ['asset_class_index' => 1, 'subclass_index' => 2, 'security' => 'VSS', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 6],    // id: 162
                    ['asset_class_index' => 1, 'subclass_index' => 3, 'security' => 'SCZ', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 7.2],  // id: 163
                    ['asset_class_index' => 0, 'subclass_index' => 0, 'security' => 'IVV', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 6.4],  // id: 164
                    ['asset_class_index' => 0, 'subclass_index' => 1, 'security' => 'VTV', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 9.6],  // id: 165
                    ['asset_class_index' => 2, 'subclass_index' => 1, 'security' => 'VNQ', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 6],   // id: 166
                    ['asset_class_index' => 0, 'subclass_index' => 2, 'security' => 'IJR', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 6.4],  // id: 167
                    ['asset_class_index' => 0, 'subclass_index' => 3, 'security' => 'IJS', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 9.6],  // id: 167
                ],
            ],
[
                'name' => 'Rick Ferri Two Fund Portfolio',
                'index' => 'rf_two_fund_portfolio',
                'risk_rating' => 3,
                'is_assumption_locked' => 0,
                'entities' => [
                    ['asset_class_index' => 3, 'subclass_index' => 0, 'security' => 'BND', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 40],
                    ['asset_class_index' => 0, 'subclass_index' => 0, 'security' => 'VTI', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 60],
                ],
            ],
            [
                'name' => 'Webo 60/40',
                'index' => 'webo_60_40',
                'risk_rating' => 2,
                'is_assumption_locked' => 0,
                'entities' => [
                    ['asset_class_index' => 3, 'subclass_index' => 0, 'security' => 'BND', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 40],  // id: 169
                    ['asset_class_index' => 2, 'subclass_index' => 0, 'security' => 'DBC', 'muni_substitution_security' => 'VTI', 'tax_loss_harvesting_security' => null, 'percent' => 3],   // id: 170
                    ['asset_class_index' => 1, 'subclass_index' => 4, 'security' => 'VWO', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 4.2], // id: 171
                    ['asset_class_index' => 1, 'subclass_index' => 0, 'security' => 'VEA', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 4.5],  // id: 172
                    ['asset_class_index' => 1, 'subclass_index' => 1, 'security' => 'EFV', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 5.4],  // id: 173
                    ['asset_class_index' => 2, 'subclass_index' => 2, 'security' => 'RWX', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 4.5], // id: 174
                    ['asset_class_index' => 1, 'subclass_index' => 2, 'security' => 'VSS', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 4.5],  // id: 175
                    ['asset_class_index' => 1, 'subclass_index' => 3, 'security' => 'SCZ', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 5.4],  // id: 176
                    ['asset_class_index' => 0, 'subclass_index' => 0, 'security' => 'IVV', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 4.8],  // id: 177
                    ['asset_class_index' => 0, 'subclass_index' => 1, 'security' => 'VTV', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 7.2],  // id: 178
                    ['asset_class_index' => 2, 'subclass_index' => 1, 'security' => 'VNQ', 'muni_substitution_security' => null, 'tax_loss_harvesting_security' => null, 'percent' => 4.5], // id: 179
                    ['asset_class_index' => 0, 'subclass_index' => 2, 'security' => 'IJR', 'muni_substitution_security' => 'DBC', 'tax_loss_harvesting_security' => null, 'percent' => 4.8],  // id: 180
                    ['asset_class_index' => 0, 'subclass_index' => 3, 'security' => 'IJS', 'muni_substitution_security' => 'BND', 'tax_loss_harvesting_security' => null, 'percent' => 7.2],  // id: 181
                ],
            ],
        ],
    ];

    private $categories = [
        [
            'name' => 'Domestic Stocks',
            'type' => AssetClass::TYPE_STOCKS,
            'subclasses' => [
                ['name' => 'Large', 'expected_performance' => 8, 'account_type_index' => 3, 'priority' => 1, 'tolerance_band' => 10],
                ['name' => 'Large Value', 'expected_performance' => 10, 'account_type_index' => 2, 'priority' => 1, 'tolerance_band' => 11],
                ['name' => 'Small', 'expected_performance' => 10, 'account_type_index' => 3, 'priority' => 2, 'tolerance_band' => 3],
                ['name' => 'Small Value', 'expected_performance' => 10, 'account_type_index' => 1, 'priority' => 1, 'tolerance_band' => 2],
            ],
        ],
        [
            'name' => 'International Stocks',
            'type' => AssetClass::TYPE_STOCKS,
            'subclasses' => [
                ['name' => 'Large', 'expected_performance' => 10, 'account_type_index' => 2, 'priority' => 2, 'tolerance_band' => 11],
                ['name' => 'Large Value', 'expected_performance' => 10, 'account_type_index' => 2, 'priority' => 3, 'tolerance_band' => 21],
                ['name' => 'Small', 'expected_performance' => 10, 'account_type_index' => 1, 'priority' => 2, 'tolerance_band' => 11],
                ['name' => 'Small Value', 'expected_performance' => 10, 'account_type_index' => 1, 'priority' => 3, 'tolerance_band' => 3],
                ['name' => 'Emerging Markets', 'expected_performance' => 10, 'account_type_index' => 1, 'priority' => 4, 'tolerance_band' => 5],
                ['name' => 'REITS', 'expected_performance' => 10, 'account_type_index' => 1, 'priority' => 5, 'tolerance_band' => 7],
            ],
        ],
        [
            'name' => 'Alternatives',
            'type' => AssetClass::TYPE_STOCKS,
            'subclasses' => [
                ['name' => 'Commodities', 'expected_performance' => 5, 'account_type_index' => 2, 'priority' => 4, 'tolerance_band' => 3],
                ['name' => 'REITS', 'expected_performance' => 8, 'account_type_index' => 2, 'priority' => 5, 'tolerance_band' => 11],
                ['name' => 'International REITS', 'expected_performance' => 10, 'account_type_index' => 1, 'priority' => 6, 'tolerance_band' => 10],
            ],
        ],
        [
            'name' => 'Bonds',
            'type' => AssetClass::TYPE_STOCKS,
            'subclasses' => [
                ['name' => 'Intermediate', 'expected_performance' => 4, 'account_type_index' => 2, 'priority' => 6, 'tolerance_band' => 7],
                ['name' => 'Short', 'expected_performance' => 3, 'account_type_index' => 2, 'priority' => 7, 'tolerance_band' => 6],
                ['name' => 'Long', 'expected_performance' => 4, 'account_type_index' => 2, 'priority' => 8, 'tolerance_band' => 10],
            ],
        ],
    ];

    /**
     * @var array Data format: array(index_of_client => array_of_client_data)
     */
    private $clients = [
        1 => [ // id: 51
            'username' => 'johnny@wealthbot.io',
            'password' => 'ab12cd34EF56gh78',
            'first_name' => 'Johnny',
            'last_name' => 'Cage',
            'middle_name' => 'X',
            'state' => 'New York',
            'street' => '555 Broadway',
            'city' => 'New York',
            'zip' => '12345',
            'birth_date' => '1972-07-25',
            'phone_number' => '1234567890',
            'marital_status' => Profile::CLIENT_MARITAL_STATUS_SINGLE,
            'annual_income' => Profile::CLIENT_ANNUAL_INCOME_VALUE4,
            'estimated_income_tax' => '0.1',
            'liquid_net_worth' => Profile::CLIENT_LIQUID_NET_WORTH_VALUE4,
            'employment_type' => Profile::CLIENT_EMPLOYMENT_TYPE_RETIRED,
            'client_account_managed' => 2,
            'registration_step' => 3,
            'suggested_portfolio_index' => 'webo_80_20',
            'client_status' => Profile::CLIENT_STATUS_PROSPECT,
            'created' => '2013-01-20T09:01:12-04:00',
            'paymentMethod' => Profile::PAYMENT_METHOD_DIRECT_DEBIT,
            'stop_tlh_value' => 5.6,
        ],
        2 => [ // id: 52
            'username' => 'liu@wealthbot.io',
            'password' => 'ab12cd34EF56gh78',
            'first_name' => 'Liu',
            'last_name' => 'Kang',
            'middle_name' => 'A',
            'state' => 'New York',
            'street' => '666 Broadway',
            'city' => 'New York',
            'zip' => '12345',
            'birth_date' => '1955-12-24',
            'phone_number' => '1234567890',
            'marital_status' => Profile::CLIENT_MARITAL_STATUS_MARRIED,
            'annual_income' => Profile::CLIENT_ANNUAL_INCOME_VALUE2,
            'estimated_income_tax' => '0.1',
            'liquid_net_worth' => Profile::CLIENT_LIQUID_NET_WORTH_VALUE4,
            'employment_type' => Profile::CLIENT_EMPLOYMENT_TYPE_RETIRED,
            'client_account_managed' => 1,
            'registration_step' => 7,
            'suggested_portfolio_index' => 'webo_60_40',
            'client_status' => Profile::CLIENT_STATUS_CLIENT,
            'created' => '2013-03-12T09:30:22-04:00',
            'paymentMethod' => Profile::PAYMENT_METHOD_DIRECT_DEBIT,
            'stop_tlh_value' => 2.3,
        ],
        3 => [ // id: 53
            'username' => 'sonya@wealthbot.io',
            'password' => 'ab12cd34EF56gh78',
            'first_name' => 'Sonya',
            'last_name' => 'Blade',
            'middle_name' => 'V',
            'state' => 'New York',
            'street' => '777 Broadway',
            'city' => 'New York',
            'zip' => '12345',
            'birth_date' => '1957-02-28',
            'phone_number' => '1234567890',
            'marital_status' => Profile::CLIENT_MARITAL_STATUS_SINGLE,
            'annual_income' => Profile::CLIENT_ANNUAL_INCOME_VALUE3,
            'estimated_income_tax' => '0.1',
            'liquid_net_worth' => Profile::CLIENT_LIQUID_NET_WORTH_VALUE4,
            'employment_type' => Profile::CLIENT_EMPLOYMENT_TYPE_RETIRED,
            'client_account_managed' => 2,
            'registration_step' => 7,
            'suggested_portfolio_index' => 'webo_60_40',
            'created' => '2013-05-19T12:21:02-04:00',
            'paymentMethod' => Profile::PAYMENT_METHOD_OUTSIDE_PAYMENT,
            'stop_tlh_value' => null,
        ],
        4 => [ // id: 29
            'username' => 'shang@wealthbot.io',
            'password' => 'ab12cd34EF56gh78',
            'first_name' => 'Shang',
            'last_name' => 'Tsung',
            'middle_name' => 'C',
            'state' => 'New York',
            'street' => '888 Broadway',
            'city' => 'New York',
            'zip' => '12345',
            'birth_date' => '1950-01-22',
            'phone_number' => '4232323232',
            'marital_status' => Profile::CLIENT_MARITAL_STATUS_MARRIED,
            'annual_income' => Profile::CLIENT_ANNUAL_INCOME_VALUE4,
            'estimated_income_tax' => '0.3',
            'liquid_net_worth' => Profile::CLIENT_LIQUID_NET_WORTH_VALUE5,
            'employment_type' => Profile::CLIENT_EMPLOYMENT_TYPE_EMPLOYED,
            'client_account_managed' => null,
            'registration_step' => 3,
            'suggested_portfolio_index' => 'webo_100_stocks',
            'created' => '2013-07-03T11:21:44-04:00',
            'paymentMethod' => Profile::PAYMENT_METHOD_OUTSIDE_PAYMENT,
            'stop_tlh_value' => null,
        ],
        5 => [ // id: 30
            'username' => 'subzero@wealthbot.io',
            'password' => 'ab12cd34EF56gh78',
            'first_name' => 'Sub',
            'last_name' => 'Zero',
            'middle_name' => 'O',
            'state' => 'New York',
            'street' => '999 Broadway',
            'city' => 'New York',
            'zip' => '12345',
            'birth_date' => '1950-01-22',
            'phone_number' => '4232323232',
            'marital_status' => Profile::CLIENT_MARITAL_STATUS_MARRIED,
            'annual_income' => Profile::CLIENT_ANNUAL_INCOME_VALUE4,
            'estimated_income_tax' => '0.3',
            'liquid_net_worth' => Profile::CLIENT_LIQUID_NET_WORTH_VALUE5,
            'employment_type' => Profile::CLIENT_EMPLOYMENT_TYPE_EMPLOYED,
            'client_account_managed' => 2,
            'registration_step' => 6,
            'suggested_portfolio_index' => 'webo_100_stocks',
            'created' => '2013-10-20T02:00:10-04:00',
            'approved_at' => '2013-10-26T02:34:43-04:00',
            'stop_tlh_value' => null,
        ],
    ];

    /**
     * @var array Data format: array(index_of_client => array_of_data)
     */
    private $clientsPersonalInformation = [
        1 => [
            'ssn_tin' => '523130336',
            'income_source' => 'Interest',
            'employer_name' => null,
            'industry' => null,
            'occupation' => null,
            'business_type' => null,
            'employer_address' => null,
            'city' => null,
            'zipcode' => null,
        ],
        2 => [
            'ssn_tin' => '262210206',
            'income_source' => 'Interest',
            'employer_name' => null,
            'industry' => null,
            'occupation' => null,
            'business_type' => null,
            'employer_address' => null,
            'city' => null,
            'zipcode' => null,
        ],
        3 => [
            'ssn_tin' => '528960595',
            'income_source' => 'Interest',
            'employer_name' => null,
            'industry' => null,
            'occupation' => null,
            'business_type' => null,
            'employer_address' => null,
            'city' => null,
            'zipcode' => null,
        ],
        5 => [
            'ssn_tin' => '123331234',
            'income_source' => null,
            'employer_name' => 'New York Mortal Kombat',
            'industry' => 'Sports',
            'occupation' => 'Fighter',
            'business_type' => 'MMA',
            'employer_address' => 'Eearthrealm',
            'city' => 'New York',
            'zipcode' => '12345',
        ],
    ];

    /**
     * @var array Data format: array(index_of_client => array_of_data)
     */
    private $clientsAdditionalContacts = [
        2 => [
            'state' => 'New York',
            'first_name' => 'Princess',
            'last_name' => 'Kitana',
            'middle_name' => 'B',
            'street' => '555 Broadway',
            'city' => 'New York',
            'zip' => '12345',
            'is_different_address' => false,
            'birth_date' => '1800-10-27',
            'phone_number' => '1234567890',
            'ssn_tin' => '265158279',
            'income_source' => 'Interest',
            'is_senior_political_figure' => false,
            'is_publicly_traded_company' => false,
            'is_broker_security_exchange_person' => false,
            'email' => 'kitana@wealthbot.io',
            'type' => 'spouse',
            'employment_type' => 'Retired',
        ],
        4 => [
            'state' => null,
            'first_name' => 'Li',
            'last_name' => 'Mei',
            'middle_name' => 'R',
            'street' => null,
            'city' => null,
            'zip' => null,
            'is_different_address' => null,
            'birth_date' => '1984-01-21',
            'phone_number' => null,
            'ssn_tin' => null,
            'income_source' => null,
            'is_senior_political_figure' => null,
            'is_publicly_traded_company' => null,
            'is_broker_security_exchange_person' => null,
            'email' => null,
            'type' => 'spouse',
            'employment_type' => null,
        ],
        5 => [
            'state' => null,
            'first_name' => 'Noob',
            'last_name' => 'Saibot',
            'middle_name' => 'R',
            'street' => null,
            'city' => null,
            'zip' => null,
            'is_different_address' => null,
            'birth_date' => '1985-04-22',
            'phone_number' => null,
            'ssn_tin' => null,
            'income_source' => null,
            'is_senior_political_figure' => null,
            'is_publicly_traded_company' => null,
            'is_broker_security_exchange_person' => null,
            'email' => null,
            'type' => 'spouse',
            'employment_type' => null,
        ],
    ];

    /**
     * @var array Data format: array(index_of_client => array(index_of_account => array_of_account_data))
     */
    private $clientAccounts = [
        1 => [
            1 => [
                'group_type_key' => 'deposit_money-10',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 50000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => ClientAccount::STEP_ACTION_REVIEW,
                'is_pre_saved' => false,
                'system_type' => 1,
                'unconsolidated' => false,
                'owners' => ['self'],
                'account_contribution' => [
                    'type' => AccountContribution::TYPE_FUNDING_WIRE,
                    'transaction_frequency' => 1,
                ],
            ],
        ],
        2 => [
            1 => [
                'group_type_key' => 'deposit_money-10',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 20000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => ClientAccount::STEP_ACTION_REVIEW,
                'is_pre_saved' => false,
                'system_type' => 1,
                'unconsolidated' => false,
                'owners' => ['spouse'],
                'account_contribution' => [
                    'type' => AccountContribution::TYPE_FUNDING_WIRE,
                    'transaction_frequency' => 1,
                ],
            ],
            [
                'group_type_key' => 'deposit_money-11',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 10000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => ClientAccount::STEP_ACTION_REVIEW,
                'is_pre_saved' => false,
                'system_type' => 2,
                'unconsolidated' => false,
                'owners' => ['self', 'spouse'],
                'account_contribution' => [
                    'type' => AccountContribution::TYPE_FUNDING_WIRE,
                    'transaction_frequency' => 1,
                ],
            ],
            [
                'group_type_key' => 'deposit_money-14',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 10000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => ClientAccount::STEP_ACTION_REVIEW,
                'is_pre_saved' => false,
                'system_type' => 3,
                'unconsolidated' => false,
                'owners' => ['self'],
                'account_contribution' => [
                    'type' => AccountContribution::TYPE_FUNDING_WIRE,
                    'transaction_frequency' => 1,
                ],
            ],
            [
                'group_type_key' => 'deposit_money-14',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 10000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => ClientAccount::STEP_ACTION_REVIEW,
                'is_pre_saved' => false,
                'system_type' => 3,
                'unconsolidated' => false,
                'owners' => ['spouse'],
                'account_contribution' => [
                    'type' => AccountContribution::TYPE_FUNDING_WIRE,
                    'transaction_frequency' => 1,
                ],
            ],
        ],
        3 => [
            1 => [
                'group_type_key' => 'deposit_money-10',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 50000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => ClientAccount::STEP_ACTION_REVIEW,
                'is_pre_saved' => false,
                'system_type' => 1,
                'unconsolidated' => false,
                'owners' => ['self'],
                'account_contribution' => [
                    'type' => AccountContribution::TYPE_FUNDING_WIRE,
                    'transaction_frequency' => 1,
                ],
            ],
            [
                'group_type_key' => 'deposit_money-19',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 10000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => ClientAccount::STEP_ACTION_REVIEW,
                'is_pre_saved' => false,
                'system_type' => 4,
                'unconsolidated' => false,
                'owners' => ['self'],
                'account_contribution' => [
                    'type' => AccountContribution::TYPE_FUNDING_WIRE,
                    'transaction_frequency' => 1,
                ],
            ],
        ],
        4 => [
            1 => [
                'group_type_key' => 'deposit_money-10',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 30000,
                'monthly_contributions' => 200,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 0,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 1,
                'unconsolidated' => false,
                'owners' => ['self'],
            ],
            [
                'group_type_key' => 'deposit_money-11',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 50000,
                'monthly_contributions' => 100,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 0,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 2,
                'unconsolidated' => false,
                'owners' => ['self', 'spouse'],
            ],
            [
                'group_type_key' => 'financial_institution-19',
                'consolidator_index' => null,
                'financial_institution' => 'Fidelity',
                'value' => 20000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 0,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 4,
                'unconsolidated' => false,
                'owners' => ['self'],
            ],
            [
                'group_type_key' => 'old_employer_retirement-2',
                'consolidator_index' => 3,
                'financial_institution' => 'Mortal Kombat Ltd',
                'value' => 5000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 0,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 4,
                'unconsolidated' => false,
                'owners' => ['self'],
            ],
        ],
        5 => [
            1 => [
                'group_type_key' => 'deposit_money-10',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 50000,
                'monthly_contributions' => 100,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 0,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 1,
                'unconsolidated' => false,
                'owners' => ['self'],
            ],
            [
                'group_type_key' => 'deposit_money-11',
                'consolidator_index' => null,
                'financial_institution' => null,
                'value' => 20000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 0,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 2,
                'unconsolidated' => false,
                'owners' => ['self', 'spouse'],
            ],
            [
                'group_type_key' => 'financial_institution-20',
                'consolidator_index' => null,
                'financial_institution' => 'Schwab',
                'value' => 5000,
                'monthly_contributions' => null,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => ClientAccount::STEP_ACTION_REVIEW,
                'is_pre_saved' => false,
                'system_type' => 4,
                'unconsolidated' => false,
                'owners' => ['self'],
                'account_contribution' => [
                    'type' => AccountContribution::TYPE_FUNDING_MAIL,
                    'transaction_frequency' => 1,
                ],
            ],
            [
                'group_type_key' => 'old_employer_retirement-2',
                'consolidator_index' => 3,
                'financial_institution' => 'Tradier',
                'value' => 4000,
                'monthly_contributions' => 200,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 2,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 4,
                'unconsolidated' => false,
                'owners' => ['self'],
            ],
            [
                'group_type_key' => 'financial_institution-14',
                'consolidator_index' => null,
                'financial_institution' => 'Fidelity',
                'value' => 40000,
                'monthly_contributions' => 200,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 0,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 3,
                'unconsolidated' => false,
                'owners' => ['spouse'],
            ],
            [
                'group_type_key' => 'employer_retirement-5',
                'consolidator_index' => null,
                'financial_institution' => 'Vanguard',
                'value' => 50000,
                'monthly_contributions' => 100,
                'monthly_distributions' => null,
                'sas_cash' => null,
                'process_step' => 0,
                'step_action' => null,
                'is_pre_saved' => false,
                'system_type' => 5,
                'unconsolidated' => false,
                'owners' => ['self'],
                'securities' => [
                    ['name' => 'Vanguard Total Stock Market Fund', 'symbol' => 'VTSMX', 'type' => 'EQ', 'exp_ratio' => 0.25],
                    ['name' => 'Vanguard Bond Fund', 'symbol' => 'VBFX', 'type' => 'EQ', 'exp_ratio' => 0.52],
                ],
            ],
        ],
    ];

    /**
     * @var array Data format: array(index_of_client => array(index_of_account => array_of_data))
     */
    private $clientBeneficiaries = [
        2 => [
            3 => [
                'type' => Beneficiary::TYPE_PRIMARY,
                'state' => 'New York',
                'first_name' => 'Princess',
                'last_name' => 'Kitana',
                'middle_name' => 'B',
                'ssn' => '265158279',
                'birth_date' => '1800-10-27',
                'street' => '555 Broadway',
                'city' => 'New York',
                'zip' => '12345',
                'relationship' => 'Spouse',
                'share' => 100,
            ],
            4 => [
                'type' => Beneficiary::TYPE_PRIMARY,
                'state' => 'New York',
                'first_name' => 'Princess',
                'last_name' => 'Kitana',
                'middle_name' => 'B',
                'ssn' => '265158279',
                'birth_date' => '1800-10-27',
                'street' => '9551 Southwest 63rd Court',
                'city' => 'New York',
                'zip' => '12345',
                'relationship' => 'Spouse',
                'share' => 100,
            ],
        ],
        5 => [
            3 => [
                'type' => Beneficiary::TYPE_PRIMARY,
                'state' => 'New York',
                'first_name' => 'Noob',
                'last_name' => 'Saibot',
                'middle_name' => 'R',
                'ssn' => '444444444',
                'birth_date' => '1985-04-22',
                'street' => '123 Don Shula Way',
                'city' => 'New York',
                'zip' => '33133',
                'relationship' => 'Spouse',
                'share' => 100,
            ],
        ],
    ];

    /**
     * @var array Data format: array(index_of_client => array(index_of_account => array_of_data))
     */
    private $systemClientAccounts = [
        1 => [
            1 => [
                'account_number' => '409888117',
                'account_description' => 'Sonya Personal Account',
                'type' => 1,
                'status' => SystemAccount::STATUS_ACTIVE,
            ],
        ],
        2 => [
            1 => [
                'account_number' => '744888385',
                'account_description' => 'Princess Personal Account',
                'type' => 1,
                'status' => SystemAccount::STATUS_ACTIVE,
            ],
            2 => [
                'account_number' => '744888386',
                'account_description' => 'Liu & Princess Joint Account',
                'type' => 2,
                'status' => SystemAccount::STATUS_ACTIVE,
            ],
            3 => [
                'account_number' => '214888609',
                'account_description' => 'Liu Roth IRA',
                'type' => 3,
                'status' => SystemAccount::STATUS_ACTIVE,
            ],
            4 => [
                'account_number' => '480888811',
                'account_description' => 'Princess Roth IRA',
                'type' => 3,
                'status' => SystemAccount::STATUS_ACTIVE,
            ],
        ],
        3 => [
            1 => [
                'account_number' => '906888992',
                'account_description' => 'Leah Personal Account',
                'type' => 1,
                'status' => SystemAccount::STATUS_ACTIVE,
            ],
            2 => [
                'account_number' => '489888498',
                'account_description' => 'Leah Traditional IRA',
                'type' => 4,
                'status' => SystemAccount::STATUS_ACTIVE,
            ],
        ],
        5 => [
            3 => [
                'account_number' => '338484924',
                'account_description' => 'Saibot Rollover IRA',
                'type' => 4,
                'status' => SystemAccount::STATUS_ACTIVE,
            ],
            4 => [
                'account_number' => '122223334',
                'account_description' => 'Test Transfer ACC',
                'type' => 4,
                'status' => SystemAccount::STATUS_WAITING_ACTIVATION,
                'creationType' => 2,
            ],
        ],
    ];

    private $clientQuestionnaire = [
        1 => [
            ['q_index' => 1, 'a_index' => 1],
            ['q_index' => 2, 'a_index' => 1],
            ['q_index' => 3, 'a_index' => 1],
            ['q_index' => 4, 'a_index' => 1],
        ],
        2 => [
            ['q_index' => 1, 'a_index' => 2],
            ['q_index' => 2, 'a_index' => 2],
            ['q_index' => 3, 'a_index' => 1],
            ['q_index' => 4, 'a_index' => 1],
        ],
        3 => [
            ['q_index' => 1, 'a_index' => 2],
            ['q_index' => 2, 'a_index' => 2],
            ['q_index' => 3, 'a_index' => 1],
            ['q_index' => 4, 'a_index' => 1],
        ],
        4 => [
            ['q_index' => 1, 'a_index' => 4],
            ['q_index' => 2, 'a_index' => 1],
            ['q_index' => 3, 'a_index' => 2],
            ['q_index' => 4, 'a_index' => 1],
        ],
        5 => [
            ['q_index' => 1, 'a_index' => 1],
            ['q_index' => 2, 'a_index' => 3],
            ['q_index' => 3, 'a_index' => 1],
            ['q_index' => 4, 'a_index' => 1],
        ],
    ];

    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $riaUser = $this->createUser();
        $manager->persist($riaUser);
        $this->addReference('user-wealthbot-io-ria', $riaUser);

        $manager->flush();

        $riaUser->setCreated(new \DateTime('2013-01-05T11:10:12-04:00'));
        $manager->flush();

        $riaCompanyInformation = $this->createRiaCompanyInformation($riaUser);
        $model = $this->createModel($manager, $riaUser);
        $riaCompanyInformation->setPortfolioModel($model);

        $manager->persist($riaCompanyInformation);
        $manager->persist($model);

        $manager->flush();

        $this->saveRiskQuestions($manager, $riaUser);
        $this->saveClientsData($manager, $riaUser);

        $manager->flush();

        $this->addDocumentForOwner($riaUser, $this->getReference('adv-document'));
        $this->addDocumentForOwner($riaUser, $this->getReference('inv-document'));

        $manager->flush();
    }

    protected function addDocumentForOwner(User $owner, Document $document)
    {
        if (!$owner->getUserDocuments()->contains($document)) {
            $owner->addUserDocument($document);
        }
    }

    private function createUser()
    {
        /** @var Group $groupAll */
        $groupAll = $this->getReference('group-all');

        $riaUser = new User();
        $riaUser->setUsername('raiden@wealthbot.io');
        $riaUser->setEmail('raiden@wealthbot.io');
        $riaUser->setPlainPassword('ab12cd34EF56gh78');
        $riaUser->setEnabled(true);
        $riaUser->setRoles(['ROLE_RIA']);
        $riaUser->addGroup($groupAll);

        $riaUserProfile = new Profile();
        $riaUserProfile->setUser($riaUser);
        $riaUserProfile->setCompany('wealthbot.io');
        $riaUserProfile->setFirstName('Lord');
        $riaUserProfile->setLastName('Raiden');
        $riaUserProfile->setRegistrationStep(5);

        $riaUser->setProfile($riaUserProfile);

        return $riaUser;
    }

    private function createRiaCompanyInformation(User $riaUser)
    {
        $riaCompanyInfo = new RiaCompanyInformation();

        $riaCompanyInfo->setRia($riaUser);
        $riaCompanyInfo->setState($this->getReference('state-New York'));

        $riaCompanyInfo->setName('Mortal Kombat Advisors');
        $riaCompanyInfo->setPrimaryFirstName('Lord');
        $riaCompanyInfo->setPrimaryLastName('Raiden');
        $riaCompanyInfo->setWebsite('http://www.wealthbot.io');
        $riaCompanyInfo->setAddress('1 Commercial St');
        $riaCompanyInfo->setOffice('Suite 555');
        $riaCompanyInfo->setCity('New York');
        $riaCompanyInfo->setZipcode('12334');
        $riaCompanyInfo->setPhoneNumber('5558588888');
        $riaCompanyInfo->setContactEmail('raiden@wealthbot.io');
        $riaCompanyInfo->setAccountManaged(3);
        $riaCompanyInfo->setIsAllowRetirementPlan(true);
        $riaCompanyInfo->setMinimumBillingFee(500);
        $riaCompanyInfo->setIsShowClientExpectedAssetClass(true);
        $riaCompanyInfo->setClientsTaxBracket(0.1);
        $riaCompanyInfo->setUseMunicipalBond(true);
        $riaCompanyInfo->setRebalancedMethod(1);
        $riaCompanyInfo->setRebalancedFrequency(4);
        $riaCompanyInfo->setIsSearchableDb(true);
        $riaCompanyInfo->setMinAssetSize(50000);
        $riaCompanyInfo->setActivated(true);
        $riaCompanyInfo->setTransactionAmount(200);
        $riaCompanyInfo->setIsTransactionFees(true);
        $riaCompanyInfo->setIsTransactionMinimums(true);
        $riaCompanyInfo->setIsTransactionRedemptionFees(true);
        $riaCompanyInfo->setIsTaxLossHarvesting(true);
        $riaCompanyInfo->setTaxLossHarvesting(50000);
        $riaCompanyInfo->setTaxLossHarvestingPercent(0.1);
        $riaCompanyInfo->setTaxLossHarvestingMinimum(100);
        $riaCompanyInfo->setTaxLossHarvestingMinimumPercent(0.1);
        $riaCompanyInfo->setIsUseQualifiedModels(false);
        $riaCompanyInfo->setPortfolioProcessing(RiaCompanyInformation::PORTFOLIO_PROCESSING_COLLABORATIVE);
        $riaCompanyInfo->setAllowNonElectronicallySigning(true);
        $riaCompanyInfo->setStopTlhValue(4.2);

        /** @var Custodian $custodian */
        $custodian = $this->getReference('custodian');
        $riaCompanyInfo->setCustodian($custodian);

        return $riaCompanyInfo;
    }

    private function createModel(ObjectManager $manager, User $riaUser)
    {
        $categories = $this->createCategories($riaUser);

        $modelManager = new CeModelManager($manager, '\App\Entity\CeModel');
        $model = $modelManager->createCustomModel($riaUser);

        $model->setCommissionMin($this->strategy['commission_min']);
        $model->setCommissionMax($this->strategy['commission_max']);
        $model->setForecast($this->strategy['forecast_min']);
        $model->setGenerousMarketReturn($this->strategy['generous_market_return']);
        $model->setLowMarketReturn($this->strategy['low_market_return']);
        $model->setIsAssumptionLocked($this->strategy['is_assumption_locked']);

        $securityAssignments = [];
        $isExistSecurityAssignment = function ($arr, $securityIndex) {
            return isset($arr[$securityIndex]);
        };

        foreach ($this->strategy['models'] as $modelItem) {
            $child = $modelManager->createChild($model);

            $child->setName($modelItem['name']);
            $child->setRiskRating($modelItem['risk_rating']);
            $child->setIsAssumptionLocked($modelItem['is_assumption_locked']);

            $model->addChildren($child);

            foreach ($modelItem['entities'] as $entityItem) {
                /** @var AssetClass $assetClass */
                $assetClass = $categories[$entityItem['asset_class_index']];
                $assetClass->setModel($model);
                $subclass = $assetClass->getSubclasses()->get($entityItem['subclass_index']);

                /** @var Security $security */
                $security = $this->getReference('security-'.$entityItem['security']);
                if ($isExistSecurityAssignment($securityAssignments, $entityItem['security'])) {
                    $securityAssignment = $securityAssignments[$entityItem['security']];
                } else {
                    $securityAssignment = new SecurityAssignment();
                    $securityAssignment->setSecurity($security);
                    $securityAssignment->setSubclass($subclass);
                    $securityAssignment->setModel($model);

                    $securityAssignments[$entityItem['security']] = $securityAssignment;

                    $this->addReference('model-security-assignment-asset-index-'.$entityItem['asset_class_index'].'-subclass-index-'.$entityItem['subclass_index'].'-security-'.$entityItem['security'], $securityAssignment);
                }

                $entity = new CeModelEntity();

                $entity->setModel($child);
                $entity->setAssetClass($assetClass);
                $entity->setSubclass($subclass);
                $entity->setSecurityAssignment($securityAssignment);
                $entity->setPercent($entityItem['percent']);

                if ($entityItem['muni_substitution_security']) {
                    /** @var Security $muniSubstitutionSecurity */
                    $muniSubstitutionSecurity = $this->getReference('security-'.$entityItem['muni_substitution_security']);

                    if ($isExistSecurityAssignment($securityAssignments, $entityItem['muni_substitution_security'])) {
                        /** @var securityAssignment $muniSubstitutionAssignment */
                        $muniSubstitutionAssignment = $securityAssignments[$entityItem['muni_substitution_security']];
                        $muniSubstitutionAssignment->setMuniSubstitution(true);
                    } else {
                        $muniSubstitutionAssignment = new SecurityAssignment();
                        $muniSubstitutionAssignment->setSecurity($muniSubstitutionSecurity);
                        $muniSubstitutionAssignment->setSubclass($subclass);
                        $muniSubstitutionAssignment->setModel($model);

                        $securityAssignments[$entityItem['muni_substitution_security']] = $muniSubstitutionAssignment;
                    }

                    $entity->setMuniSubstitution($muniSubstitutionAssignment);
                }

                if ($entityItem['tax_loss_harvesting_security']) {
                    /** @var Security $taxLossHarvestingSecurity */
                    $taxLossHarvestingSecurity = $this->getReference('security-'.$entityItem['tax_loss_harvesting_security']);

                    if ($isExistSecurityAssignment($securityAssignments, $entityItem['tax_loss_harvesting_security'])) {
                        /** @var securityAssignment $taxLossHarvestingAssignment */
                        $taxLossHarvestingAssignment = $securityAssignments[$entityItem['tax_loss_harvesting_security']];
                        $taxLossHarvestingAssignment->setMuniSubstitution(true);
                    } else {
                        $taxLossHarvestingAssignment = new SecurityAssignment();
                        $taxLossHarvestingAssignment->setSecurity($taxLossHarvestingSecurity);
                        $taxLossHarvestingAssignment->setSubclass($subclass);
                        $taxLossHarvestingAssignment->setModel($model);

                        $securityAssignments[$entityItem['tax_loss_harvesting_security']] = $taxLossHarvestingAssignment;
                    }

                    $entity->setTaxLossHarvesting($taxLossHarvestingAssignment);
                }

                $child->addModelEntity($entity);
            }

            $this->addReference('cec-ria-model-'.$modelItem['index'], $child);
        }

        return $model;
    }

    private function createCategories(User $riaUser)
    {
        $categories = [];

        foreach ($this->categories as $category) {
            $asset = new AssetClass();

            $asset->setName($category['name']);
            $asset->setType($category['type']);

            foreach ($category['subclasses'] as $item) {
                $subclass = new Subclass();

                $subclass->setOwner($riaUser);
                $subclass->setAssetClass($asset);
                $subclass->setName($item['name']);
                $subclass->setExpectedPerformance($item['expected_performance']);
                $subclass->setAccountType($this->getReference('subclass-account-type-'.$item['account_type_index']));
                $subclass->setPriority($item['priority']);
                $subclass->setToleranceBand($item['tolerance_band']);

                $asset->addSubclasse($subclass);
            }

            $categories[] = $asset;
        }

        return $categories;
    }

    private function saveRiskQuestions(ObjectManager $manager, User $owner)
    {
        foreach ($this->riskProfiling as $qIndex => $questionItem) {
            $adminQuestion = $this->getReference('risk-question-'.$questionItem['question_index']);

            $question = new RiskQuestion();
            $question->setTitle($adminQuestion->getTitle());
            $question->setDescription($adminQuestion->getDescription());
            $question->setIsWithdrawAgeInput($adminQuestion->getIsWithdrawAgeInput());
            $question->setOwner($owner);
            $question->setSequence($questionItem['sequence']);

            foreach ($questionItem['answers'] as $aIndex => $answerItem) {
                $adminAnswer = $this->getReference('risk-answer-'.$questionItem['question_index'].'-'.$answerItem['answer_index']);

                $answer = new RiskAnswer();
                $answer->setQuestion($question);
                $answer->setTitle($adminAnswer->getTitle());
                $answer->setPoint($answerItem['point']);

                $question->addAnswer($answer);
                $this->addReference('cec-answer-'.$qIndex.'-'.$aIndex, $answer);
            }

            $manager->persist($question);
            $this->addReference('cec-question-'.$qIndex, $question);
        }
    }

    private function saveClientsData(ObjectManager $manager, User $riaUser)
    {
        $clientPortfolioManager = $this->container->get('wealthbot_client.client_portfolio.manager');

        foreach ($this->clients as $indexOfClient => $clientData) {
            $clientUser = $this->createClientUser($clientData, $riaUser);

            $this->setReference('clientN'.$indexOfClient, $clientUser);

            //$this->saveClientPortfolio($manager, $clientUser);

            // Add personal information
            if (isset($this->clientsPersonalInformation[$indexOfClient])) {
                $personalInformationData = $this->clientsPersonalInformation[$indexOfClient];
                $personalInformation = $this->createClientPersonalInformation($personalInformationData, $clientUser);
                $clientUser->setClientPersonalInformation($personalInformation);
            }

            // Add additional contact
            if (isset($this->clientsAdditionalContacts[$indexOfClient])) {
                $contactData = $this->clientsAdditionalContacts[$indexOfClient];
                $additionalContact = $this->createClientAdditionalContact($contactData, $clientUser);
                $clientUser->addAdditionalContact($additionalContact);
            }

            $manager->persist($clientUser);
            $manager->flush();
            $manager->refresh($clientUser);

            // Add accounts
            if (isset($this->clientAccounts[$indexOfClient])) {
                foreach ($this->clientAccounts[$indexOfClient] as $indexOfAccount => $accountItem) {
                    $account = $this->createClientAccount($manager, $accountItem, $clientUser);
                    $clientUser->addClientAccount($account);

                    if (isset($this->clientBeneficiaries[$indexOfClient]) &&
                        isset($this->clientBeneficiaries[$indexOfClient][$indexOfAccount])) {
                        $beneficiaryData = $this->clientBeneficiaries[$indexOfClient][$indexOfAccount];
                        $beneficiary = $this->createClientBeneficiary($beneficiaryData, $account);
                        $account->addBeneficiarie($beneficiary);
                    }

                    if (isset($this->systemClientAccounts[$indexOfClient]) &&
                        isset($this->systemClientAccounts[$indexOfClient][$indexOfAccount])) {
                        $systemAccountData = $this->systemClientAccounts[$indexOfClient][$indexOfAccount];
                        $systemAccount = $this->createSystemAccount($systemAccountData, $account);
                        $account->setSystemAccount($systemAccount);

                        $this->addReference('system-account-'.$systemAccount->getAccountNumber(), $systemAccount);

                        if (array_key_exists('creationType', $this->systemClientAccounts[$indexOfClient][$indexOfAccount])) {
                            $systemAccount->setCreationType($this->systemClientAccounts[$indexOfClient][$indexOfAccount]['creationType']);
                        }
                    }
                }
            }

            // Add client questionnaire answers
            if (isset($this->clientQuestionnaire[$indexOfClient])) {
                foreach ($this->clientQuestionnaire[$indexOfClient] as $questionnaireItem) {
                    $clientAnswer = $this->createClientQuestionnaireAnswer($questionnaireItem, $clientUser);
                    $manager->persist($clientAnswer);
                }
            }

            $manager->persist($clientUser);

            // Add client portfolio and create workflow
            $proposedModel = $this->getReference('cec-ria-model-'.$clientData['suggested_portfolio_index']);
            $portfolio = $clientPortfolioManager->proposePortfolio($clientUser, $proposedModel);

            $registrationStep = $clientUser->getRegistrationStep();
            if ($registrationStep > 3) {
                $clientPortfolioManager->approveProposedPortfolio($clientUser);

                if ($registrationStep > 4) {
                    //And now creating new Subclass, new SecurityAssignments etc. Look into CeModelManager.
                    $portfolio = $clientPortfolioManager->acceptApprovedPortfolio($clientUser);
                    if (isset($clientData['approved_at'])) {
                        $portfolio->setApprovedAt(new \DateTime($clientData['approved_at']));
                    }
                }
            }

            $this->addReference('client-portfolio-'.$portfolio->getClient()->getProfile(), $portfolio);
        }
    }

    private function createClientUser(array $data, User $riaUser)
    {
        $clientUser = new User();

        $clientUser->setUsername($data['username']);
        $clientUser->setEmail($data['username']);
        $clientUser->setPlainPassword($data['password']);
        $clientUser->setEnabled(true);
        $clientUser->setRoles(['ROLE_CLIENT']);

        $clientUserProfile = new Profile();

        $clientUserProfile->setUser($clientUser);
        $clientUserProfile->setRia($riaUser);
        $clientUserProfile->setFirstName($data['first_name']);
        $clientUserProfile->setLastName($data['last_name']);
        $clientUserProfile->setState($this->getReference('state-'.$data['state']));
        $clientUserProfile->setStreet($data['street']);
        $clientUserProfile->setCity($data['city']);
        $clientUserProfile->setZip($data['zip']);
        $clientUserProfile->setBirthDate(new \DateTime($data['birth_date']));
        $clientUserProfile->setPhoneNumber($data['phone_number']);
        $clientUserProfile->setMaritalStatus($data['marital_status']);
        $clientUserProfile->setAnnualIncome($data['annual_income']);
        $clientUserProfile->setEstimatedIncomeTax($data['estimated_income_tax']);
        $clientUserProfile->setLiquidNetWorth($data['liquid_net_worth']);
        $clientUserProfile->setEmploymentType($data['employment_type']);
        $clientUserProfile->setClientAccountManaged($data['client_account_managed']);
        $clientUserProfile->setRegistrationStep($data['registration_step']);

        $clientSettings = new ClientSettings();
        $clientSettings->setStopTlhValue($data['stop_tlh_value']);
        $clientUser->setClientSettings($clientSettings);
        $clientSettings->setClient($clientUser);

        //$clientUserProfile->setSuggestedPortfolio($this->getReference('cec-ria-model-' . $data['suggested_portfolio_index']));

        if (isset($data['client_status'])) {
            $clientUserProfile->setClientStatus($data['client_status']);
        }

        $clientUser->setProfile($clientUserProfile);

        if (isset($data['created'])) {
            $createdAt = new \DateTime($data['created']);
            $clientUser->setCreated($createdAt);
        }

        if (isset($data['paymentMethod'])) {
            $clientUser->getProfile()->setPaymentMethod($data['paymentMethod']);
        }

        return $clientUser;
    }

    private function createClientPersonalInformation(array $data, User $clientUser)
    {
        $personalInformation = new PersonalInformation();

        $personalInformation->setClient($clientUser);
        $personalInformation->setSsnTin($data['ssn_tin']);
        $personalInformation->setIncomeSource($data['income_source']);
        $personalInformation->setEmployerName($data['employer_name']);
        $personalInformation->setIndustry($data['industry']);
        $personalInformation->setOccupation($data['occupation']);
        $personalInformation->setBusinessType($data['business_type']);
        $personalInformation->setEmployerAddress($data['employer_address']);
        $personalInformation->setCity($data['city']);
        $personalInformation->setZipcode($data['zipcode']);

        return $personalInformation;
    }

    private function createClientAdditionalContact(array $data, User $clientUser)
    {
        $additionalContact = new ClientAdditionalContact();

        $additionalContact->setClient($clientUser);

        if ($data['state']) {
            $additionalContact->setState($this->getReference('state-'.$data['state']));
        }

        $additionalContact->setFirstName($data['first_name']);
        $additionalContact->setLastName($data['last_name']);
        $additionalContact->setMiddleName($data['middle_name']);
        $additionalContact->setStreet($data['street']);
        $additionalContact->setCity($data['city']);
        $additionalContact->setZip($data['zip']);
        $additionalContact->setIsDifferentAddress($data['is_different_address']);
        $additionalContact->setBirthDate(new \DateTime($data['birth_date']));
        $additionalContact->setPhoneNumber($data['phone_number']);
        $additionalContact->setSsnTin($data['ssn_tin']);
        $additionalContact->setIncomeSource($data['income_source']);

        $additionalContact->setIsSeniorPoliticalFigure($data['is_senior_political_figure']);
        $additionalContact->setIsPubliclyTradedCompany($data['is_publicly_traded_company']);
        $additionalContact->setIsBrokerSecurityExchangePerson($data['is_broker_security_exchange_person']);
        $additionalContact->setEmail($data['email']);
        $additionalContact->setType($data['type']);
        $additionalContact->setEmploymentType($data['employment_type']);

        return $additionalContact;
    }

    /**
     * @param ObjectManager $manager
     * @param array         $data
     * @param User          $clientUser
     *
     * @return ClientAccount
     */
    private function createClientAccount(ObjectManager $manager, array $data, User $clientUser)
    {
        $securityRepository = $manager->getRepository('App\Entity\Security');

        $account = new ClientAccount();

        $account->setClient($clientUser);
        $account->setGroupType($this->getReference('client-account-group-type-'.$data['group_type_key']));
        $account->setFinancialInstitution($data['financial_institution']);
        $account->setValue($data['value']);
        $account->setMonthlyContributions($data['monthly_contributions']);
        $account->setMonthlyDistributions($data['monthly_distributions']);
        $account->setSasCash($data['sas_cash']);
        $account->setProcessStep($data['process_step']);
        $account->setStepAction($data['step_action']);
        $account->setIsPreSaved($data['is_pre_saved']);
        $account->setUnconsolidated($data['unconsolidated']);

        if ($data['consolidator_index']) {
            $consolidator = $clientUser->getClientAccounts()->get($data['consolidator_index'] - 1);
            $account->setConsolidator($consolidator);
        }

        foreach ($data['owners'] as $ownerType) {
            $accountOwner = new ClientAccountOwner();

            if (ClientAccountOwner::OWNER_TYPE_SELF === $ownerType) {
                $accountOwner->setClient($clientUser);
            } else {
                $accountOwner->setContact($clientUser->getAdditionalContacts()->first());
            }

            $accountOwner->setOwnerType($ownerType);
            $accountOwner->setAccount($account);
            $account->addAccountOwner($accountOwner);
        }

        $manager->persist($account);
        $manager->flush();
        $manager->refresh($account);

        if (isset($data['account_contribution'])) {
            $accountContribution = new AccountContribution();

            $accountContribution->setAccount($account);
            $accountContribution->setType($data['account_contribution']['type']);
            $accountContribution->setTransactionFrequency($data['account_contribution']['transaction_frequency']);
            $account->setAccountContribution($accountContribution);

            $manager->persist($accountContribution);
        }

        if (isset($data['securities'])) {
            foreach ($data['securities'] as $securityItem) {
                //ToDo: CE-402. Check that code is not needed more.
//                $security = $securityRepository->findOneBySymbol($securityItem['symbol']);
//                if (!$security) {
//                    /** @var SecurityType $securityType */
//                    $securityType = $this->getReference('security-type-' . $securityItem['type']);
//
//                    $security = new Security();
//                    $security->setName($securityItem['name']);
//                    $security->setSymbol($securityItem['symbol']);
//                    $security->setSecurityType($securityType);
//                    $security->setExpenseRatio($securityItem['exp_ratio']);
//                }

//                $securityAssignment = new SecurityAssignment();
//                $securityAssignment->setSecurity($security);
//                $securityAssignment->setRia($clientUser->getRia()); Deprecated

//                $accountOutsideFund = new AccountOutsideFund();
//                $accountOutsideFund->setAccount($account);
//                $accountOutsideFund->setSecurityAssignment($securityAssignment);
//                $accountOutsideFund->setIsPreferred(false);
//
//                $manager->persist($accountOutsideFund);
            }
        }

        $manager->persist($account);
        $manager->flush();

        $this->addReference('client-account-'.$account->getId(), $account);

        return $account;
    }

    private function createClientBeneficiary(array $data, ClientAccount $account)
    {
        $beneficiary = new Beneficiary();

        $beneficiary->setAccount($account);
        $beneficiary->setType($data['type']);
        $beneficiary->setState($this->getReference('state-'.$data['state']));
        $beneficiary->setFirstName($data['first_name']);
        $beneficiary->setLastName($data['last_name']);
        $beneficiary->setMiddleName($data['middle_name']);
        $beneficiary->setSsn($data['ssn']);
        $beneficiary->setBirthDate(new \DateTime($data['birth_date']));
        $beneficiary->setStreet($data['street']);
        $beneficiary->setCity($data['city']);
        $beneficiary->setZip($data['zip']);
        $beneficiary->setRelationship($data['relationship']);
        $beneficiary->setShare($data['share']);

        return $beneficiary;
    }

    private function createSystemAccount(array $data, ClientAccount $account)
    {
        $systemAccount = new SystemAccount();

        $systemAccount->setClientAccount($account);
        $systemAccount->setClient($account->getClient());
        $systemAccount->setAccountNumber($data['account_number']);
        $systemAccount->setAccountDescription($data['account_description']);
        $systemAccount->setType($data['type']);
        $systemAccount->setStatus($data['status']);

        if (SystemAccount::STATUS_ACTIVE === $data['status']) {
            $systemAccount->setActivatedOn(new \DateTime());
        } elseif (SystemAccount::STATUS_CLOSED === $data['status']) {
            $systemAccount->setClosed(new \DateTime());
        }

        $systemAccount->setSource(SystemAccount::SOURCE_SAMPLE);

        return $systemAccount;
    }

    private function createClientQuestionnaireAnswer(array $data, User $clientUser)
    {
        $clientAnswer = new ClientQuestionnaireAnswer();

        $clientAnswer->setClient($clientUser);
        $clientAnswer->setQuestion($this->getReference('cec-question-'.$data['q_index']));
        $clientAnswer->setAnswer($this->getReference('cec-answer-'.$data['q_index'].'-'.$data['a_index']));

        return $clientAnswer;
    }

    public function getOrder()
    {
        return 8;
    }
}
