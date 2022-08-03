<?php
/**
 * EasyMongo startup
 *
 * In here we define some default settings and start the configuration files
 * @package rockmongo
 */

/**
* Defining version number and enabling error reporting
*/
define("ROCK_MONGO_VERSION", "0.0.1");

error_reporting(E_ALL);

/**
* Environment detection
*/
if (!version_compare(PHP_VERSION, "5.0")) {
	exit("To make things right, you must install PHP5");
}
if (!class_exists("MongoDB\Driver\Cursor") && !class_exists("MongoDB\Driver\Manager")) {
	exit("To make things right, you must install php_mongodb module. <a href=\"https://www.php.net/manual/en/mongodb.installation.php\" target=\"_blank\">Here for installation documents on PHP.net.</a>");
}

// enforce Mongo support for int64 data type (Kyryl Bilokurov <kyryl.bilokurov@gmail.com>)
if (PHP_INT_SIZE == 8) {
	ini_set("mongo.native_long", 1);
	ini_set("mongo.long_as_object", 1);
}

/**
* Initializing configuration files and EasyMongo
*/
require_once __DIR__ . '/vendor/autoload.php';
require "config.php";
require "rock.php";
rock_check_version();
rock_init_lang();
rock_init_plugins();
Rock::start();

?>
