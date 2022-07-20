# EasyMongo
Mongodb Web管理应用，本项目基于[RockMongo](https://github.com/iwind/rockmongo)，使用最新的mongodb扩展+phplib模式，支持Mongodb3.0+，以及php5.6以上所有版本。


## 广泛的版本支持
MongoDB: Ver3.0及以上版本

PHP: php5.6, php7.x, php8.x


## 丰富的功能
* 基础的增删改查操作
* 索引操作
* 数据库/复制集状态监控
* 数据导入导出
* 查询性能优化

## 安装方法
首先确保已经安装了mongodb扩展，然后执行如下命令：
~~~
git clone https://github.com/pkuoliver/EasyMongo.git
~~~
然后安装phplib
~~~
composer require mongodb/mongodb
~~~
复制配置文件
~~~
cp config.sample.php config.php
~~~
修改相关配置，就可以开始使用啦。

## 致谢
[Liu Xiaochao/iwind](https://github.com/iwind)

[lxpmoon](https://github.com/lxpmoon)