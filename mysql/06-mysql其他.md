# mysql-SQL编程

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

## 5. 变量和作用域

mysql本质是一种编程语言，需要变量来保存数据。mysql中许多属性控制都是通过mysql中的变量来实现的

> `:=` : mysql中没有比较符号`==`，用`=`作为比较符号，容易与赋值符号混淆，因此增加变量赋值符号 `:=`

### 5.1 系统变量

系统变量针对所有用户（MySQL客户端）有效

#### 查看系统变量

> 基本语法：
>
> show variables [like 'pattern'];   -- 查看系统所有变量
>
> select @@<变量名>;   -- 使用select查询变量值

```
mysql> show variables like 'auto_increment%';
mysql> select @@autocommit;
```

#### 修改系统变量

1. 局部修改（会话级别）：当前客户端当次连接有效

> 基本语法：
>
> set <变量名> := <新值>;

```
mysql> set autocommit;
```

2. 全局修改：所有新客户端都生效（当前连接的客户端无效）

> 基本语法：
>
> set global <变量名> := <新值>;
>
> set @@global.<变量名> := <新值>;

```
mysql> set global autocommit = 0;
mysql> set @@global.auto_increment_increment = 2;
```

### 5.2 会话变量

会话变量（用户变量）跟随mysql客户端绑定，只在当前客户端生效

#### 定义用户变量

> 基本语法：
>
> set @<变量名> := <新值>;

```
mysql> set @name = 'csxiaoyao';
```

mysql允许将数据从表中取出存储到变量中，mysql没有数组，查询的数据只能是一行数据（一个变量对应一个字段值）

> 基本语法：
>
> -- 赋值且查看赋值过程
>
> select @<变量1> := <字段1>, @<变量2> := <字段2> from <数据表> where <条件>;
>
> -- 仅赋值
>
> select <字段1>, <字段2> from <数据源> where <条件> into @<变量1>, @<变量2>;

```
mysql> select @name := stu_name, @age := stu_age from tbStudent limit 1;
mysql> select stu_name, stu_age from tbStudent limit 1 into @name, @age;
```

### 5.3 局部变量

1. 局部变量使用declare关键字声明，语法：`declare <变量名> <数据类型> [<属性>];`
2. 局部变量作用范围在begin到end语句块之间，declare语句出现在begin和end之间，begin / end 在大型语句块(函数/存储过程/触发器)中使用

### 5.4 变量作用域 

**局部作用域**

在结构体内( 函数/存储过程/触发器 )使用`declare`关键字声明，只能在结构体内使用。`declare`关键字声明的变量如果没有修饰符为普通字符串，如果在外部访问该变量，系统会自动认为是字段

**会话作用域**

用户使用set@定义的变量，在当前用户当次连接有效，可以在结构体中使用，也可以跨库

**全局作用域**

所有的客户端的所有的连接都有效

## 6. 流程结构

### 6.1 if分支

两种用途：

1. select查询中的条件判断
2. 复杂语句块中（函数/存储过程/触发器），可嵌套

> 基本语法：
>
> -- 【select查询中】
>
> if( <条件>, <为真结果>, <为假结果>)
>
> -- 【复杂语句块中】
>
> if <条件表达式> then
>
> ​    <满足条件执行语句>
>
> end  if;
>
> if <条件表达式> then
>
> ​    <满足条件执行语句>
>
> else
>
> ​    <不满足条件执行语句>
>
> end  if;

```
mysql> select *, if(stu_age > 20, '符合','不符合') as judge from tbStudent;
```

### 6.2 while循环

循环体在大型代码块中使用

> 基本语法：
>
> while <条件> do
>
> ​    <循环体>
>
> end while;

**结构标识符**: 为结构命名，方便在循环体中进行循环控制。mysql中没有continue和break，使用iterate和leave控制

`iterate`：迭代，重新开始循环（continue）

`leave`：离开，循环终止（break）

> 基本语法：
>
> <标识名>:while <条件> do
>
> ​    if <条件判断> then
>
> ​        iterate/leave <标识名>;
>
> ​    end if;
>
> ​    <循环体>
>
> end while [<标识名>];

## 7. 函数

mysql中函数分两类：系统函数（内置函数）和自定义函数

> 基本语法：
>
> select <函数名>(<参数列表>);

### 7.1 内置函数

#### 字符串函数

`char_length()`：返回字符串的字符数

`length()`：返回字符串的字节数（字符集）

`concat()`：连接字符串

`instr()`：判断字符在目标字符串中是否存在，存在返回其位置，不存在返回0

`lcase()`：字符串转小写

`left()`：字符串截取，从左侧开始到指定位置（位置如果超过长度，截取所有）

`ltrim()`：消除字符串左边的空格

`mid()`：从中间指定位置开始截取，如果不指定截取长度，截取到最后

```
mysql> select char_length('你好'), length('你好'); -- 2 4
mysql> select concat('你好','编程'); -- 你好编程
mysql> select instr('你好编程','编'), instr('你好编程','人'); -- 3 0
mysql> select lcase('aBcD'); -- abcd
mysql> select left('你好编程',2); -- 你好
mysql> select ltrim(' a bcd '); -- a bcd
mysql> select mid('你好编程',2); -- 好编程
```

#### 时间函数

`now()`：返回当前 日期 时间

`curdate()`：返回当前日期

`curtime()`：返回当前时间

`datediff()`：返回两个日期的天数差，参数日期为字符串

`date_add(<日期>,interval <数字> <type>)`：增加时间，type: day/hour/minute/second

`unix_timestamp()`：获取时间戳

`from_unixtime()`：时间戳转日期时间格式

```
mysql> select now(), curdate(), curtime();
mysql> -- 2018-06-24 13:08:57 | 2018-06-24 | 13:08:57
mysql> select datediff('2018-06-24','2018-06-01'); -- 23
mysql> select date_add('2018-06-01', interval 10 second), date_add('2018-06-01', interval 10 day), date_add('2018-06-01', interval 10 year);
mysql> -- 2018-06-01 00:00:10 | 2018-06-11 | 2028-06-01
mysql> select unix_timestamp(); -- 1529817476
mysql> select from_unixtime(1529817476); -- 2018-06-24 13:17:56
```

#### 数学函数

`abs()`：绝对值

`ceiling()`：向上取整

`floor()`：向下取整

`pow()`：求指

`rand()`：获取随机数（0-1）

`round()`：四舍五入

```
mysql> select abs(-1), ceiling(1.1), floor(1.1), pow(2,4), rand(), round(1.5);
mysql> -- 1 2 1 16 0.13695664995997833 2
```

#### 其他函数

`md5()`：md5加密

`version()`：获取版本号

`database()`：显示当前所在数据库

`uuid()`：生成唯一标识符：自增长是单表唯一，UUID是整库唯一（数据唯一且空间唯一）

```
mysql> select md5('sun'), version(), database(), uuid();
```

### 7.2 自定义函数

> 流程：
>
> 1. 定义函数前使用delimiter修改临时语句结束符（非系统内置即可$$）
> 2. 正常SQL指令，分号结尾（系统不执行，不能识别分号）
> 3. 使用新符号结束
> 4. 修改回语句结束符：`delimiter ;`

#### 创建函数

自定义函数包含要素：`function关键字`，`函数名`，`参数（形参和实参[可选]）`，`函数返回值类型`，`函数体`，`返回值`

```
mysql> -- 修改语句结束符
mysql> delimiter $$
mysql> -- create function 函数名(形参 数据类型) returns 返回值类型
    -> create function func_test1() returns int
    -> begin
    ->     -- 函数体
    ->     return 10;
    -> end
    -> $$
mysql> delimiter ;
```

如果函数体只有一条指令（return），可以省略`begin`和`end`

```
mysql> create function func_test2(param1 int, param2 int) returns int
    -> return param1 + param2;
```

#### 查看函数

**通过查看function状态查看所有函数**

> show function status [like 'pattern'];

```
mysql> show function status\G
```

**查看函数的创建语句**

```
mysql> show create function func_test\G
```

#### 调用函数

自定义函数调用与内置函数调用相同

```
mysql> select func_test1(), func_test2(100,200);
```

#### 删除函数

```
mysql> drop function func_test1;
```

#### 注意事项

1. 自定义函数属于用户级别，只有当前客户端对应的数据库中可以使用，不同的数据库下能看到函数但不可以调用
2. 自定义函数通常是为了将多行代码集合到一起解决一个重复性问题
3. 函数必须规范返回值，函数内部不能使用select指令，因为select执行会得到一个结果（result set），唯一可用的select是 `select <字段> into @<变量>;`

### 7.3 函数流程结构案例

实现从1开始累加到用户传入的值为止，且去除5的倍数

> 声明局部变量必须在函数体其他语句前
>
> declare <变量名> <类型> [= <默认值>];
>
> 函数体中可以使用会话变量
>
> return @name;

```
mysql> delimiter $$
mysql> create function my_sum(end_value int) returns int
    -> begin
    ->     declare res int default 0;
    ->     declare i int default 1;
    ->     mywhile:while i <= end_value do
    ->         if i % 5 = 0 then
    ->             set i = i + 1;
    ->             iterate mywhile;
    ->         end if;
    ->         set res = res + i;
    ->         set i = i + 1;
    ->         end while mywhile;
    ->     return res;
    -> end
    -> $$
mysql> delimiter ;
mysql> select my_sum(10);
```

## 8. 存储过程

### 8.1 概念

存储过程（Stored Procedure）是在大型数据库系统中，一组为了完成特定功能的 SQL 语句集，存储在数据库中，经过第一次编译后再次调用不需要编译（效率高），用户通过存储过程名和参数来执行

### 8.2 与函数的区别

**相同点**

1. 都是重复执行的sql语句的集合
2. 都是一次编译，后续执行

**不同点**

1. 标识符不同，FUNCTION / PROCEDURE
2. 函数必须返回值，过程没有。过程无返回值类型，不能将结果直接赋值给变量；函数有返回值类型，调用时，除在select中，必须将返回值赋给变量
3. 调用方式不同，函数使用select调用，过程不是，函数可在select语句中直接使用，过程不能

### 8.3 存储过程操作

#### 创建过程

> 基本语法:
>
> create procedure <过程名>([<参数列表>])
>
> begin
>
> ​    <过程体>
>
> end
>
> <结束符>

如果过程体只有一条指令可以省略begin和end

```
mysql> create procedure my_pro1()
    -> select * from tbStudent;
```

过程基本也可完成函数的所有功能

```
mysql> delimiter $$
mysql> create procedure my_pro2()
    -> begin
    ->     declare i int default 1; -- 局部变量
    ->     set @sum = 0;            -- 会话变量
    ->     while i <= 100 do
    ->         set @sum = @sum + i;
    ->         set i = i + 1;
    ->     end while;
    ->     select @sum; -- 显示结果
    -> end
    -> $$
mysql> delimiter ;
```

#### 查看过程

**查看全部存储过程**

> show procedure status [like 'pattern'];

```
mysql> show procedure status\G
```

**查看过程创建语句**

```
mysql> show create procedure my_pro2\G
```

#### 调用过程

> call <过程名>([<实参列表>]);

```
mysql> call my_pro2();
```

#### 删除过程

```
mysql> drop procedure my_pro2;
```

### 8.4 存储过程形参类型

存储过程对参数有额外的要求(参数分类)

#### in

参数从外部传入内部使用（直接数据或保存数据的变量）

#### out

参数从过程内部把数据保存到变量中传出到外部使用（必须是变量）

如果传入的out变量在外部有数据，那么进入过程后会立即被清空，设为NULL

#### inout

数据从外部传入到过程内部使用，同时内部操作后会将数据返还外部

> 形参使用级别语法：
>
> <过程类型> <变量名> <数据类型>

```
mysql> delimiter $$
mysql> create procedure my_pro3(in int_1 int, out int_2 int, inout int_3 int)
    -> begin
    ->     -- 查看三个形参值
    ->     select int_1, int_2, int_3;
    ->     -- 修改三个变量值
    ->     set int_1 = 10;
    ->     set int_2 = 100;
    ->     set int_3 = 1000;
    ->     select int_1, int_2, int_3;
    ->     -- 查看会话变量
    ->     select @n1,@n2,@n3;
    ->     -- 修改会话变量
    ->     set @n1 = 'a';
    ->     set @n2 = 'b';
    ->     set @n3 = 'c';
    ->     select @n1,@n2,@n3;
    -> end
    -> $$
mysql> delimiter ;
mysql> set @n1:=1;
mysql> set @n2:=2;
mysql> set @n3:=3;
mysql> call my_pro3(@n1, @n2, @n3);
```

第一处查看形参值，out类型的数据会被清空，其他正常

| int_1 | int_2 | int_3 |
| :---: | :---: | :---: |
|   1   | NULL  |   3   |

查看外部的值，out和inout类型的值被覆盖

```
mysql> select @n1,@n2,@n3;
```

| @n1  | @n2  | @n3  |
| :--: | :--: | :--: |
|  a   | 100  | 1000 |

## 9. 触发器

trigger：触发器通过事件触发被执行，而存储过程通过过程名被直接调用

### 9.1 作用

1. 保证数据安全，可在写入数据表前，强制检验或转换数据
2. 触发器发生错误时，异动的结果会被撤销，事务安全
3. 部分数据库管理系统可以针对数据定义语言（DDL）使用触发器，称为DDL触发器
4. 可依照特定的情况，替换异动的指令 (INSTEAD OF)（mysql不支持）

### 9.2 优缺点

**优点**

1. 触发器可通过数据库中的相关表实现级联更改
2. 保证数据安全，进行安全校验

 **缺点**

1. 对触发器过分的依赖，会影响数据库的结构，同时增加维护的复杂度
2. 造成数据在程序层面不可控（PHP层）

### 9.3 基本操作

#### 创建触发器

> 基本语法：
>
> create trigger <触发器名> <触发时机> <触发事件> on <表> for each row
>
> begin
>
> ......
>
> end

**触发对象**：on <表> for each row，触发器绑定表中所有行，当每一行发生指定的改变时会触发触发器

**触发时机**：每张表中的行都会有不同的状态，当SQL指令发生时会令行中数据发生改变，每一行总会有两种状态：数据操作前和操作后(before,after)

**触发事件**：mysql中触发器针对的目标是数据发生改变，对应的操作只有增删改三种写操作(insert,delete,update)

**注意事项**：一张表中，触发器的触发时机绑定的触发事件对应的类型只能有一个，因此一张表中触发器最多只能有6个：before insert，before update，before delete，after insert，after update，after delete

例如：有两张表：商品表、订单表（保留商品ID），每次订单生成，商品表中对应的库存发生变化

|  id  |  name  | inv  |
| :--: | :----: | :--: |
|  1   |  电脑  | 1000 |
|  2   |  手机  | 500  |
|  3   | 游戏机 | 100  |

创建触发器：如果订单表发生数据插入，对应的商品减少库存

```
mysql> delimiter $$
mysql> create trigger after_insert_order after insert on tbOrder for each row
    -> begin
    ->     -- 如何获取商品id等订单信息见下一节
    ->     update tbGoods set inv = inv - 1 where id = 1;
    -> end
    -> $$
mysql> delimiter ;
```

#### 查看触发器

**查看全部触发器**

> show triggers;

```
mysql> show triggers\G
```

**查看触发器创建语句**

```
mysql> show create trigger after_insert_order\G
```

#### 触发触发器

此处执行订单表插入操作即可

#### 删除触发器

```
mysql> drop trigger after_insert_order;
```

### 9.4 记录关键字new,old

触发器在执行前将没有操作的状态（数据）保存到old关键字中，而操作后的状态保存到new关键字中。可以通过old和new来获取绑定表中对应的记录数据。old和new并不是所有触发器都有：insert前没有old，delete后没有new

> 基本语法：
>
> <old/new>.<字段名>

```
mysql> delimiter $$
mysql> create trigger after_insert_order_trigger after insert on tbOrder for each row
    -> begin
    ->     -- 更新库存，new代表新增的订单
    ->     update tbGoods set inv = inv - new.goods_num where id = new.goods_id;
    -> end
    -> $$
mysql> delimiter ;
```

改进：先判断库存

操作目标: 订单表，操作时机: 下单前，操作事件: 插入

```
mysql> delimiter $$
mysql> create trigger before_insert_order_trigger before insert on tbOrder for each row
    -> begin
    ->     -- 取出库存数据进行判断
    ->     select inv from tbGoods where id = new.goods_id into @inv;
    ->     -- 判断
    ->     if @inv < new.goods_num then
    ->         -- 暴力解决，主动出错，中断操作
    ->         insert into xxx values('xxx');
    ->     end if;
    -> end
    -> $$
mysql> delimiter ;
```

