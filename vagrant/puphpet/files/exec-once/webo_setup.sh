#disable default vhosts
echo .... disable default vhost 80 ....
sudo a2dissite 10-default_vhost_80.conf
echo .... disable default vhost 443 ....
sudo a2dissite 10-default_vhost_443.conf

#reload apache
sudo service apache2 reload

#update dependencies
cd /srv/wealthbot
composer clear-cache
composer install --prefer-source

#create mongo user webo
echo .... creating mongo user 'webo' ....
mongo < mongo_user.js

#create mongo user for unit tests
mongo < mongo_user_test.js

#clear cache
app/console cache:clear --env=dev --no-debug
app/console cache:clear --env=prod --no-debug

#setup db and load fixtures
app/console doctrine:database:drop --force
app/console doctrine:database:create
app/console doctrine:schema:create
app/console doctrine:fixtures:load

#warming up cache
echo .... warming up cache ....
app/console cache:warmup --env=dev
app/console cache:warmup --env=prod

