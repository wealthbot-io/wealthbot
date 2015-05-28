#disable default vhosts
echo disable default vhost 80
sudo a2dissite 10-default_vhost_80.conf
echo disable default vhost 443
sudo a2dissite 10-default_vhost_443.conf

#reload apache
sudo service apache2 reload

#update dependencies
cd /srv/wealthbot
composer update

#create mongo user webo
mongo < mongo_user.js

#create mongo user for unit tests
mongo < mongo_user_test.js

#setup db and load fixtures
php app/console doctrine:database:create
php app/console doctrine:schema:create
app/console doctrine:fixtures:load

