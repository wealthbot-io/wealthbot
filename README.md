wealthbot.io
===============

[![Software License](https://img.shields.io/badge/license-GPL-green.svg)](LICENSE)

## Wealth Management, Set Free

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

## Demo

Go to http://demo.wealthbot.io to play with *all the features* before installing.

## Installation

Our super easy, 7 Step Installation.

1. `git clone https://github.com/wealthbot-io/core.git wealthbot`
2. Install [Vagrant](https://www.vagrantup.com/) and [Virtual Box](https://www.virtualbox.org/)
3. `vagrant up` in /wealthbot/vagrant and wait
4. Once complete, `vagrant ssh` in the same folder
5. You are now in your Virtual Box. `cd /srv/wealthbot`
6. run `composer update`
7. Open your browser to `http://192.168.56.105/` to see the wealthbot.io demo landing page

More specific docs are [here](app/Resources/doc).
