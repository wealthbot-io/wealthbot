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

**Prereqs:**
* Install [Vagrant](https://www.vagrantup.com/) and [Virtual Box](https://www.virtualbox.org/)
* You'll need an NFS plugin. Once vagrant is installed run: `vagrant plugin install vagrant-bindfs`

And now, our super-simple, 3-step install

1. `git clone https://github.com/wealthbot-io/wealthbot`
2. Add `192.168.56.105  local.wealthbot.io` to your etc/hosts file
3. `cd wealthbot/vagrant` and run `vagrant up`

*[If you prefer your instructions via Youtube](https://www.youtube.com/watch?v=cZQONErBFXo)*

Go grab a coffee or a beer ... this will take a while the first time you run it.

Once complete, simply go to http://local.wealthbot.io in your browser to see the wealthbot.io demo landing page.

Note: To use the app you'll need to setup an SMTP server with authentication. The config is in wealthbot/app/config/parameters.yml. Digital Ocean has [a good writeup on how to use your Gmail account](https://www.digitalocean.com/community/tutorials/how-to-use-google-s-smtp-server) for this. 

If you want to take a look around your vagrant box you can `vagrant ssh`.
The config is located in `wealthbot\vagrant\puphpet\config.yaml`

We strongly recommend running all console commands inside the vagrant box.

# Contributing 

### We love pull requests! 

Push to your fork and [submit a pull request](https://github.com/wealthbot-io/wealthbot/compare).

At this point you're waiting on us. We like to at least comment on pull requests within three business days (and, typically, one business day). We may suggest some changes or improvements or alternatives.

Some things that will increase the chance that your pull request is accepted:

* Write tests.
* Write a good commit message.


