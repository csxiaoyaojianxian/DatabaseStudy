# mysql学习总结09 — 优化(架构层)-主从复制与读写分离

[TOC]

> Write By CS逍遥剑仙
> 我的主页: [www.csxiaoyao.com](http://www.csxiaoyao.com/)
> GitHub: [github.com/csxiaoyaojianxian](https://github.com/csxiaoyaojianxian)
> Email: sunjianfeng@csxiaoyao.com
> QQ: [1724338257](https://www.csxiaoyao.cn/blog/index.php/2018/09/18/02-2/wpa.qq.com/msgrd?uin=1724338257&site=qq&menu=yes)

架构层的优化大致有：分布式部署(集群)(主从复制、读写分离)

## 1. 主从复制

### 1.1 概述

数据库服务器压力增大，增加多台mysql数据库服务器，需要建立主从复制机制保证数据一致同步。

主服务器写，从服务器读，从服务器去主服务器复制/同步数据

主从复制适用范围：

(1) 主从复制后，可以用作后面业务的一个读写分离需求

(2) 从服务器作为主服务器的备份服务器

php业务实现读写分离

写读比例1/7，一般一个写服务器，多个读从服务器

### 1.2 主服务器配置

主服务器 server-id:1

**Step1: 修改配置文件**

主从复制会根据日志记录的位置来进行同步，my-bin.log

```
$ vim /etc/my.conf
log-bin=mysql-bin #开启二进制日志文件
binlog_format=mixed #日志文件存储方式
server-id=1 #服务器识别id 
# 启动mysql服务
$ /usr/local/mysql/bin/mysqld_safe -user=mysql &
```

**Step2: 创建同步账号**

```
# 登录
$ /usr/local/mysql/bin/mysql -uroot -p
# 在主服务器创建同步账号 slave(123456) 以便从服务器同步主服务器数据
mysql> grant replication slave on *.* to 'slave'@'%' identified by '123456';
mysql> flush privileges;
```

**Step3: 创建远程登录账号**

一般root用户权限太高，只设置为允许本地登录，所以再创建一个用户进行远程登录

```
mysql> grant all on *.* to 'api'@'%' identified by '123456';
mysql> flush privileges;
```

**Step4: 在主服务器查看日志文件名称和记录位置**

用于从服务器配置

```
mysql> show master status;
```

### 1.3 从服务器配置

从服务器 server-id:2

**Step1: 修改配置文件**

```
$ vim /etc/my.conf
log-bin=mysql-bin #开启二进制日志文件
binlog_format=mixed #日志文件存储方式
server-id=2 #服务器识别id
# 启动mysql服务
$ /usr/local/mysql/bin/mysqld_safe -user=mysql &
```

**Step2: 创建用户**

```
# 登录
$ /usr/local/mysql/bin/mysql -uroot -p
mysql> grant all on *.* to 'api'@'%' identified by '123456';
mysql> flush privileges;
```

**Step3: 配置slave服务**

master_log_file 和 master_log_pos 在主服务器通过 show master status 查看

```
# 在从服务器将slave同步服务关闭
mysql> slave stop;
# 配置
mysql> change master to master_host="192.168.0.1", master_user="slave", master_password="123456", master_log_file="mysql-bin.000001", master_log_pos=100;
# 启动
mysql> slave start;
```

**Step4: 在从服务器查看当前从服务器状态**

```
mysql> show slave status\G;
```

如果看到 `Slave_IO_Running: Yes` 和 `Slave_SQL_Running: Yes` 说明配置成功

在主服务器进行写操作，查看从服务器是否更新数据判断实际是否配置成功

## 2. 读写分离

```
$con_write = mysql_connect('192.168.0.1','api','123456');
$con_read = mysql_connect('192.168.0.2','api','123456');
```

然后在SQL中判断读写即可。

![](https://raw.githubusercontent.com/csxiaoyaojianxian/ImageHosting/master/img/sign.jpg)