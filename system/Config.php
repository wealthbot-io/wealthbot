<?php
/**
 * This is the Main Config file having global configurations
 * COPY THIS FILE AS "Config.php" IN THE SAME FOLDER (do not remove original)
 *
 */
class Config {
	static public $DEBUG = 0; // 0 - Disabled, >=1 - Enabled

	static public $PROD_MODE = 0; // 1 - Prod , 0 - Develop

	const ENV_PROD = 0;
    const ENV_DEV =  1;
    const ENV_TEST = 2;

	static public $MYSQL_HOST = 'localhost';
	static public $MYSQL_USERNAME = 'root';
	static public $MYSQL_PASSWORD = 'password';
	static public $MYSQL_PORT = 3306;
	static public $MYSQL_DATABASE = 'advisor';
	static public $MYSQL_DATABASE_TEST = 'advisor_test';

	static public $MONGODB_HOST = 'localhost';
	static public $MONGODB_USERNAME = '';
	static public $MONGODB_PASSWORD = '';
	static public $MONGODB_PORT = 27017;
	static public $MONGODB_DATABASE = 'pas';
}
