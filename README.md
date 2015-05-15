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
* Custom portolio models
* Custom risk profiling
* Integration with financial custodians
* Automated onboarding document flow and e-signing
* Cash generating transactions while maintaining target asset allocation
* Muni substitiution

## Demo

Go to http://demo.wealthbot.io to play with *all the features* before installing.

## Installation

Setup
---------------

1. git `clone git@github.com:wealthbot-io/core.git`

2. Install and setup [LAMP](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04) and [MongoDB](http://docs.mongodb.org/manual/tutorial/install-mongodb-on-ubuntu/#install-mongodb)

You'll also need the PHP Mongo Driver.  In Ubuntu/Debian:
```
sudo apt-get php5-mongo
```

3. Go to `app/config` folder and copy `parameters.yml.dist` to `parameters.yml`.  This file configures wealthbot.io with database passwords, settings, and etc.  Be sure you can login to MySQL and Mongo with the settings in this file.

```
cd app/config
cp parameters.yml.dist parameters.yml
nano parameters.yml
```

Create your databases like so (you may need to change passwords)
```
mysql -u root -e 'CREATE DATABASE wealthbot'   
```

4. Install the [Composer dependency manager](https://getcomposer.org/doc/00-intro.md), and run this command to install the wealthbot.io's dependencies:
    
```
php composer.phar install
```

#### Install MongoDB with Homebrew
Homebrew installs binary packages based on published “formulae.” This section describes how to update brew to the latest packages and install MongoDB. Homebrew requires some initial setup and configuration, which is beyond the scope of this document.

1.  Update Homebrew’s package database.
In a system shell, issue the following command:

```
     $ brew update
```

2. Install MongoDB.
To install the MongoDB binaries, issue the following command in a system shell:

```
     brew install mongodb
```

------

5. Add parameters uploads_dir, uploads_ria_company_logos_dir and uploads_documents_dir in parameters.yml

##### Example:

```
    uploads_dir: ../uploads
    uploads_ria_company_logos_dir: %uploads_dir%/ria_company_logos
    uploads_documents_dir: %uploads_dir%/documents
```
6. Install ImageMagick library:

##### For ubuntu, use apt-get:
```
    $ apt-get install imagemagick
    $ service apache2 restart
```
##### For Mac OS, use homebrew:
```
    $ brew install imagemagick
```
7. Check if app/console works by running commands below:

##### Example:

    $ app/console doctrine:database:drop
    $ app/console doctrine:database:create
    $ app/console doctrine:schema:update --force
    $ [php -d memory_limit=536870912] app/console doctrine:fixtures:load

8. Install APC

```
sudo apt-get install php-apc
```

9. Setup your database:

```
php app/console doctrine:database:create
php app/console doctrine:schema:create
```

10. Configure and start your webserver (Apache or nginx).  For testing, use Symfony2's mini web server

```
cd core
php app/console server:run
```

11. Open your browser to `http://localhost:8000` to see your wealthbot.io.

More specific docs are [here](app/Resources/doc).
