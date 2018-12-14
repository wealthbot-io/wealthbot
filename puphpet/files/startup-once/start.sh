sudo update-alternatives --set php /usr/bin/php5.6
service mongod start
cd /var/www/wealthbot
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

