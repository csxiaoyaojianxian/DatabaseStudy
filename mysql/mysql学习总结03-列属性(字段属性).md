# mysql学习总结03 — 列属性(字段属性)

[TOC]

mysql中的6个列属性：null，default，comment，primary key，unique key，auto_increment

## 1. NULL

代表字段为空。mysql的记录长度为65535字节，如果表中有字段允许为NULL，那么系统会设计保留1个字节来存储NULL，最终有效存储长度为65534字节

```
mysql> create table tbTest (
          name varchar(10) NOT NULL  -- 不能为空
       ) charset utf8;
```

## 2. default 默认值

```
mysql> create table tbTest (
          name varchar(10) NOT NULL
          age int default 18  -- 默认为18	
       ) charset utf8;
mysql> -- 两种方式触发默认值
mysql> insert into tbTest values('csxiaoyao');
mysql> insert into tbTest values('csxiaoyao', default);
```

## 3 comment 列描述

基本语法：comment '字段描述';

```
mysql> create table tbTest (
          name varchar(10) NOT NULL COMMENT '用户名不能为空'
       ) charset utf8;
mysql> -- 查看Comment必须通过查看表创建语句
mysql> show create table tbTest;
```

## 4 primary key 主键

### 4.1 创建主键

#### 随表创建

```
mysql> -- 方法1，给字段增加 primary key 属性
mysql> create table tbTest (
          name varchar(10) primary key
       ) charset utf8;

mysql> -- 方法2，在所有字段之后增加 primary key 选项
mysql> create table tbTest (
          name varchar(10),
          primary key(name)
       ) charset utf8;
```

#### 表后增加

> 基本语法：alter table <表名> add primary key(<字段>);

### 4.2 查看主键

```
mysql> -- 方案1：查看表结构
mysql> desc tbTest;
mysql> -- 方案2：查看建表语句
mysql> show create table tbTest;
```

### 4.3 删除主键

> 基本语法：alter table <表名> drop primary key;

### 4.4 复合主键

```
mysql> create table tbTest (
          student_no char(10),
          course_no char(10),
          score tinyint not null,
          primary key(student_no, course_no)
       ) charset utf8;
```

### 4.5 主键约束

1. 主键数据不能为空
2. 主键数据不能重复

### 4.6 主键分类

业务主键：主键所在的字段，具有业务意义（学生ID，课程ID）

逻辑主键：自然增长的整型（应用广泛）

## 5. unique key 唯一键 

主键也可以用来保证字段数据唯一性，但一张表只有一个主键

1. 唯一键在一张表中可以有多个。
2. 唯一键允许字段数据为NULL，NULL可以有多个（NULL不参与比较）

### 5.1 创建唯一键

#### 随表创建

```
mysql> -- 方法1，给字段增加 unique[ key] 属性
mysql> create table tbTest (
          name varchar(10) unique
       ) charset utf8;

mysql> -- 方法2，在所有字段之后增加 unique key 选项
mysql> create table tbTest (
          name varchar(10),
          unique key(name)
       ) charset utf8;
```

#### 表后增加

> 基本语法：
>
> alter table <表名> add unique key(<字段1[,字段2,...]>);

### 5.2 查看唯一键

```
mysql> -- 方案1：查看表结构
mysql> desc tbTest;
mysql> -- 方案2：查看建表语句
mysql> show create table tbTest;
mysql> -- 系统会为唯一键自动创建一个名字(默认为字段名)

CREATE TABLE `tbTest` (
  `name` varchar(10) NOT NULL,
  PRIMARY KEY (`name`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
```

### 5.3 删除唯一键

> 基本语法：
>
> alter table <表名> drop index < 唯一键名>;

### 5.4 修改唯一键

先删除后增加

### 5.5 复合唯一键

```
mysql> create table tbTest (
          student_no char(10),
          course_no char(10),
          score tinyint not null,
          unique key(student_no, course_no)
       ) charset utf8;
```

## 6. auto_increment 自动增长

通常自动增长用于逻辑主键，只适用于数值，sqlserver中使用`identity(1,1)`

### 6.1 自动增长原理

在系统中维护一组数据保存当前使用自动增长属性的字段，记住当前对应的数据值，再给定一个指定的步长

### 6.2 使用自动增长

```
mysql> create table tbTest2 (
          id int primary key auto_increment,
          name varchar(10)
       ) charset utf8;
```

### 6.3 修改自动增长

**查看自增长**：自增长触发使用后，会自动在表选项中增加一个选项 (一张表最多只能有一个自增长)

```
CREATE TABLE `tbTest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8
```

可以通过修改表结构来修改自动增长

> 基本语法：
>
> alter table <表名> auto_increment = <值>;

### 6.4 删除自动增长

删除自增长：修改自动增长的字段，字段属性之后不再保留 auto_increment 即可

### 6.5 初始设置

在系统中有一组变量维护自增长的初始值和步长

> show variables like ‘auto_increment%’;

### 6.6 细节问题

1. 一张表最多只有一个自增长，自增长会上升到表选项中
2. 如果数据插入没有触发自增长(给定了数据)，那么自增长不会表现，但是会根据当前用户设定的值初始化下一个值，例如当前id=1，插入数据给定id=3，则AUTO_INCREMENT=4
3. 自增长修改时，值可以较大，但不能比当前已有的自增长字段的值小

![](http://www.csxiaoyao.com/src/img/sign.jpg)