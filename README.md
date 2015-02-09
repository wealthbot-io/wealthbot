wealthbot.io
===============


Local setup
---------------

1. git clone git@github.com:wealthbot-io/core.git
2. Go to app/config folder and make a copy of the file parameters.yml.dist and rename it to parameters.yml
3. Remove composer.lock file and run the 'php composer.phar install' command
4. Install and setp DB's (MySQL and Mongo), then configure parameters.yml
5. Add parameters uploads_dir, uploads_ria_company_logos_dir and uploads_documents_dir in parameters.yml

##### Example:

    uploads_dir: ../uploads
    uploads_ria_company_logos_dir: %uploads_dir%/ria_company_logos
    uploads_documents_dir: %uploads_dir%/documents

6. Install ImageMagick library:

##### Example for ubuntu:
    $ apt-get install imagemagick
    $ service apache2 restart

7. Check if app/console works by running commands below:

##### Example:

    $ app/console doctrine:database:drop
    $ app/console doctrine:database:create
    $ app/console doctrine:schema:update --force
    $ app/console doctrine:fixtures:load

8. Make sure APC (or APCu) is enabled


More specific docs are [here](app/Resources/doc).