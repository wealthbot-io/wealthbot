#!/usr/bin/env bash


cd /var/www/symfony
chmod -R 0777 app/cache app/logs
composer clear-cache
composer install
mongo < mongo_user.js
mongo < mongo_user_test.js
app/console doctrine:database:drop --force
app/console doctrine:database:create
app/console doctrine:schema:create
app/console doctrine:fixtures:load --append
app/console assetic:dump

