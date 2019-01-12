wealthbot.io
===============

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
* Muni substitiution

## Demo

Go to http://demo.wealthbot.io to play with *all the features* before installing.

## Community

Join our Gitter to discuss the project in realtime.
* Talk to the core devs and the wealthbot.io community.
* Learn from others and ask questions.
* Share your work and demos.

https://gitter.im/wealthbot-io

## Installation

**Prereqs:**
* Install [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/)
* Run `docker-compose build` in the root folder
* Then `docker-compose up -d`

Once complete, simply go to http://wealthbot.localhost in your browser to see the wealthbot.io demo landing page.

Note: To use the app you'll need to setup an SMTP server with authentication. The config is in .env Environment file. Digital Ocean has [a good writeup on how to use your Gmail account](https://www.digitalocean.com/community/tutorials/how-to-use-google-s-smtp-server) for this.

If you want to take a look around your docker container you can `docker-compose exec wealthbot_php_1 bash`.

To access Kibana (ELK Stack) go to http://wealthbot.localhost:81/

We strongly recommend running all console commands inside the docker container.

Search /* TODO:<Symfony3> */ for files to refactor

# Contributing

We love pull requests! The details on how to contribute to Wealthbot can be found [here](docs/CONTRIBUTING.md).