#!/usr/bin/env bash

chmod -R 0777 var
COMPOSER_MEMORY_LIMIT=-1 composer up --ignore-platform-reqs
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
php bin/console wealthbot:fixtures:load --append
php bin/console assetic:dump

