# mysql 命令完全总结
/*

精心整理关于 mysql 的命令
By CS逍遥剑仙
[www.csxiaoyao.com](http://www.csxiaoyao.com) 

> 数据库环境配置见 `mysql配置总结.md`
>
> 常用SQL用法见文件 `sql代码总结.md`

*/

[TOC]

## 1. 连接mysql
> 格式： mysql -h主机地址 -u用户名 －p用户密码

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
exit
```
## 2. 修改密码
> 格式：mysqladmin -u用户名 -p旧密码 password 新密码

```sql
mysqladmin -u root password 931128 -- 初始化数据库root无密码
mysqladmin -u root -p931128 password 19931128
```
## 3. 用户管理
### 3.1 新建用户

```sql
mysql> insert into mysql.user(Host,User,Password) values('localhost','csxiaoyao',password('000000'));
mysql> update mysql.user set password=password('19931128') where User="csxiaoyao" and Host="localhost";
```

### 3.2 用户权限管理

> 命令：GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER ON 数据库名.* TO 用户名@登录主机 IDENTIFIED BY '密码';

```sql
mysql> grant all PRIVILEGES on *.* to root@'%' identified by '19931128';
mysql> grant all on mydb.* to csxiaoyao@localhost identified by '19931128';
mysql> grant select,update on mydb.* to csxiaoyao@localhost identified by '19931128';
mysql> flush privileges;
```

注意：

1. select, insert, update, delete, create, drop, index, alter, grant, references, reload, shutdown, process, file共14个权限,可被all privileges或者all代替
2. `数据库名称.表名称` 代替为 `.` ，表示赋予用户操作服务器上所有数据库所有表的权限

3. 用户地址可以是 `localhost`，也可以是ip地址、机器名、域名，`'%'`表示从任何地址连接


4. '连接口令' 不能为空

### 3.3 删除用户

```sql
mysql> DELETE FROM mysql.user WHERE User="csxiaoyao" and Host="localhost";
mysql> flush privileges;
```

## 4. 数据库操作

### 4.1 连接数据库
> 命令： **use** <数据库名>;

使用USE语句为当前数据库做标记，不会影响访问其它数据库中的表
```sql
mysql> USE db1;
mysql> SELECT a_name,e_name FROM author,db2.editor WHERE author.editor_id = db2.editor.editor_id;   
```
### 4.2 显示数据库

> 命令：**show databases**;

```sql
mysql> show databases;
```

### 4.3 创建数据库

> 命令：**create database** <数据库名>;
>
> **CREATE DATABASE [IFNOT EXISTS] db_name [CHARSET utf8]**

```sql
mysql> create database sunshine;
```

### 4.4 SELECT操作

> 命令：**select** database();

`mysql` 中 `SELECT` 命令类似于其他编程语言的 `print` 或 `write`，可用来显示字符串、数字、数学表达式的结果等
**显示mysql的版本**

```sql
mysql> select version();
```

**显示当前时间**

```sql
mysql> select now();
```

**显示年月日**

```sql
mysql> SELECT YEAR(CURRENT_DATE);
mysql> SELECT MONTH(CURRENT_DATE);
mysql> SELECT DAYOFMONTH(CURRENT_DATE);
```

**显示字符串**

```sql
mysql> SELECT "sunshine";
```

**当计算器用**

```sql
mysql> select ((4 * 4) / 10 ) + 25; 
```

### 4.5 删除数据库

> 命令：**drop database** <数据库名>;
>
> **DROP DATABASE [IFEXISTS] db_name;**

```sql
mysql> drop database sunshine;
mysql> drop database if exists sunshine;
```

## 5. 表操作

### 5.1 创建数据表

| 字段名      | 数字类型    | 数据宽度  | 是否为空 | 是否主键        | 自动增加           | 默认值  |
| -------- | ------- | ----- | ---- | ----------- | -------------- | ---- |
| id       | int     | 4     | 否    | primary key | auto_increment |      |
| name     | char    | 20    | 否    |             |                |      |
| sex      | int     | 4     | 否    |             |                | 0    |
| address  | varchar | 50    | 是    |             |                | 江苏   |
| birthday | date    |       | 是    |             |                |      |
| degree   | double  | 16, 2 | 是    |             |                |      |
```sql
create table sunshine
(
    id int(4) auto_increment not null primary key,
    name char(20) not null,
    sex int(4) not null default 0,
    address varchar(50) default "江苏",
    birthday date,
    degree double(16,2)
); 
```

注：更多建表操作见附录

### 5.2 表字段操作

**增加字段：**

> 命令：**alter table** <表名> **add**  <字段> <类型> <其他>;
>
> **ALTER TABLE** table_name **ADD** field_name field_type;

```sql
mysql> alter table sunshine add salary int(4) default 0
```

**修改原字段名称及类型：**

> 命令：**ALTER TABLE** table_name **CHANGE** old_field_name new_field_name field_type;

**删除字段：**

```sql
mysql> ALTER TABLE table_name DROP field_name;
```

### 5.3 修改表名

> 命令：**rename table** <原表名> **to** <新表名>;

```sql
mysql> rename table OldTable to NewTable;
```

注意：不能有活动的事务或对锁定的表操作，须有对原表的 `ALTER` 和 `DROP` 权限，和对新表的 `CREATE` 和 `INSERT` 权限

### 5.4 删除数据表

> 命令：**drop table** <表名>;

```sql
mysql> drop table sunshine; -- 普通删除
mysql> DROP TABLE IF EXISTS `sunshine`; -- 安全删除
```

### 5.5 索引操作

**加索引**

> 命令：**alter table** <表名> **add index** <索引名 (字段名1[，字段名2 …])>;

```sql
mysql> alter table sunshine add index name_index1(name);
```

**加主关键字索引**

> 命令：**alter table** <表名> **add primary key** <(字段名)>;

```sql
mysql> alter table sunshine add primary key(id);
```

**加唯一限制条件索引**

> 命令：**alter table** <表名> **add unique** <索引名 (字段名)>;

```sql
mysql> alter table sunshine add unique name_index2(cardnumber);
```

**删除索引**

> 命令：**alter table** <表名> **drop index** <索引名>;

```sql
mysql> alter table sunshine drop index name_index2;
```

### 5.6 显示表的结构定义 

> 命令：DESCRIBE table_name;

## 6. 表数据操作

### 6.1 表插入数据

> 命令：**insert into** <表名 [( <字段名1>[,..<字段名n > ])]> **values** <( 值1 )[, ( 值n )]>;

```sql
mysql> insert into sunshine values(1,'Sun',99.99),(2,'Jian',98.99),(3,'Fent', 97.99);
```

注意：insert into每次只能插入一条记录

### 6.2 查询表数据

**查询所有行**

> 命令：**select** <字段1，字段2，...> **from** < 表名 > **where** < 表达式 >;

```sql
mysql> select * from sunshine;
```

**查询前n行数据 LIMIT**

```sql
mysql> select * from sunshine order by id limit 0,2;
```

### 6.3 删除表数据

```sql
mysql> DELETE FROM sunshine WHERE name='csxiaoyao';
```

### 6.4 修改表数据

> 命令：**update** <表名> **set** <字段> **=** <新值,…> **where** <条件>

```sql
mysql> update sunshine set name='csxiaoyao' where id=1;
```

**单表UPDATE**

> 命令：**UPDATE** `[LOW_PRIORITY][IGNORE]` tbl_name **SET** col_name1=expr1 `[, col_name2=expr2 ...][WHERE where_definition] [ORDER BY …][LIMIT row_count]`

**多表UPDATE**

> 命令：**UPDATE** `[LOW_PRIORITY][IGNORE]` table_references **SET** col_name1=expr1 `[, col_name2=expr2 ...][WHERE where_definition]`

注意：如果指定ORDER BY子句，则按被指定顺序对行更新；LIMIT子句限制被更新行数

## 7. 导入导出数据库

### 7.1 导出整个数据库

> 格式：mysqldump -u用户名 -p密码 数据库名 > 导出文件名.sql

```Sql
mysqldump -u root -p database_name > outfile_name.sql
```

注意：命令行下执行，导出文件默认是存在 `/usr/local/mysql/bin/` 目录下

### 7.2 导出表

> 格式：mysqldump -u用户名 -p密码 数据库名 表名 > 导出文件名.sql

```sql
mysqldump -u root -p database_name table_name > outfile_name.sql
```

### 7.3 导出表结构

> 格式：mysqldump -u用户名 -p密码 -d 数据库名 > 导出文件名.sql

```sql
mysqldump -u root -p -d –add-drop-table database_name > outfile_name.sql
mysqldump -u root -p -d database_name > outfile_name.sql
```

注意：`-d` 如果没有数据，`–add-drop-table` 在每个create语句前增加一个drop table

### 7.4 带语言参数导出

```sql
mysqldump -uroot -p –default-character-set=latin1 –set-charset=gbk –skip-opt database_name > outfile_name.sql
```

### 7.5 导入sql文件

**方法1**

> 格式：mysql -u用户名 -p密码 数据库名 < 导入文件名.sql

```sql
mysql -uroot -p < /Users/sunshine/database.sql;
```

**方法2**

```sql
mysql> set names utf8; # 设置数据库编码
mysql> source /Users/sunshine/database.sql;
```

## 8. 其他问题

### 8.1 符号 ` 的使用

使用 "`" 避免和 mysql 关键字冲突，通常用来指明内容为数据库名、表名、字段名

### 8.2 自增

`mysql` 使用 `auto_increment`，`sqlserver` 使用 `identity(1,1)`

### 8.3 字符集

> SHOW CREATE DATABASE db_name;
>
> ALTER DATABASE db_name DEFAULT CHARACTER SET utf8

## 9. MySQL数据类型

### 9.1 整型浮点型

1. 整型

```
tinyint   	最小型整数  0-255(-128 ~ +127)   1个字节
smallint  	小型整数    0-65535            2个字节
mediumint	中型整数    0-1677万           3个字节
int          一般整数    0-21亿             4个字节
bigint       最大整数    0-42亿              8个字节
```

2. 浮点型

```
float(M,D)	单精度(精确到小数点后7位)    M代表长度，D代表小数位数
 	举例：float(6,2) //总长度为6位，小数位数为2位，小数点不算。存的最大值为9999.99
double(M,D) 双精度(精确到小数点后15位)   M代表长度，D代表小数位数
```

### 9.2 日期时间型

 ```
 date   日期型   格式为： “YYYY-mm-dd”
 time   时间型   格式为： “00:00:00”
 ```

### 9.3 字符和文本型

1. 字符型

```
char(M)	  	0-255	 固定长度的字符串   如：邮编、手机号码、电话号码等
varchar(M) 	0-65535  可变长度的字符串   如：新闻标题、家庭地址、毕业院校等
```

2. 文本型

```
tinytext     0-255      小型文本
Text        0-1670万  中型文本
longtext    0-42亿     大型文本
```

## 10. 常用SQL

### 10.1 串接字符串 CONCAT 与 AS

```sql
mysql> select CONCAT(name, " ", sex) 
     > AS inf 
     > from user
     > where degree > 0; 
```

### 10.2 PRIMARY KEY 

````
DROP TABLE IF EXISTS `sun`;
CREATE TABLE `sun` (
  `sn` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `degree` double(16,2) DEFAULT NULL,
  PRIMARY KEY (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
````
