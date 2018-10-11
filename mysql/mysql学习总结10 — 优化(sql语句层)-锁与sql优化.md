# mysql学习总结10 — 优化(sql语句层)-锁与sql优化

[TOC]

> Write By CS逍遥剑仙
> 我的主页: [www.csxiaoyao.com](http://www.csxiaoyao.com/)
> GitHub: [github.com/csxiaoyaojianxian](https://github.com/csxiaoyaojianxian)
> Email: sunjianfeng@csxiaoyao.com
> QQ: [1724338257](https://www.csxiaoyao.cn/blog/index.php/2018/09/18/02-2/wpa.qq.com/msgrd?uin=1724338257&site=qq&menu=yes)

sql语句层的优化主要包括锁的使用、慢查询的定位、limit分页优化

## 1. 锁机制

### 1.1 概念

**读锁：**共享锁S-lock，读操作时添加，所有用户(包括当前用户)只可读不可写

**写锁：**独占锁/排他锁X-lock，写操作时添加，其他用户不能读写

**表级锁：**开销小，加锁快，冲突率高，并发低

**行级锁：**开销大，加锁慢，冲突率低，并发高

myisam只支持表锁，innodb支持表锁和行锁。锁机制消耗性能，容易发生阻塞，拖慢网站速度。

### 1.2 表锁

```
# 添加锁，当前用户添加表的锁定后只能操作锁定的表，不能操作未锁定的表
# lock table table_1 read|write, table2 read|write
# 添加读锁
mysql> lock table user read; -- 添加读锁，所有用户只读不写
# 释放锁
mysql> unlock tables;
# 添加写锁
mysql> lock table user write; -- 添加写锁，其他用户不可读
# 释放锁
mysql> unlock tables;
```

### 1.3 行锁

```
# innodb支持行锁，myisam不支持
mysql> alter table user engine innodb;
# begin;
# 执行语句...
# commit;
mysql> begin;
mysql> update user set name="sun" where id=1; -- 只锁定了id=1的行
mysql> commit;
```

## 2. 慢查询定位

### 2.1 临时启动慢查询日志

默认未开启

```
$ mysqld --safe-mode -slow-query-log
# 慢查询阈值默认10秒，修改为1秒
mysql> show variables like 'long_query_time';
mysql> set long_query_time=1;
```

### 2.2 修改配置文件启动慢查询日志

修改配置文件 my.ini 

```
log-slow-queries="/xxx/slow-log"
long_query_time = 1
```

### 2.3 精确记录查询时间

**(1) 开启profile机制**

精确到小数点后8位

```
mysql> set profiling = 1;
```

**(2) 查询时间**

```
mysql> show profiles;
```

**(3) 不需要分析时关闭profile**

```
mysql> set profiling = 0;
```

## 3. limit分页优化

limit offset, N; 当offset非常大时，效率极低，表现为翻页越翻越慢，因为mysql的操作非跳过offset行，单独取N行，而是取offset+N行，舍弃前offset行，返回N行。此外，mysql中获取的行数较多，达到整表记录的一定比例时会默认不使用索引。

**预处理：**(1) 设置主键索引  (2) 关闭查询缓存

**分页算法：**

limit (page-1)*length, length;

**优化方法：**

1. 业务上解决：不允许翻过100页

2. 不用offset，用条件查询

   通过 `where + id>offset + order + limit`取代`order + limit`

   因为查询的数据量较少，所以能够使用索引，所以快。

![](https://raw.githubusercontent.com/csxiaoyaojianxian/ImageHosting/master/img/sign.jpg)