# EasyMongo 
EasyMongo is a MongoDB web management application. This project is based on [iwind/RockMongo](https://github.com/iwind/rockmongo), uses the latest [mongodb-extension](https://pecl.php.net/package/mongodb) + [mongo-php-library](https://github.com/mongodb/mongo-php-library) mode, supports MongoDB3.0+, and all php versions above 5.6. 

[中文版说明](./README_CN.md)

## Compatibility
MongoDB: All versions above 3.0

PHP: All versions above 5.6

## Features
* CURD operation very easy
* Add/Remove index
* Database/Replication state monitoring
* Import/Export data
* Performance tuning tool

## Installation
Make sure that the mongodb extension is installed, then execute the following command:
~~~
$ git clone https://github.com/pkuoliver/EasyMongo.git
~~~
Install mongodb php library with [Composer](https://getcomposer.org/)
~~~
$ composer require mongodb/mongodb
~~~
Prepare config file.
~~~
$ cp config.sample.php config.php
~~~
Modify the database host/port and other related configurations to your own information.

## Language support
We support English, Chinese, Japanese, Portuguese, French, Spanish, German, Russian, Italian, Portugal, Turkish.

## UI Preview
![RUNOOB 图标](./screenshots/ss-main.png "Main UI")
For more, please visit [sceenshots folder](./screenshots/)

## Thanks
[Liu Xiaochao/iwind](https://github.com/iwind)

[lxp_kidd/lxphelloworld](https://github.com/lxphelloworld)