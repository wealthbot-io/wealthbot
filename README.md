wealthbot.io
===============

**Written with Symfony Flex**

[![](https://www.codeshelter.co/static/badges/badge-flat.svg)](https://www.codeshelter.co/)
[![GitHub license](https://img.shields.io/github/license/mashape/apistatus.svg)]()

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/881769ff-b0e8-43f0-b67f-e0915d7aee5f/big.png)](https://insight.sensiolabs.com/projects/881769ff-b0e8-43f0-b67f-e0915d7aee5f)

For development purposes write to hi[@]createit.am 


### Wealth Management, Set Free

Hi, I'm [wealthbot.io](http://wealthbot.io). Webo for short. I'm an open source wealth management platform. I help Investment Advisors profitably serve the mass affluent.

**Use Cases**

* Help human investment advisors compete with robo-advisors
* Build your own SaaS robo-advisor
* Manage a personal portfolio
* Run multiple RIA firms under a single installation

**Modules**

* Admin Control Panel - manage wealthbot.io installation, including RIA and Client accounts.
* Client Dashboard - beautiful transaction, holding and performance reports on any screen.
* RIA Portal - define asset classes and representative securities, setup custom portofilios and multi-tiered billing, create your own risk profile questionnaire,  manage clients and document workflows.
* Rebalancer - automatically rebalance client portfolios to match target allocations at the household or account-level, ad-hoc or on an set schedule.
* Portfolio Accounting System - integrate with custodians to verify customer demographics and reconcile transactions placed by the Rebalancer.

**Yea, We've Got That**

* Tax loss harvesting
* Custom portfolio models
* Custom risk profiling
* Integration with financial custodians
* Automated onboarding, document flow and e-signing
* Cash generating transactions while maintaining target asset allocation
* Muni substitiution

## Community

Join our Gitter to discuss the project in realtime.
* Talk to the core devs and the wealthbot.io community.
* Learn from others and ask questions.
* Share your work and demos.

https://gitter.im/wealthbot-io

## Installation

**Prereqs:**

* PHP 7.3 with GD and Imagick
* MySQL 5.7 or 8
* Composer
* Curl extension
* Zip extension
* GD extension
* MySQL extension

**For installation**

1. Install symofony installer: `install symfony  curl -sS https://get.symfony.com/cli/installer | bash`
2. Clone The repository: `git clone https://github.com/wealthbot-io/wealthbot`
3. Move to dir:  `cd wealthbot`
4. Install composer dependencies: `symfony composer install --ignore-platform-reqs`
5. Make configurations: `customise .env.{prod|dev}.local file`
6. Create the database: `bin/console doctrine:database:create`
7. Create the DB schema: `bin/console doctrine:schema:create`
8. Load the fixtures: `bin/console wealthbot:fixtures:load`
9. Update security prices: `bin/console wealthbot:security:price`
10. Run the symfony local server: `symfony serve`

Once complete, simply go to https://127.0.0.1:8000 in your browser to see the wealthbot.io demo landing page.


**Cron jobs**

* `30 1 * * * /usr/bin/php bin/console rx:mailer:send-cron-emails`
* `30 2 * * * /usr/bin/php bin/console wealthbot:security:price`
* `0 0 1 * * /usr/bin/php bin/console wealthbot:rebalancer`

## Contributing

We love pull requests! The details on how to contribute to Wealthbot can be found [here](.github/CONTRIBUTING.md).
