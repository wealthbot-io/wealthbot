wealthbot.io
===============


Local setup
---------------

1. git clone git@github.com:wealthbot-io/core.git
2. Go to app/config folder and make a copy of the file parameters.yml.dist and rename it to parameters.yml

     $ cp parameters.yml.dist parameters.yml

3. Remove composer.lock file from /core and run
    
     $ php composer.phar install

4. Install and setp DB's (MySQL and Mongo), then configure parameters.yml

### Install MongoDB with Homebrew
Homebrew installs binary packages based on published “formulae.” This section describes how to update brew to the latest packages and install MongoDB. Homebrew requires some initial setup and configuration, which is beyond the scope of this document.

1.  Update Homebrew’s package database.
In a system shell, issue the following command:

     $ brew update

2. Install MongoDB.
To install the MongoDB binaries, issue the following command in a system shell:

     brew install mongodb

------

5. Add parameters uploads_dir, uploads_ria_company_logos_dir and uploads_documents_dir in parameters.yml

##### Example:

    uploads_dir: ../uploads
    uploads_ria_company_logos_dir: %uploads_dir%/ria_company_logos
    uploads_documents_dir: %uploads_dir%/documents

6. Install ImageMagick library:

##### For ubuntu, use apt-get:
    $ apt-get install imagemagick
    $ service apache2 restart
    
##### For Mac OS, use homebrew:
    $ brew install imagemagick

7. Check if app/console works by running commands below:

##### Example:

    $ app/console doctrine:database:drop
    $ app/console doctrine:database:create
    $ app/console doctrine:schema:update --force
    $ app/console doctrine:fixtures:load

8. Make sure APC (or APCu) is enabled


More specific docs are [here](app/Resources/doc).
