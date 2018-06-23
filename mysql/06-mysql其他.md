# mysql-其他

## 1. 用户权限管理

### 1.1 用户管理

mysql中所有用户信息保存在`mysql`数据库下的`user`表中。在安装mysql时，如果不创建匿名用户，那么默认用户只有`root`超级用户。mysql使用`host`(允许访问的IP或者主机地址)和`user`(用户名)共同组成主键来区分用户。如果`host`为`%`，表示所有用户(客户端)都可访问

#### 创建用户

两种方式：

1. 直接使用root用户在mysql.user表中插入记录（不推荐）
2. 使用创建用户的SQL指令

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

#### 删除用户

> 基本语法：
>
> drop user '用户名'@'host';

#### 修改密码

> 注意：密码需要使用系统函数 password() 加密处理

1. 使用修改密码SQL指令

> 基本语法：
>
> set password for 用户 = password('新明文密码');

```
mysql> set password for 'user1'@'%' = password('19931128');
```

2. 使用update修改表

> 基本语法：
>
> update mysql.user set password = password('新明文密码') where user = '用户名' and host= '地址';

### 1.2 权限管理

mysql中三类权限：

1. 数据权限：增删改查（ select / update / delete / insert ）
2. 结构权限：结构操作（ create / drop ）
3. 管理权限：权限管理（ create user / grant / revoke ）

#### 授予权限：grant

> 基本语法：
>
> grant <权限列表 / all privileges> on <数据库 / * >.<表名 / * > to <用户>;

权限列表：使用`,`分隔，可使用`all privileges`代表全部权限

数据库.表名：可以是单表(数据库.表名)，可以是具体某个数据库(数据库. * )，也可以整库( * . * )

```
mysql> -- 权限修改立即生效，不需要刷新
mysql> grant select on dbTest.tbTest to 'user1'@'%';
mysql> -- user1使用show tables只能看到tbTest一张表
```

#### 权限回收：revoke

> 基本语法：
>
> revoke <权限列表 / all privileges> on <数据库 / * >.<表名 / * > from 用户;

```
mysql> -- 权限修改立即生效，不需要刷新
mysql> revoke all privileges on dbTest.tbTest from 'user1'@'%';
```

#### 刷新权限：flush

> 基本语法：flush privileges;

#### 密码丢失解决方案

重置root密码(windows)

1.  停止服务
2. 重启服务跳过权限
3. 直接登录
4. 修改root用户的密码：指定 用户名@host
5. 重启服务

```
$ net stop mysql
$ mysqld –skip-grant-tables
$ mysql
mysql> update mysql.user set password = password('root') where user='root' and host='localhost';
$ net start mysql
```

## 2. 外键

### 2.1 概念

foreign key : 一张表(从表)中有一个字段(外键)，保存的值指向另外一张表(主表)的主键 

### 2.2 外键的操作

#### 增加外键

**方案1**：创建表时增加外键（类似主键）

> 基本语法：
>
> [constraint '<外键名>'] foreign key(<外键字段>) references <主表>(<主键>);

```
mysql> create table tbStudent(
    ->    id int primary key auto_increment,
    ->    name varchar(10) not null,
    ->    class_id int,
    ->    -- 增加外键，创建完后 class_id 对应的 key 为 MUL 多索引，外键本身也是一种普通索引
    ->    foreign key(class_id) references tbClass(id)
    -> )chatset utf8;
```

**方案2**：创建表后增加外键

> 基本语法：
>
> alter table <从表> add [constraint <外键名>] foreign key(<外键字段>) references <主表>(<主键>);

```
mysql> alter table tbStudent add constraint 'stu_class_ibfk_1' foreign key(class_id) references tbClass(id);
```

#### 修改&删除外键

外键不允许修改，只能先删除后增加。外键创建时会自动增加一个普通索引，但删除时仅删除外键不删除索引，如果要删除需要手动删除

> 基本语法：
>
> alter table <从表> drop foreign key <外键名>;
>
> alter table <表名> drop index <索引名>;

```
mysql> alter mysql tbStudent drop foreign key 'stu_class_ibfk_1';
```

### 2.3 外键基本要求

1. 外键字段与主表主键字段类型完全一致
2. 外键字段与主表主键字段基本属性相同
3. 如果是在表后增加外键，对数据有要求(从表数据与主表的关联关系)
4. 外键只能使用innodb存储引擎，myisam不支持

### 2.4 外键约束概念

外键约束主要约束主表操作，从表仅约束不能插入主表不存在的数据，外键约束约束了例如：

1. 从表插入数据，不能插入对应主表主键不存在的数据
2. 主表删除数据，不能删除被从表引入的数据

外键约束保证了数据的完整性(主表与从表数据一致)，外键强大的数据约束作用可能导致数据在后台变化的不可控，所以外键在实际开发中较少使用

### 2.5 外键约束模式

**三种约束模式**：

1. district：严格模式，默认的，不允许操作
2. cascade：级联模式，一起操作，主表变化，从表数据跟随变化
3. set null：置空模式，主表变化（删除），从表对应记录设置为空，前提是从表中对应的外键字段允许为空

 **添加外键约束模式**：

> 基本语法：
>
> add foreign key(<外键字段>) references <主表>(<主键>)  on <约束模式>;

通常在进行约束时候的时候，需要指定操作：`update`和`delete`

常用的约束模式：`on update cascade, on delete set null`，更新级联，删除置空

```
mysql> alter table tbStudent add foreign key(class_id) 
    -> references tbClass(class_id)
    -> on update cascade
    -> on delete set null;
```

## 3. 视图

### 3.1 创建视图

视图的本质是SQL指令（select语句，单表数据/连接查询/联合查询/子查询）

> 基本语法：
>
> create view <视图名> as <select指令>;

```
mysql> create view stu_class_v as
    -> select s.*, c.name from tbStudent as s left join tbClass as c on s.class_id = c.class_id;
```

### 3.2 查看视图结构

视图本身是虚拟表，所以关于表的操作都适用于视图

> 基本语法：
>
> show tables;
>
> show create table[view];
>
> desc <视图名>；

```
mysql> show create view stu_class_v\G;
```

### 3.3 使用视图

视图本身没有数据，是临时执行select语句得到的结果，视图主要用于查询操作

> 基本语法：
>
> select <字段列表> from <视图名> [子句];

```
mysql> select * from stu_class_v;
```

### 3.4 修改视图

修改视图的查询语句

> 基本语法：
>
> alter view <视图名> as <新select指令>;

```
mysql> alter view stu_class_v as
    -> select * from tbStudent as s left join tbClass as c using(class_id);
```

### 3.5 删除视图

> 基本语法：
>
> drop view <视图名>;

```
mysql> drop view stu_class_v;
```

## 4. 事务安全

### 4.1 事务基本原理

事务(transaction)是访问并可能更新数据库中各种数据项的一个程序执行单元(unit)。事务通常由高级数据库操纵语言或编程语言书写的用户程序的执行所引起。事务由事务开始(begin transaction)和事务结束(end transaction)之间执行的全体操作组成

**基本原理**：

mysql允许将事务统一进行管理（存储引擎INNODB），将用户操作暂时保存，不直接更新数据表，等到用户确认结果后再操作

事务在mysql中通常是自动提交的，但也可以使用手动事务

### 4.2 自动事务

自动事务：`autocommit`，当客户端发送一条SQL指令（写操作：增删改）给服务器，服务器执行后，不用等待用户反馈结果，自动将结果同步到数据表。系统是通过变量`autocommit`来控制

```
mysql> show variables like 'autocommit%';
mysql> -- 关闭自动事务，系统不再帮用户提交结果
mysql> set autocommit = Off;
```

自动事务关闭便需要用户提供同步命令

`commit`：提交（同步到数据表，事务被清空）

`rollback`：回滚（清空之前的操作）

```
mysql> xxxx
mysql> commit;
mysql> xxxx
mysql> rollback;
```

执行事务的客户端中，进行数据查看时会利用事务日志中保存的结果对数据进行加工，看到的是修改后的数据，实际还未更改

### 4.3 手动事务

手动事务期间所有语句都不会直接写入到数据表（保存在事务日志中）

手动事务命令：

1. 开启事务：`start transaction;` 
2. 事务处理：多个写指令构成
3. 事务提交：`commit` / `rollback`

```
mysql> start transaction;
mysql> ......
mysql> commit;
```

### 4.4 回滚点

> 增加回滚点：
>
> savepoint <回滚点名>;
>
> 回到回滚点：
>
> rollback to <回滚点名>;

```
mysql> savepoint sp1;
mysql> ......
mysql> rollback to sp1;
```

### 4.5 事务特点

事务4个属性：`原子性（atomicity）`、`一致性（consistency）`、`隔离性（isolation）`、`持久性（durability）`，即`ACID`特性

如果一个客户端在使用事务操作一个数据（一行 / 整表）的时候，另一个客户端不能对该数据进行操作。如果条件中使用了索引（主键），系统根据主键直接找到某条记录，只隔离一条记录；如果系统通过全表检索（没有索引），被检索的所有数据都会被锁定（整表）



