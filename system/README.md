# System framework for handling PAS and Rebalancer operations. #

#### Installation ####
In 'system' directory:

1. install PHPUnit and Monolog run: _composer install_ (assuming you have already setup [composer](http://getcomposer.org/)).
2. Change Config.php.init to Config.php (supply appropriate values)
3. run sample unit test: _vendor/bin/phpunit --colors Test/Case/CalculatorTest.php_

(Make sure you are on the "develop" branch)

Add cron jobs to automatically run data import:  
> 35 6 \* \* \* /var/www/prod/app/system/load_data.sh  
> 50 6 \* \* \* /var/www/staging/app/system/load_data.sh  
> 10 7 \* \* \* /var/www/dev/app/system/load_data.sh  
> 0-59 \* \* \* \* bash wealthbot-core/system/Console/fakeRebalance start  

#### Files and folders used by system scripts: ####
*/mnt/windows_downloads/* is a Windows folder, mounted to the Linux server using Samba filesystem. TD Ameritrade Download Scheduler on Windows server stores Zip 
archives received from custodian into this folder.  
*/mnt/tmp/* is a folder used by *PackManager.php* for storing its temporary files.  
*system/incoming_files/* is a folder in which script *PackManager.php* stores unpacked and sorted files for following processing by *parser.php*.  

Bash script *load_data.sh* consequentially starts the following php scripts situated in the same folder "system":
PackManager.php. This php script unpacks files, received from custodian, and stores them into different folders sorted by type. 
parser.php. This php script imports data from data files received from custodian into Mongo database. 
normalizer.php. This php script imports data from Mongo database into MySQL database. 

#### Custodian archives. ####
Custodian TD Ameritrade has the following archive naming format:  
W{CODE}{CUSTODIAN}{MM}{DD}.EXE  
Where:  
W is a letter W. TD Ameritrade archives are always start from this letter.  
{CODE} is a three-letter Advisor code. Advisors can set their codes in RIA Admin Settings -> Custodians to receive data from custodian.  
{CUSTODIAN} is short two-letter Custodian code (TD for TD Ameritrade).  
{MM} is two-digit month for which archive has been generated.  
{DD} is two-digit day for which archive has been generated.  
.EXE is file extension indicating file type. TD Ameritrade archives can have either extension .ZIP (indicating that this file is a Zip-archive) or .EXE (indicating 
self-extracting Zip archive) 
 
#### Config values. ####
File *system/Config.php.init* is a sample for config file.  
All scripts in the folder *system* get config values from the file named *Config.php*.  

$DEBUG  
Debug mode. When debug mode is enabled, scripts are output additional debugging information. This config setting can be one of the following values:  
0 - Disabled  
\>=1 - Enabled  

$PROD_MODE  
Working mode. Can be one of the following constants:  
ENV_PROD - Production mode. This working mode is used on production server.
ENV_DEV - Development mode. This working mode is deprecated. It is not used by any scripts at all.  
ENV_TEST - Test mode. This mode is used internally in automatic PHPUnit tests.  

Config values for MySQL connection:  
$MYSQL_HOST - host name using for MySQL connection (for example, 'localhost')  
$MYSQL_PORT - port using for MySQL connection (for example, 3306)  
$MYSQL_USERNAME - user name using for MySQL connection (for example, 'root')  
$MYSQL_PASSWORD - password using for MySQL connection (for example, '1234')  
$MYSQL_DATABASE - connection database (for example, 'advisor');  

The following config values are using during automatic tests for connection to test database:  
$MYSQL_USERNAME_TEST - user name using for test MySQL connection (for example, 'advisor_test')  
$MYSQL_PASSWORD_TEST - password for test MySQL connection (for example, 'advisor_test')  
$MYSQL_DATABASE_TEST - test database (for example, 'advisor_test');  
Testing scripts use the same host and port values as for usual MySQL connection.  

Config values for connection to Mongo database:  
$MONGODB_HOST - host name for Mongo database connection (for example, 'localhost')  
$MONGODB_USERNAME - user name for Mongo database connection (for example, root)  
$MONGODB_PASSWORD - password for Mongo database connection (for example, 'root');  
$MONGODB_PORT - port for Mongo database connection (for example, 27017);  
$MONGODB_DATABASE - name of used Mongo database (for example, 'pas');  

The following config values are used by PackManager.php for unpacking archives received from custodian.  
$CUSTODIAN_NAMES - array of short custodian names. These short custodian names are used for archive name generation (['TD', 'TDA'])  
$ARCHIVE_SOURCE_PATH - this config values sets the directory where PackManager.php will get archives for extraction ('/mnt/windows_downloads')  
$ARCHIVE_UNPACK_PATH - this config value sets the directory where PackManager.php will put extracted temporary files ("/mnt/tmp/")  
$UNPACK_COMMAND - archive program used for extracting files received from custodian ("unzip")  
$UNPACK_DESTINATION_COMMAND - program option used to specify extraction directory (for Linux command line unzip program this option is "-d")