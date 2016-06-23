# How to upgrade

*Attention! you can use also our most stable version (for example `1.0.0` tag). But for latest features, you need to upgrade to latest version*

## System requirements for `latest version`

##### PHP Version
Please be careful to use **PHP version `5.4` and higher, but not PHP 7**.
If you deployed wealthbot.io to vps server (amazon aws, digitalocean, etc), at first be sure, that you are using exact version of PHP. To check, SSH to server, then run `php -v` command.

##### PHP Extensions
We are use MongoDB and APC cache, and you need to install this 2 extensions (`apc` or `apcu` and `mongo`)

##### Vagrant VirtualBox VM Update
1)  Please update your VirtualBox software to use latest version, and be careful to not install php7 instead of php5.

2)  In this version, you need to run `vagrant provision` after first `vagrant up` command.

## Process
*Open terminal and run this commands line-by-line:*

```bash
# Connect to server
ssh you@yourserver.domain
# Change directory to wealthbot root
cd /var/www/welthbot_root/
# Checkout master branch
git checkout master
# Stash your changes, then make Git Pull
git pull
# Install dependencies from composer.lock
composer install
# Clear the cache
php app/console cache:clear -e prod
# Clear the APC cache
php app/console apc:clear -e prod
# Validate the Doctrine database schema
php app/console doctrine:schema:validate -e prod
```
Profit!

After this commands,  ths system will be upgraded. If you dont have a experience with Symfony Framework, **we can help you to upgrade the Webo**. Feel free to contact us via github or e-mail.

## Forks
If you are forked the main repository, you need to merge latest changes to your fork and then run the commands mentioned above.

**Note!** At first, update fork from origin repository with this git command:
```bash
git remote add upstream https://github.com/wealthbot-io/wealthbot.git
```
For more information, check this github article (https://help.github.com/articles/syncing-a-fork/)

## Testing
To be sure, as the software if working correctly run PHPUnit test before and after upgrade process and compare the results.


#### Thank you for upgrading the Webo!
