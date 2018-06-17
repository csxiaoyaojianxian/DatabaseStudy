# mysql 配置总结

[TOC]

## 1. Linux-Ubuntu 下 mysql 安装与配置

### 1.1 mysql安装

```shell
sudo apt-get update
sudo apt-get install mysql-server mysql-client
sudo netstat -tap | grep mysql # 检查MySQL服务器是否正在运行
sudo /etc/init.d/mysql restart # 如果不能正常运行，手动重启
sudo service mysql restart # 重启服务
sudo apt-get install libapache2-mod-auth-mysql # 添加Apache对mysql支持
sudo apt-get install php5-mysql # 添加php对mysql支持
```

### 1.2 mysql卸载

```shell
sudo apt-get autoremove --purge mysql-server-5.0
sudo apt-get remove mysql-server
sudo apt-get autoremove mysql-server
sudo apt-get remove mysql-common # 重要
dpkg -l |grep ^rc|awk '{print $2}' |sudo xargs dpkg -P # 清理残留数据
```

### 1.3 开启远程连接

```shell
mysql> grant all PRIVILEGES on *.* to root@'%' identified by '19931128';
mysql> flush privileges;
netstat -anpt|grep 3306 # 检查3306端口
```

当前默认监听 `127.0.0.1:3306`
修改 `127.0.0.1` 为当前ip地址
修改 `/etc/mysql/my.cnf` 文件中 `bind-address`，将 `bind-address=127.0.0.1` 修改为本机IP，重启mysql服务

## 2. Linux-CentOS 下 mysql 安装与配置

### 2.1 安装客户端和服务器端

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

### 2.2 启动、停止设置

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

### 2.3 登录及忘记修改密码

创建root管理员：

```shell
mysqladmin -u root password 19931128
```

登录：

```shell
mysql -u root -p
```

如果忘记密码，则执行以下代码：

```shell
service mysqld stop
mysqld_safe --user=root --skip-grant-tables;
mysql -u root  
use mysql
update user set password=password("19931128") where user="root";  
flush privileges;
```

### 2.4 开启远程连接

```shell
mysql> grant all PRIVILEGES on *.* to root@'%' identified by '19931128';
mysql> flush privileges;
netstat -anpt|grep 3306 # 检查3306端口
```

## 3. macos 下 xampp-mysql 配置

### 3.1 启动服务

```shell
/Applications/XAMPP/xamppfiles/xampp start
```

### 3.2 取消锁定

macos 使用了 Rootlees 对 /usr/bin 操作进行锁定
重启电脑，按住 `command+r`，终端输入

```shell
csrutil disable
reboot
```

### 3.3 链接mysql

```shell
sudo chmod 777 /Applications/XAMPP/bin/mysql
ln -s /applications/xampp/bin/mysql /usr/bin
```

### 3.4 设置密码

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

## 4. 其他问题

### 4.1 编码

**修改MYSQL的配置文件：**

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

**代码运行时修改：**
① Java代码：jdbc:mysql://localhost:3306/test?useUnicode=true&characterEncoding=gbk
② PHP代码：header("Content-Type:text/html;charset=gb2312");
③ C语言代码：int mysql_set_character_set( MYSQL * mysql, char * csname);

### 4.2 开启数据库失败

```
shutdown -h now
mv /var/lib/mysql/mysql.sock /var/lib/mysql/mysql.sock.bak
service mysqld start
```



