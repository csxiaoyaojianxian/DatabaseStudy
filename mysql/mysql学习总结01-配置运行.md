# mysql学习总结01 — 配置运行

[TOC]

## 1. mysql 安装与配置

### 1.1 Linux-Ubuntu

#### mysql安装

```shell
sudo apt-get update
sudo apt-get install mysql-server mysql-client
sudo netstat -tap | grep mysql # 检查MySQL服务器是否正在运行
sudo /etc/init.d/mysql restart # 如果不能正常运行，手动重启
sudo service mysql restart # 重启服务
sudo apt-get install libapache2-mod-auth-mysql # 添加Apache对mysql支持
sudo apt-get install php5-mysql # 添加php对mysql支持
```

#### mysql卸载

```shell
sudo apt-get autoremove --purge mysql-server-5.0
sudo apt-get remove mysql-server
sudo apt-get autoremove mysql-server
sudo apt-get remove mysql-common # 重要
dpkg -l |grep ^rc|awk '{print $2}' |sudo xargs dpkg -P # 清理残留数据
```

#### 开启远程连接

详见权限管理

```shell
mysql> grant all PRIVILEGES on *.* to root@'%' identified by '19931128';
mysql> flush privileges;
netstat -anpt|grep 3306 # 检查3306端口
```

当前默认监听 `127.0.0.1:3306`
修改 `127.0.0.1` 为当前ip地址
修改 `/etc/mysql/my.cnf` 文件中 `bind-address`，将 `bind-address=127.0.0.1` 修改为本机IP，重启mysql服务

### 1.2 Linux-CentOS

#### 安装客户端和服务器端

确认mysql是否已安装：

```Shell
yum list installed mysql*
rpm -qa | grep mysql*
```

查看是否有安装包：

```shell
yum list mysql*
```

安装mysql客户端：

```shell
yum install mysql
```

安装mysql 服务器端：

```shell
yum install mysql-server
yum install mysql-devel
```

#### 启动、停止设置

数据库字符集设置：

mysql配置文件 `/etc/my.cnf `中加入 `default-character-set=utf8`

启动mysql服务：

```shell
service mysqld start
```

或 `/etc/init.d/mysqld start`

设置开机启动：

```shell
chkconfig --add mysqld
```

查看开机启动设置是否成功：

```shell
chkconfig --list | grep mysql*
```

停止mysql服务：

```shell
service mysqld stop
```

### 1.3 macos 下 xampp-mysql 配置

#### 启动服务

```shell
/Applications/XAMPP/xamppfiles/xampp start
```

#### 取消锁定

macos 使用了 Rootlees 对 /usr/bin 操作进行锁定
重启电脑，按住 `command+r`，终端输入

```shell
csrutil disable
reboot
```

#### 链接mysql

```shell
sudo chmod 777 /Applications/XAMPP/bin/mysql
ln -s /applications/xampp/bin/mysql /usr/bin
```

#### 设置密码

**方法1：**

> 格式：mysqladmin -u用户名 -p旧密码 password 新密码

注意：此处-p默认为空，可省略

**方法2：**

```sql
mysql -u root
mysql> use mysql;
mysql> UPDATE user SET Password = PASSWORD('newpass') WHERE user = 'root';
mysql> FLUSH PRIVILEGES;
```

**方法3：**

```sql
SET PASSWORD FOR 'root'@'localhost' = PASSWORD('newpass’);
```

### 1.4 windows下mysql的使用

mysql是C/S结构，分服务端(mysqld)和客户端(mysql)

```shell
# windows服务端
$ net start mysql
$ net stop mysql
```

## 2. 连接mysql

> 格式： mysql -h主机地址 -P端口 -u用户名 －p用户密码

**1. 连接本机mysql**
终端进入目录 `mysql/bin`

```sql
mysql -u root -p
```

注意：用户名前可有空格也可没有空格，密码前必须没有空格
**2. 连接到远程主机mysql**

```sql
mysql -h 192.168.0.1 -u root -p19931128;
```

**3. 退出mysql**

```sql
mysql> exit;
# 或quit
mysql> \q;
```

## 3. SQL数据备份与还原

mysql中提供了专门用于备份SQL的客户端：mysqldump

SQL备份需要备份结构，因此产生的备份文件特别大，不适合特大型数据备份，也不适合数据变换频繁型数据库备份。

三种备份形式：

1. 整库备份（只需提供数据库名）
2. 单表备份
3. 多表备份：数据库后跟多张表

> 基本语法：
>
> mysqldump -hPup 数据库名 [表1   [表2…]]  >  备份文件地址.sql
>
> mysqldump -hPup -d 数据库名 > 备份文件地址.sql

```
$ mysqldump -hlocalhost -P3306 -uroot -proot dbDatabase > /home/ubuntu/backup/dbbackup.sql
$ mysqldump -hlocalhost -P3306 -uroot -proot dbDatabase tbStudent tbClass> /home/ubuntu/backup/dbbackup.sql
# 注意：命令行下执行，导出文件默认是存在 /usr/local/mysql/bin/ 目录下
$ mysqldump -u root -p -d –add-drop-table database_name > outfile_name.sql
$ mysqldump -u root -p -d database_name > outfile_name.sql
$ mysqldump -uroot -p –default-character-set=latin1 –set-charset=gbk –skip-opt database_name > outfile_name.sql
# 注意：-d 如果没有数据，–add-drop-table 在每个create语句前增加一个drop table
```

mysqldump备份的数据中没有关于数据库本身的操作，都是针对表级别的操作，当进行数据（SQL还原），必须指定数据库

两种还原形式：

**1. 使用 mysql 客户端**

> 基本语法：mysql –hPup 数据库 < 文件位置

**2. 使用导入数据的SQL指令 (必须先进入到对应的数据库)**

> 基本语法：source  SQL文件位置;

**3. 复制SQL指令在mysql客户端中粘贴执行（不推荐）**

```
$ mysql –uroot -proot dbTest < /home/ubuntu/backup/dbbackup.sql
mysql> set names utf8; # 设置数据库编码
mysql> source /home/ubuntu/backup/dbbackup.sql;
```

## 4. 其他问题

### 4.1 符号 ` 的使用

使用 "`" 避免和 mysql 关键字冲突，通常用来指明内容为数据库名、表名、字段名

### 4.2 字符集编码问题

```
mysql> show variables like 'character_set_%';
mysql> set names utf8;
```

客户端传入数据给服务端：client:  character_set_client

服务端返回数据给客户端：server:  character_set_results

客户端与服务端之间的连接：connection:  character_set_connection

set names 字符集统一了三层的字符集

**代码运行时修改：**
① Java代码：jdbc:mysql://localhost:3306/test?useUnicode=true&characterEncoding=gbk
② PHP代码：header("Content-Type:text/html;charset=gb2312");
③ C语言代码：int mysql_set_character_set( MYSQL * mysql, char * csname);

### 4.3 编码相关配置文件

`my.ini` 中修改 `default-character-set=gbk`

拷贝 `/usr/local/mysql/support-files/my-medim.cnf` 到 `/etc` 目录下

[client]

```
port        = 3306
socket      = /tmp/mysql.sock
default-character-set   = utf8
```

[mysqld]

```
default-storage-engine=INNODB
character-set-server=utf8
collation-server=utf8_general_ci
```



![](http://www.csxiaoyao.com/src/img/sign.jpg)

