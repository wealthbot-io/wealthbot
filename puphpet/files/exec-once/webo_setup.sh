# Force php5 version
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y php5 php5-mongo php5-apcu php5-cli  php5-intl php5-imagick php5-mcrypt php5-curl
sudo curl -sS https://getcomposer.org/installer  -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/bin --filename=composer
sudo apt-get install -y git

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
app/console assetic:dump

#warming up cache
echo .... warming up cache ....
app/console cache:warmup --env=dev
app/console cache:warmup --env=prod
