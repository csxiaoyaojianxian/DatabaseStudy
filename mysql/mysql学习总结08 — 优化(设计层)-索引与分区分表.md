# mysql学习总结08 — 优化(设计层)-索引与分区分表

[TOC]

> Write By CS逍遥剑仙
> 我的主页: [www.csxiaoyao.com](http://www.csxiaoyao.com/)
> GitHub: [github.com/csxiaoyaojianxian](https://github.com/csxiaoyaojianxian)
> Email: sunjianfeng@csxiaoyao.com
> QQ: [1724338257](https://www.csxiaoyao.cn/blog/index.php/2018/09/18/02-2/wpa.qq.com/msgrd?uin=1724338257&site=qq&menu=yes)

设计层的优化大致有：索引、分区、分表

## 1. 索引应用

### 1.1 索引类型

**普通索引：**(index) 对关键字没有要求，如果一个索引在多个字段提取关键字，称为**复合索引**

**唯一索引：**(unique key) 关键字不能重复，同时增加唯一约束

**主键索引：**(primary key) 关键字不能重复，且不能为NULL，同时增加主键约束

**全文索引：**(fulltext index) 关键字来源于字段中提取的特别关键词

### 1.2 创建索引

```
# 建表时直接添加索引
mysql> create table t2 (
	->	id int primary key auto_increment, # 主键索引
	->	name varchar(32) not null,
	->	age tinyint not null,
	->	intro text,
	#   primary key (`iId`), # 主键
	->	unique key(name), # 唯一索引
	->	index(age), # 普通索引
	->	fulltext index(intro), # 全文索引
	->	index(name,age) # 复合索引
	-> )engine myisam charset utf8;

# 表创建后添加索引
mysql> alter table t2 add unique key(name), add index(age), add fulltext index(intro), add index(name,age);
```

注意：

1. 表创建后添加索引，表中已有数据要符合唯一/主键约束才能创建成功。
2. auto_increment属性依赖于某个主键/唯一key

### 1.3 查看索引

```
mysql> show create table <表名>;
mysql> show index from <表名>;
mysql> show indexes from <表名>;
mysql> show keys from <表名>;
mysql> desc <表名>;
```

### 1.4 索引删除

主键索引删除

```
# 如果有auto_increment属性，要先去掉该属性再删除
mysql> alter table t2 modify id int unsigned;
# 没有auto_increment属性时
mysql> alter table t2 drop primary key;
```

其他索引删除

```
# 如果没有指定索引名可以通过查看索引获取
mysql> alter table <表名> drop index <索引名>;
```

### 1.5 创建索引的场合

1. 频繁作为查询条件的字段应该创建索引，如学生学号
2. 唯一性不强的字段不适合单独创建索引，即使频繁作为查询条件，如性别
3. 更新频繁的字段不适合创建索引，如登录次数
4. 不会出现在where子句中的字段不应该创建索引

### 1.6 执行计划分析

通过执行计划可以分析sql的执行效率

```
mysql> explain select * from t2 where id=1\G
```

### 1.7 索引数据结构

#### 1.7.1 myisam引擎

BTREE，索引的节点中存储数据的物理地址(磁道和扇区)。

查找时，找到索引后根据索引节点中的物理地址查找具体数据内容。

索引和数据分开存储。

#### 1.7.2 innodb引擎

1. 主键索引：索引文件中不仅存储主键值，还直接存储行数据，称为**聚簇索引**。
2. 非主键索引：索引中存储主键id (指向对主键的引用)，而myisam的主键/非主键索引都指向物理地址。
3. 如果没有主键，则 unique key 作为主键；如果没有 unique key，则系统生成内部 rowid 作为主键

例如：通过age创建的索引查询年龄为25岁的人，先根据age建立的索引找到该记录的主键id，再根据主键id通过主键索引找出该条记录。

聚簇索引：优势，根据主键查询条目比较少时，不用回行(数据在主键节点下)；劣势，碰到不规则数据插入时会造成频繁的页分裂。

### 1.8 索引覆盖

如果查询的列恰好是索引的一部分，那么查询只需在索引区进行，不需要到数据区再找数据，速度非常快。负面影响是增加了索引尺寸。

### 1.9 索引使用原则

(1) 列独立

索引列不能作为表达式的一部分，也不能作为函数参数。

```
# ID上有主键索引但没有用到索引的情况
mysql> desc select * from user where id+2=4\G
# 可以使用索引的情况
mysql> desc select * from user where id=4-2\G
mysql> desc select * from user where 4-2=id\G
```

 (2) like查询

模糊匹配，左侧没有通配符可以使用索引，以%开头的like查询不使用索引。

```
# 索引覆盖
mysql> desc select * from user where name like '%cs'\G # 不使用索引
mysql> desc select * from user where name like 'cs%'\G # 使用索引
```

(3) OR运算

参与OR运算的字段都必须拥有索引才能使用使用。

(4) 复合索引

对于创建的多列(复合)索引，只要查询条件使用了最左边的列，索引一般就会被使用。

```
mysql> alter table user add index(name,age);
mysql> select * from user where name = "cs"; # 使用索引
mysql> select * from user where age = 25; # 未使用索引
```

### 1.10 mysql智能选择

如果mysql认为全表扫描不会慢于使用索引，则mysql会选择放弃索引，直接全表扫描。一般当取出的数据量超过表中数据的20%，优化器就不会使用索引，直接全表扫描。

### 1.11 group by 优化

默认情况下，mysql会对 group by col1,col2 进行排序，order by col1,col2，可以通过 `group by null` 禁止排序，优化查询速度。

### 1.12 前缀索引

占据空间更小，运行速度更快。

```
mysql> alter <表名> add key (字段(前n位位数))
```

如何确定位数？

```
mysql> select count(distinct left(id,9)) from t1;
```

### 1.13 全文索引

全文索引把内容中的一些单词(非简单单词)拆分作为索引字段使用，可以解决模糊查询不能使用索引的问题。

```
# 添加全文索引
mysql> alter table t1 add fulltext index(name);
# 使用方法 select * from t1 where match(<字段>) against(<模糊内容>);
# 错误案例 select * from t1 where <字段> like '%<模糊内容>%';
# 使用
mysql> select * from t1 where match(title) against('well');
```

注意：

1. 字段类型必须为 varchar/char/text
2. mysql5.6.4前只有Myisam支持，之后Myisam和innodb都支持
3. 目前只支持英文，中文支持需要使用sphinx
4. 生产活动中不常用，可以通过sphinx代替
5. 全文索引有额外操作，对常用单词不建索引

## 2. 分区技术

### 2.1 分区概念

如果数据表记录非常多，达到上亿条，表活性降低，影响mysql整体性能，可以使用分区技术，把一张表，从逻辑上分成多个区域，便于存储数据。mysql本身支持分区技术。

```
mysql> create table <表名> (
	->	<字段信息>,
	->	<索引>
	-> )<表选项>
	-> partition by <分区算法>(<分区字段>)(
	->	<分区选项>
	-> );
```

分区算法：

条件分区：list(列表), range(范围), hash/key(取模轮询)

### 2.2 list

list: 条件值为一个数据列表

例：职员表 p_list(id, name, store_id)   // store_id为分公司的id

| 区域 | store_id            |
| ---- | ------------------- |
| 北部 | 1, 4, 5, 6, 17, 18  |
| 南部 | 2, 7, 9, 10, 11, 13 |
| 东部 | 3, 12, 19, 20       |
| 西部 | 8, 14, 15, 16       |

实际sql操作

```
mysql> create table p_list(
	->	id int,
	->	name varchar(32),
	->	store_id int
	-> )engine innodb charset utf8
	-> partition by list(store_id)(
	->	partition p_north values in(1,4,5,6,17,18),
	->	partition p_east values in(2,7,9,10,11,13),
	->	partition p_south values in(3,12,19,20),
	->	partition p_west values in(8,14,15,16)
	-> );
mysql> insert into p_list values(12,'csxiaoyao',3); # 存入东部区域
```

查询分区的使用情况，注意：只有where子句含分区字段store_id才能使用分区。

```
mysql> explain partitions select * from p_list where store_id=20\G
```

### 2.3 range

range模式允许将数据划分不同范围，例如按照月份划分若干分区。

```
mysql> create table p_range(
	->	id int,
	->	name varchar(32),
	->	birthday date
	-> )engine myisam charset utf8
	-> partition by range(month(birthday))(
	->	partition p_1 values less than(3),
	->	partition p_2 values less than(6),
	->	partition p_3 values less than(9),
	->	partition p_4 values less than MAXVALUE
	-> );
# 插入数据
mysql> insert into p_range values(1,'csxiaoyao','2018-08-24'),(2,'sunshine','2018-11-28'); # 存入p_3和p_4
```

### 2.4 hash

hash模式通过对表的一个或多个列的hash key计算得到的数值对应的数据区域进行分区。例如可以建立一个对主键进行分区的表。

```
mysql> create table p_hash(
	->	id int,
	->	name varchar(20),
	->	birthday date
	-> )
	-> -- 按生日月份hash值将数据划分到5个区中
	-> partition by hash(month(birthday)) partitions 5;
```

### 2.5 分区管理

#### 2.5.1 删除分区

(1) key/hash类分区删除不会造成数据丢失，删除的分区的数据会重新整合到剩余分区，至少要保留一个分区，可以使用drop table删除整个表。

```
# 求余方式(key/hash)
# alter table <表名> coalesce partition <数量>;
mysql> alter table p_hash coalesce partition 4;
```

(2) range/list类分区删除会造成数据丢失

```
# 范围方式(range/list)
# alter table <表名> drop partition <分表名>;
mysql> alter table p_list drop partition p_north;
```

#### 2.5.2 增加分区

(1) key/hash类增加分区

```
# 求余方式(key/hash)
# alter table <表名> add partition partitions <数量>;
mysql> alter table p_hash add partition partitions 5;
```

(2) range/list类增加分区

```
# 范围方式(range/list)
# alter table <表名> add partition(
#	partition <分区名> values less than (<常量>)
#	或
#	partition <分区名> in (n,n,n)
# );
```

### 2.6 说明

注意：创建分区的字段必须是主键/唯一键或其中的一部分。

## 3. 分表技术

水平分表：把一个表的记录信息存储到分表中。

垂直分表：把一个表的全部字段存储到分表中。

### 3.1 水平分表

物理方式分表，程序需要考虑分表算法，即判断读写的表。

比如，根据id参数来选择对应的表

```
<?php
	$id = $_GET['id'];
	$tableArea = $id % 4;
	$tableName = 'tb_' . $tableArea;
	$sql = "insert into $tableName values( ... )";
	...
```

问题：添加数据时没有id，如何确定待添加的分表名？

解决方案：创建一个独立的数据表flag，专门对记录的id值进行维护，每次插入数据先通过flag表确定id，再使用该id完成计算确定插入的分表，flag表需要定期delete清空。

```
mysql> create table flag(id int primary key auto_increment)engine myisam charset utf8;

<?php
	$sql = "insert into flag values(null)";
	mysql_query($sql);
	$id = mysql_insert_id();
	$area = $id % 4;
	...
```

### 3.2 垂直分表

一个数据表中的不常用字段也会占据一定资源，对整体性能产生影响，可以将不常用的字段存储到另外的辅表中，通过主键关联。

![](https://raw.githubusercontent.com/csxiaoyaojianxian/ImageHosting/master/img/sign.jpg)