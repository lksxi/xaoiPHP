
# xaoiPHP

## 简述

**xaoiPHP**是一款简单的PHP单文件 MVC框架，开始目的是一个单纯的面向对象环境，现在集成了常用的函数+数据库(PDO,mongodb)+模板引擎，详细介绍请参考：https://lksxi.github.io/xaoiPHP/

要求：

* PHP 5.4.0+

## 目录说明

```
project			根目录
├─app			应用目录
│  ├─home		模块目录
│  │ ├─code		控制器目录
│  │ ├─view		视图目录
├─static		静态文件目录
├─.htaccess		Apache伪静态文件
├─index.php		入口文件
```

## 使用

### 1.修改数据库配置

修改配置函数C ，使之与自己的数据库匹配

### 2.配置Nginx伪静态

#### lnmp配置
```
include enable-php.conf;
或
include enable-php-pathinfo.conf;

替换为↓

location / {
	if (!-e $request_filename) {
		rewrite ^/(.*)$ /index.php/$1;
	}
}

location ~ .*\.php
{
    #try_files $uri =404;
    fastcgi_pass  unix:/tmp/php-cgi.sock;
    fastcgi_index index.php;
    include fastcgi.conf;
    include pathinfo.conf;
}
```

### 3.测试访问

然后访问站点域名：http://localhost/ 就可以了。
