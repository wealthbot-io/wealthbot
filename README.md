wealthbot.io
===============


Local setup
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

4. Install the [Composer dependency manager](https://getcomposer.org/doc/00-intro.md), and run this command to install the wealthbot.io's dependencies:
    
```
php composer.phar install
```

### Install MongoDB with Homebrew
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

8. Use a PHP OpCache 

Use [either APC, or PHP >=5.5](https://www.digitalocean.com/community/questions/how-to-install-alternative-php-cache-apc-on-ubuntu-14-04) depending on your `php --version`


More specific docs are [here](app/Resources/doc).
