wealthbot.io
===============

[![GitHub license](https://img.shields.io/github/license/mashape/apistatus.svg)]()

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

## Community

Join our Slack server to discuss the project in realtime.
* Talk to the core devs and the wealthbot.io community.
* Learn from others and ask questions.
* Share your work and demos.

https://webo-slack.herokuapp.com/

Contribute to our roadmap on Trello.
* Follow our general vision
* Submit new feature ideas
* Comment on ideas, to-dos and in-progress work

https://trello.com/b/klhsT5Xj/wealthbot-io-roadmap-and-ideas

## Installation

**Prereqs:**
* Install [Vagrant](https://www.vagrantup.com/) and [Virtual Box](https://www.virtualbox.org/)
* You'll need an NFS plugin. Once vagrant is installed run: `vagrant plugin install vagrant-bindfs`

And now, our super-simple, 3-step install

1. `git clone https://github.com/wealthbot-io/wealthbot`
2. Add `192.168.56.105  local.wealthbot.io` to your etc/hosts file
3. `cd wealthbot/vagrant` and run `vagrant up` (run `vagrant provision` at first-run)

*[If you prefer your instructions via Youtube](https://www.youtube.com/watch?v=cZQONErBFXo)*

Go grab a coffee or a beer ... this will take a while the first time you run it.

Once complete, simply go to http://local.wealthbot.io in your browser to see the wealthbot.io demo landing page.

Note: To use the app you'll need to setup an SMTP server with authentication. The config is in wealthbot/app/config/parameters.yml. Digital Ocean has [a good writeup on how to use your Gmail account](https://www.digitalocean.com/community/tutorials/how-to-use-google-s-smtp-server) for this. 

If you want to take a look around your vagrant box you can `vagrant ssh`.
The config is located in `wealthbot\vagrant\puphpet\config.yaml`

We strongly recommend running all console commands inside the vagrant box.

# Contributing 

We love pull requests! The details on how to contribute to Wealthbot can be found [here](CONTRIBUTING.md).



[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/wealthbot-io/wealthbot/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

