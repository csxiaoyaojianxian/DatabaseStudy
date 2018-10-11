# mysql学习总结07 — 优化(存储层)-存储引擎与缓存

[TOC]

> Write By CS逍遥剑仙
> 我的主页: [www.csxiaoyao.com](http://www.csxiaoyao.com/)
> GitHub: [github.com/csxiaoyaojianxian](https://github.com/csxiaoyaojianxian)
> Email: sunjianfeng@csxiaoyao.com
> QQ: [1724338257](https://www.csxiaoyao.cn/blog/index.php/2018/09/18/02-2/wpa.qq.com/msgrd?uin=1724338257&site=qq&menu=yes)

## 1. mysql优化方向概述

mysql作为最流行的数据库，在开发过程中仍然有较多优化的空间，mysql的优化主要有4个方向：

1. **存储层：**数据表存储引擎选取、字段类型选取、查询缓存、3范式、数据碎片维护
2. **设计层：**索引、分区、分表
3. **架构层：**分布式部署(集群)(主从复制、读写分离)
4. **sql语句层：**锁的使用、慢查询的定位、limit分页优化

## 2. 存储引擎选择

### 2.1 三种存储引擎特点概述

1. Myisam：表锁，全文索引
2. Innodb：行(记录)锁，事务(回滚)，外键
3. Memory：内存存储引擎，速度快、数据容易丢失

### 2.2 innodb
**(1) 存储格式**

表结构存储于* .frm文件中。

默认所有表的数据/索引存储在同一个表空间文件中。


可以通过配置将不同表的数据/索引单独存储在*.ibd中，方便管理。

```
# 创建表
mysql> create table t1(id int, name varchar(32))engine innodb charset utf8;
mysql> show variables like 'innodb_file_per_table%';
mysql> set global innodb_file_per_table=1;
```
注意：innodb不能直接通过文件的复制粘贴进行备份还原，备份还原需要使用mysqldump。

**(2) 存储顺序: 主键顺序**

数据按照主键顺序存储，写入顺序与存储顺序不同，因此速度比Myisam稍慢。

**(3) 并发处理**

擅长并发处理，支持行级锁和表级锁。

### 2.3 MyISAM

**(1) 存储格式**

mysql5.5以下默认存储引擎。

结构、数据、索引分别存储于frm、MYD、MYI文件中，支持直接通过文件复制粘贴进行备份还原。

**(2) 存储顺序: 插入顺序**

写入速度快

**(3) 并发处理**

不如innodb，只支持表级锁

**(4) 压缩性**

对于不频繁发生变化的数据，可以进行压缩，压缩后只读，写操作需要先解压

### 2.4 Memory

内存存储引擎，速度快、数据容易丢失，可以用作缓存。

### 2.5 innodb & myisam 的适用场景

myisam：写入快，适合写入、读取操作多的系统，如微博。表锁，全文索引。

innodb：适合业务逻辑强、修改操作多的系统，如商城、办公系统。行(记录)锁，事务(回滚)，外键。

## 3. 查询缓存

### 3.1 使用方法

mysql服务器提供的用于缓存select语句结果的一种内部内存缓存系统。

```
mysql> show variables like 'query_cache%';
# query_cache_size: 缓存空间大小
# query_cache_type: 是否开启缓存
# 在 my.ini 中开启缓存，设置缓存空间为128M
query_cache_type=1
query_cache_size=134217728
# 重启mysql
```

### 3.2 缓存失效

数据表的数据发生变化(数据修改)或结构改变(字段增删)，则会清空全部的缓存数据，即缓存失效。

### 3.3 不使用缓存情况

sql语句中有变化表达式(时间、随机数等)，则不会使用缓存。

```
mysql> select name, now() from t2 where id=1234; # 不使用缓存
mysql> select * from t2 order by rand() limit 5; # 不使用缓存
```

### 3.4 生成多个缓存

生成缓存的sql语句对"空格"、"大小写"敏感，相同结果的sql语句，由于空格、大小写问题就会分别生成多个缓存。

### 3.5 禁用缓存

```
mysql> select sql_no_cache * from t2 where id=1234;
```

### 3.6 查看缓存空间的使用

```
mysql> show status like 'Qcache%';
```

Cache_free_memory		缓存中空闲内存总量

Qcache_hits				缓存命中次数

Qcache_inserts			缓存失效次数

Qcache_queries_in_cache	当前缓存的数量

## 4. 范式

范式主要分为四类范式，在开发过程中没有特殊情况数据表尽量要设计为第三范式。

**第一范式：**

(1) 表属性(列)具有原子性(不可分割)

(2) 表属性(列)不能重复

**第二范式：**

表中不能存在完全相同的两条记录，通常通过设置主键来实现。

**第三范式：**

表中不能存在冗余数据，列数据不能通过推导得到。

**反三范式：**

有时出于性能考虑，有意违反三范式，适度冗余，提高查询效率，例如存储浏览量。

## 5. 数据碎片与维护

长期数据操作过程中，索引和数据文件产生空洞碎片，会拖慢执行效率，需要修复，修复可以把数据文件重新整理，使之对齐。由于修复十分消耗资源，如果表的update、delete操作频繁，可以按周月修复。

```
# 方式1:
mysql> alter table engine innodb;
# 方式2:
mysql> optimize table <表名>;
```

![](https://raw.githubusercontent.com/csxiaoyaojianxian/ImageHosting/master/img/sign.jpg)