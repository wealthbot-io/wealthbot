wealthbot.io
===============

## Partnership and participation
The code is functional, but offered as-is.
If you are interested in partnership or working on the core and new features, you can write to hi@wealthbot.io.

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
* Tax loss harvesting
* Custom portfolio models
* Custom risk profiling
* Integration with financial custodians
* Automated onboarding, document flow and e-signing
* Cash generating transactions while maintaining target asset allocation
* Muni substitution

## Installation

**For installation**

1. Clone The [repository](https://github.com/wealthbot-io/devenv): `git clone git@github.com:wealthbot-io/devenv.git`
2. Move to dir:  `cd devenv`
3. Build Docker Containers: Run `docker-compose up --build -d`
4. Find ID of PHP Container: Run `docker ps` and look for the container ID (ex. `abcd123efg`) beside devenv_monolith.php 
5. Create the DB schema: Run `docker exec -it abcd123efg bin/console doctrine:schema:create`
6. Load the fixtures: Run `docker exec -it abcd123efg bin/console wealthbot:fixtures:load`


Once complete, simply go to http://127.0.0.1:10001 in your browser to see the wealthbot.io demo landing page.

## Community

Join our Slack to discuss the project in realtime.

https://wealthbot-io.slack.com/


## Contributing

We love pull requests! The details on how to contribute to Wealthbot can be found [here](.github/CONTRIBUTING.md).


## Donation

You can make a donation to sponsor this project:

BTC: `12rXu28cidvH8guAe6t4rRAg2BzdGKS6Zu`

ETH: `0x6bdb45921acD0CD4D770a3c791CCf79934A19D7f` 
