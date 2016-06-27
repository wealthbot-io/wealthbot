# How to upgrade

*Attention! you can use also our most stable version (for example `1.0.0` tag). But to use Symfony 2.8, you need to upgrade to 1.1.0*

## System requirements for version 1.1.0.

##### PHP Version
Please be careful to use **PHP version `5.4` and higher, but not PHP7**.


If you deployed wealthbot.io to a VPS server (amazon aws, digitalocean, etc), check your PHP version: 

SSH to server, then run `php -v` command.

##### PHP Extensions
We use MongoDB and APC cache, and you need to install these 2 extensions (`apc` or `apcu` and `mongo`)

##### Vagrant VirtualBox VM Update
1)  Please update your VirtualBox software to use latest version, and be careful to not install PHP7 instead of PHP5.

2)  In this version, you need to run `vagrant provision` after `vagrant up` command.

## Process
To upgrade a version 1.0.0 install, open the terminal and run the following commands *line-by-line:*

```bash
# Connect to server
ssh you@yourserver.domain
# Change directory to wealthbot root
cd /var/www/wealthbot_root/
# Checkout master branch
git checkout master
# Stash your changes (git stash, optional) , then make Git Pull
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

## Upgrading Forks
If you forked the main repository, you need to merge latest changes to your fork and then run the commands mentioned above.

**Note:** First, update your fork from the origin repository with this git command:
```bash
git remote add upstream https://github.com/wealthbot-io/wealthbot.git
```
For more information, check this github article (https://help.github.com/articles/syncing-a-fork/)

## Testing
Run PHPUnit tests before and after upgrade process and compare the results.


#### Thank you for upgrading wealthbot.io!
