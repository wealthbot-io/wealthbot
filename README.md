wealthbot.io
===============

## Partnership and participation
The code is functional, but offered as-is.
If you are interested in partnership or working on the core and new features, you can write to vlad.kobilansky+webo@gmail.com.

**Written with Symfony Flex**

[![](https://www.codeshelter.co/static/badges/badge-flat.svg)](https://www.codeshelter.co/)
[![GitHub license](https://img.shields.io/github/license/mashape/apistatus.svg)]()

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/881769ff-b0e8-43f0-b67f-e0915d7aee5f/big.png)](https://insight.sensiolabs.com/projects/881769ff-b0e8-43f0-b67f-e0915d7aee5f)

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
* Muni substitution

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

1. Install symofony installer: `curl -sS https://get.symfony.com/cli/installer | bash`
2. Clone The repository: `git clone https://github.com/wealthbot-io/wealthbot`
3. Move to dir:  `cd wealthbot`
4. Install composer dependencies: `symfony composer install --ignore-platform-reqs`
5. Override configurations: `nano .env.local file`
6. Create the database: `bin/console doctrine:database:create`
7. Create the DB schema: `bin/console doctrine:schema:create`
8. Load the fixtures: `bin/console wealthbot:fixtures:load`
9. Update security prices: `bin/console wealthbot:security:price`
10. Run the symfony local server: `symfony serve`
11. Register at tradier.com and get API keys for RIA (Brokerage Account)
12. Place your logo in public/img/logo.png and public/img/big_logo.png


Once complete, simply go to https://127.0.0.1:8000 in your browser to see the wealthbot.io demo landing page.


**Cron jobs**

* `30 1 * * * /usr/bin/php bin/console rx:mailer:send-cron-emails`
* `30 2 * * * /usr/bin/php bin/console wealthbot:security:price`
* `0 0 1 * * /usr/bin/php bin/console wealthbot:rebalancer`


## Community

Join our Slack to discuss the project in realtime.

https://wealthbot-io.slack.com/


## Contributing

We love pull requests! The details on how to contribute to Wealthbot can be found [here](.github/CONTRIBUTING.md).


## Donation

You can make a donation to sponsor this project:

BTC: `12rXu28cidvH8guAe6t4rRAg2BzdGKS6Zu`

ETH: `0x6bdb45921acD0CD4D770a3c791CCf79934A19D7f` 





# 🐳 Docker Installation

## Description

This is a complete stack for running Symfony 5 into Docker containers using docker-compose tool.

It is composed by 3 containers:

- `nginx`, acting as the webserver.
- `php`, the PHP-FPM container with the 7.4 PHPversion.
- `db` which is the MySQL database container with a **MySQL 8.0** image.

## Installation

1. 😀 Clone this rep.

2. Run `docker-compose build && docker-compose up -d`

3. The 3 containers are deployed: 

```
Creating symfony-docker_db_1    ... done
Creating symfony-docker_php_1   ... done
Creating symfony-docker_nginx_1 ... done
```

4. Use this value for the DATABASE_URL environment variable of Symfony:

```
DATABASE_URL=mysql://app_user:helloworld@db:3306/app_db?serverVersion=5.7
```

You could change the name, user and password of the database in the `env` file at the root of the project.

