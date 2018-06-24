# mysql学习总结05 — 用户权限

[TOC]

## 1. 用户管理

mysql中所有用户信息保存在`mysql`数据库下的`user`表中。在安装mysql时，如果不创建匿名用户，那么默认用户只有`root`超级用户。mysql使用`host`(允许访问的IP或者主机地址)和`user`(用户名)共同组成主键来区分用户。如果`host`为`%`，表示所有用户(客户端)都可访问

### 1.1 创建用户

两种方式：

**1. 直接使用root用户在mysql.user表中插入记录（不推荐）**

```sql
mysql> insert into mysql.user(Host,User,Password) values('localhost','csxiaoyao',password('000000'));
```

**2. 使用创建用户的SQL指令**

> 基本语法：
>
> create user '用户名'@'主机地址' identified by '<明文密码>';

```
mysql> -- 创建user1
mysql> create user 'user1'@'%' identified by '123456';
mysql> -- 查看mysql.user表中是否存在新增用户
mysql> select * from mysql.user;
mysql> -- 简化版创建用户（所有用户可以访问，不需要密码）
mysql> create user user2;
mysql> -- 使用新用户登录
mysql> mysql -uuser2;
```

### 1.2 删除用户

**1. 使用drop user指令**

> 基本语法：
>
> drop user '用户名'@'host';

**2. 使用delete指令**

```
mysql> DELETE FROM mysql.user WHERE user="csxiaoyao" and host="localhost";
mysql> flush privileges;
```

### 1.3 修改密码

> 注意：密码需要使用系统函数 password() 加密处理

**1. 使用修改密码SQL指令**

> 基本语法：
>
> set password for 用户 = password('新明文密码');

```
mysql> set password for 'user1'@'%' = password('19931128');
```

**2. 使用update修改表**

> 基本语法：
>
> update mysql.user set password = password('新明文密码') where user = '用户名' and host= '地址';

```
mysql> update mysql.user set password=password('19931128') where user="csxiaoyao" and host="localhost";
```

**3. 使用mysqladmin**

> 基本语法：
>
> 格式：mysqladmin -u用户名 -p旧密码 password 新密码

```
$ mysqladmin -u root password 931128 -- 初始化数据库root无密码
$ mysqladmin -u root -p931128 password 19931128
```

## 2. 权限管理

mysql中三类权限：

1. 数据权限：增删改查（ select / update / delete / insert ）
2. 结构权限：结构操作（ create / drop ）
3. 管理权限：权限管理（ create user / grant / revoke ）

### 2.1 授予权限：grant

> 基本语法：
>
> grant <权限列表 / all privileges> on <数据库 / * >.<表名 / * > to <用户[@ 登录主机]> [identified by '<密码>'];

**权限列表**：使用`,`分隔，可使用`all privileges`代表全部权限 ( select, insert, update, delete, create, drop, index, alter, grant, references, reload, shutdown, process, file共14个权限,可被all privileges或all代替 )

**数据库.表名**：可以是单表(数据库.表名)，可以是具体某个数据库(数据库. * )，也可以整库( * . * )， `.` 表示赋予用户操作服务器上所有数据库所有表权限

**用户地址**：可以是 `localhost`，也可以是ip地址、机器名、域名，`'%'`表示从任何地址连接

```
mysql> -- 权限修改立即生效，不需要刷新
mysql> grant select,insert on dbTest.tbTest to 'user1'@'%';
mysql> -- user1使用show tables只能看到tbTest一张表
```

需要密码：

```
mysql> grant all PRIVILEGES on *.* to 'csxiaoyao'@'%' identified by '19931128';
mysql> flush privileges;
```

### 2.2 权限回收：revoke

> 基本语法：
>
> revoke <权限列表 / all privileges> on <数据库 / * >.<表名 / * > from 用户;

```
mysql> -- 权限修改立即生效，不需要刷新
mysql> revoke all privileges on dbTest.tbTest from 'user1'@'%';
```

### 2.3 刷新权限：flush

> 基本语法：flush privileges;

```
mysql> flush privileges;
```

### 2.4 密码丢失解决方案

重置root密码(windows)

1. 停止服务
2. 重启服务跳过权限
3. 直接登录
4. 修改root用户的密码：指定 用户名@host
5. 重启服务

```
$ service mysqld stop
$ mysqld –skip-grant-tables
$ mysql
mysql> update mysql.user set password = password('root') where user='root' and host='localhost';
$ flush privileges;
$ service mysqld start

# mysqld_safe --user=root --skip-grant-tables
# mysql -u root
```



![](http://www.csxiaoyao.com/src/img/sign.jpg)