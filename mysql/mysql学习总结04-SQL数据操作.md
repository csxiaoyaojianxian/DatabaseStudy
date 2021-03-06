# mysql学习总结04 — SQL数据操作

[TOC]

## 1. 数据库操作

### 1.1 选择数据库

> 命令： **use** <数据库名>;

使用USE语句为当前数据库做标记，不会影响访问其它数据库中的表

```sql
mysql> USE db1;
mysql> SELECT a_name,e_name FROM author,db2.editor WHERE author.editor_id = db2.editor.editor_id;   
```

### 1.2 显示数据库

> 命令：**show databases**;

```sql
mysql> show databases;
# 部分匹配，'_'匹配当前位置单个字符，'%'匹配指定位置多个字符
mysql> show databases like 'm_database';
mysql> show databases like '%database';
```

默认表：

1. `information_schema` 保存数据库所有的结构信息(表、库)
2. `mysql` 核心数据库，存放权限关系
3. `performance_schema` 效率库
4. `test` 测试，空库

### 1.3 创建数据库

> 命令：**create database** <数据库名>;
>
> **CREATE DATABASE [IFNOT EXISTS] db_name [CHARSET utf8]**

```sql
mysql> create database sunshine;
```

### 1.4 SELECT操作

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

### 1.5 删除数据库

> 命令：**drop database** <数据库名>;
>
> **DROP DATABASE [IFEXISTS] db_name;**

```sql
mysql> drop database sunshine;
mysql> drop database if exists sunshine;
```

### 1.6 修改数据库属性

修改字符集

```
# 显示建表语句
mysql> SHOW CREATE DATABASE db_name;
# 修改默认字符集
mysql> ALTER DATABASE db_name DEFAULT CHARACTER SET utf8
# 或
mysql> alter database db_name charset gbk;
```

## 2. 基础表结构操作

### 2.1 显示表

> 命令：**show tables**;
>
> 命令：**show tables like** '匹配模式';

### 2.2 显示表的结构定义

> 命令：**DESCRIBE** table_name;
>
> 命令：**desc** table_name;
>
> 命令：**show columns from** table_name;
>
> 命令：**show create table**  table_name;

```
mysql> describe sunshine;
mysql> desc sunshine;
mysql> show columns from sunshine;
mysql> show create table sunshine;
```

### 2.3 创建数据表

| 字段名   | 数字类型 | 数据宽度 | 是否为空 | 是否主键    | 自动增加       | 默认值 |
| -------- | -------- | -------- | -------- | ----------- | -------------- | ------ |
| id       | int      | 4        | 否       | primary key | auto_increment |        |
| name     | char     | 20       | 否       |             |                |        |
| sex      | int      | 4        | 否       |             |                | 0      |
| address  | varchar  | 50       | 是       |             |                | 江苏   |
| birthday | date     |          | 是       |             |                |        |
| degree   | double   | 16, 2    | 是       |             |                |        |

> 命令：**create table** <表名> (<字段> <类型> <其他>, <字段> <类型> <其他>,…) [表选项]

```sql
create table sunshine
(
    id int(4) auto_increment not null primary key,
    name char(20) not null,
    sex int(4) not null default 0,
    address varchar(50) default "江苏",
    birthday date,
    degree double(16,2)
) charset utf8; 
```

复制已有表结构，只要使用 "数据库.表名"，就可以在任何数据库下访问其他数据库的表名

> 命令：**create table** <新表名> like <表名>;

注：更多建表操作见附录

### 2.4 表字段操作

**增加字段：**

> 命令：**alter table** <表名> **add**  [column] <字段> <类型> <其他> [first/after <字段>];
>
> **ALTER TABLE** table_name **ADD** field_name field_type;

```sql
mysql> alter table sunshine add salary int(4) default 0;
# 插入到第一个字段
mysql> alter table sunshine add id int first;
```

**修改原字段名称及类型：**

> 命令：**ALTER TABLE** table_name **CHANGE** old_field_name new_field_name field_type [属性 位置] ;
>
> 命令：**alter table** table_name **modify** field_name new_type [属性 位置]

```
# 修改名称
mysql> alter table sunshine change id iId int;
mysql> alter table sunshine modify iId int(20);
```

**删除字段：**

```sql
mysql> ALTER TABLE table_name DROP field_name;
```

### 2.5 修改表名

> 命令：**rename table** <原表名> **to** <新表名>;

```sql
mysql> rename table OldTable to NewTable;
```

注意：不能有活动的事务或对锁定的表操作，须有对原表的 `ALTER` 和 `DROP` 权限，和对新表的 `CREATE` 和 `INSERT` 权限

### 2.6 删除数据表

> 命令：**drop table** <表名> [,<表名2>…];

```sql
mysql> drop table sunshine; -- 普通删除
mysql> DROP TABLE IF EXISTS `sunshine`; -- 安全删除
```

### 2.7 索引操作

**加索引**

> 命令：**alter table** <表名> **add index** <索引名 (字段名1[，字段名2 …])>;

```sql
mysql> alter table sunshine add index name_index1(name);
```

**加主关键字索引**

详细操作见`mysql列属性.md`文档

> 命令：**alter table** <表名> **add primary key** <(字段名)>;

```sql
mysql> alter table sunshine add primary key(id);
```

**加唯一限制条件索引**

详细操作见`mysql列属性.md`文档

> 命令：**alter table** <表名> **add unique** <索引名 (字段名)>;

```sql
mysql> alter table sunshine add unique name_index2(cardnumber);
```

**删除索引**

> 命令：**alter table** <表名> **drop index** <索引名>;

```sql
mysql> alter table sunshine drop index name_index2;
```

### 2.8 设置表属性

表属性(表选项): engine / charset / collate

> 命令：**alter table** <表名> <表选项> [=] <值>;

```
mysql> alter table tbSunshine charset gbk;
```

## 3. 基础表数据操作

此部分基础操作可直接跳过

### 3.1 表插入数据

> 命令：**insert into** <表名 [( <字段名1>[,..<字段名n > ])]> **values** <( 值1 )[, ( 值n )]>;

```sql
mysql> insert into sunshine values(1,'Sun',99.99),(2,'Jian',98.99),(3,'Fent', 97.99);
```

注意：insert into每次只能插入一条记录

### 3.2 查询表数据

**查询所有行**

> 命令：**select** <字段1，字段2，...> **from** < 表名 > **where** < 表达式 >;

```sql
mysql> select * from sunshine;
```

**查询前n行数据 LIMIT**

```sql
mysql> select * from sunshine order by id limit 0,2;
```

### 3.3 删除表数据

```sql
mysql> DELETE FROM sunshine WHERE name='csxiaoyao';
```

### 3.4 修改表数据

> 命令：**update** <表名> **set** <字段> **=** <新值,…> **where** <条件>

```sql
mysql> update sunshine set name='csxiaoyao' where id=1;
```

**单表UPDATE**

> 命令：**UPDATE** `[LOW_PRIORITY][IGNORE]` tbl_name **SET** col_name1=expr1 `[, col_name2=expr2 ...][WHERE where_definition] [ORDER BY …][LIMIT row_count]`

**多表UPDATE**

> 命令：**UPDATE** `[LOW_PRIORITY][IGNORE]` table_references **SET** col_name1=expr1 `[, col_name2=expr2 ...][WHERE where_definition]`

注意：如果指定ORDER BY子句，则按被指定顺序对行更新；LIMIT子句限制被更新行数

## 4. 新增数据

### 4.1 多数据插入

> 基本语法：insert into <表名> [(<字段列表>)] values(<值列表>), (<值列表>),…;

```
mysql> insert into tbTest values('sun', 25), ('jian', 26), ('feng', 27);
```

### 4.2 主键冲突

主键冲突的解决方案：

**1. 主键冲突更新：**

> 基本语法：insert into <表名> [(<字段列表>)] values(<值列表>) on duplicate key update <字段> = <新值>;

```
mysql> insert into tbTest values('stu0001','sun') on duplicate key update stu_name = 'sun';
```

**2. 主键冲突替换：**

> 基本语法：replace into <表名> [(<字段列表>)] values(<值列表>);

```
mysql> replace into tbTest values('stu0001','sun');
```

### 4.3 蠕虫复制

从已有数据中获取数据并插入到数据表中

> 基本语法：insert into <表名> [(<字段列表>)] select */<字段列表> from <表名>;

```
mysql> insert into tbTest(stu_name) select stu_name from tbTest; 
```

> 注意：
>
> 1. 蠕虫复制通常是重复数据，没有多少业务意义，可以在短期内快速增加表的数据量从而测试表压力，还可以通过大量数据来测试表的效率(索引)
> 2. 蠕虫复制时要注意主键冲突

## 5. 更新数据

更新数据时通常跟随where条件，如果没有条件，是全表更新数据，可以使用 limit 限制更新的数量

> 基本语法：update <表名> set <字段名> = <新值> [where <判断条件>] limit <数量>;

## 6. 删除数据

删除数据时通常跟随where条件，如果没有条件，是删除全表数据，可以使用 limit 限制删除的数量

delete 删除数据时无法重置 auto_increment

**truncate**

Truncate能够重置表的自增长选项，相当于先 `drop` 再 `create`

> 基本语法：truncate <表名>;

```
mysql> truncate tbTest;
```

## 7. 查询数据

完整的查询指令：

> SELECT  select选项  字段列表  FROM  数据源  WHERE  条件  GROUP BY  分组  HAVING  条件  ORDER BY  排序 LIMIT  限制;

### 7.1 select选项

系统处理查询结果的方式

**all** :   默认，表示保存所有记录

**distinct** :   去重，去除重复记录(所有字段都相同)

### 7.2 字段列表

若从多张表获取数据，可能存在不同表中有同名字段，需要使用别名 alias 进行区分

> 基本语法：<字段名> [as] <别名>

```
mysql> select distinct name as name1, name name2 from tbTest;
```

结果包含两个字段：name1, name2

### 7.3 from 数据源

from是为前面的查询提供数据，数据源只要是符合二维表结构的数据(如实体表、子查询)即可

**表查询**：

> 基本语法：from <表1>[, <表2>, <表3>, …]

**动态数据**：

> 基本语法：from (select <字段列表> from <表名>) as <别名>;

### 7.4 where

从数据表获取数据的时候进行条件筛选，where通过运算符进行结果比较来判断数据，注意和后面的`having`区分

### 7.5 group by

分组：根据指定的字段将数据进行分组，分组的目标是为了统计。group by 将数据按照指定的字段分组后，只会保留每组的第一条记录，如果仅想看数据显示，group by 没什么含义

> 基本语法：group by <字段名>;

#### 7.5.1 统计(聚合)函数

> count()：统计每组中的数量，count(<字段名>)不统计为NULL的字段，count(*)统计记录数
>
> avg()：求平均值
>
> sum()：求和
>
> max()：求最大值
>
> min()：求最小值
>
> group_concat()：将分组中指定的字段进行合并（字符串拼接）

```
mysql> select class_id, group_concat(stu_name), count(*), max(age), min(height), avg(score) from tbTest group by class_id;
```

| class_id | group_concat(stu_name) | count(*) | max(age) | min(height) | avg(score) |
| :------: | :--------------------: | :------: | :------: | :---------: | :--------: |
|    1     |       张三、李四       |    2     |    28    |     170     |     98     |
|    2     |       王五、赵六       |    2     |    25    |     172     |     99     |

#### 7.5.2 多分组

将数据按某个字段分组后，对已分组的数据再次分组

先按照字段1分组，再按照字段2分组

> 基本语法：group by <字段1>,<字段2>;

#### 7.5.3 分组排序

mysql中分组默认有排序功能，默认升序

> 基本语法：group by <字段> [asc|desc], <字段> [asc|desc]

```
mysql> select class_id, gender, count(*), group_concat(stu_name) from tbTest group by class_id asc, gender desc;
```

| class_id | gender | count(*) | group_concat(stu_name) |
| :------: | :----: | :------: | :--------------------: |
|    1     |   女   |    2     |       李四,王五        |
|    1     |   男   |    1     |          张三          |
|    2     |   女   |    1     |         学生3          |
|    2     |   男   |    2     |      学生1,学生2       |

#### 7.5.4 回溯统计

多分组后，往上统计过程中需要层层上报，称为回溯统计。每次分组向上统计的过程都会产生一次新的统计数据，而且当前数据对应的分组字段为NULL

> 基本语法：group by <字段> [asc|desc] with rollup; 

```
mysql> select class_id, gender, count(*) from tbTest group by class_id, gender with rollup;
```

| class_id | gender | count(*) |
| :------: | :----: | :------: |
|    1     |   男   |    1     |
|    1     |   女   |    2     |
|    1     |  NULL  |    3     |
|    2     |   男   |    2     |
|    2     |   女   |    1     |
|    2     |  NULL  |    3     |
|   NULL   |  NULL  |    6     |

### 7.6 having

having 的本质和 where 一样，用来进行数据条件筛选

例如：查询班级人数大于等于4个以上的班级

```
mysql> select class_id, count(*) as number from tbTest group by class_id having count(*) >= 4;
```

having 在 group by 子句之后针对分组数据进行统计筛选，但是where不行

where不能使用聚合函数，因为聚合函数用在 group by 分组，此时 where 已执行完毕

having 在 group by 分组之后，可以使用聚合函数或字段别名 (where从表中取出数据，别名在数据进入内存后才有)

> 注意：
>
> having 在 group by 之后，group by 在 where 之后
>
> where 表示将数据从磁盘取到内存，where之后的所有操作都是内存操作

### 7.7 order by

排序，默认asc升序

> 基本语法：order by <字段1> [asc|desc],  <字段2> [asc|desc];  

### 7.8 limit

限制记录获取数量，常用于分页

> 基本语法：limt <数量>;
>
> 基本语法：limit offset,length;

例如：limit 0,2;  表示获取前两条记录

## 8. 查询中的运算符

**1 - 算术运算符**：  +、-、*、/、%

通常不在条件中使用，用于结果运算(select中)，其中：除法运算结果均用浮点数表示，若除数为0结果为NULL，NULL进行任何运算结果均为NULL

**2 - 比较运算符**： \>、>=、<、<=、=、<>、<=>

通常用在条件中进行限定结果

> `<>` 与 `!=` 都表示不等于，但一般用 `<>`，因为 `!=` 在sql2000中语法错误，兼容性不如 `<>`
>
> `<=>` 安全比较运算符，用来做 NULL 值的关系运算，因为 mysql 的 NULL 值的特性，NULL进行任何运算结果均为NULL，`1 <> NULL` 为 NULL，`1 IS NOT NULL` 为 1，`!(1 <=> NULL)` 为 1，可见 `<=>` 更简洁

特殊应用：在结果中进行比较运算

```
mysql> select '1'<=>1, 0.02<=>0, 0.02<>0;
```

| '1'<=>1 | 0.02<=>0 | 0.02<>0 |
| :-----: | :------: | :-----: |
|    1    |    0     |    1    |

mysql中数据会先转成同类型再进行比较，没有bool类型，1 代表 true，0 代表 flase

**3 - between** 

**闭区间**查找

```
mysql> select * from tbTest where age between 20 and 30;
```

**4 - 逻辑运算符**

and、or、not 

**5 - in**

> 基本语法： in (<结果1>, <结果2>, <结果3>, …)

```
mysql> select * from tbTest where stu_id in ('stu001','stu002','stu003');
```

**6 - is**

专门用来判断字段是否为NULL的运算符

> 基本语法：is null / is not null

**7 - like**

模糊匹配字符串

> 基本语法：like '匹配模式';

 匹配模式中，有两种占位符：

`_`：匹配单个字符

`%`：匹配多个字符

## 9. 联合查询

UNION 联合查询是可合并多个相似的选择查询的结果集。等同于将一个表追加到另一个表，从而实现将两个表的查询组合到一起。纵向合并，字段数不变，多个查询的记录数合并

### 9.1 应用场景

将同一张表中不同的结果（需要对应多条查询语句来实现），合并到一起展示数据

最常见：在数据量大的情况下对表进行分表操作，需要对每张表进行部分数据统计，使用联合查询将数据存放到一起显示

例如：男生身高升序排序，女生身高降序排序

例如：QQ1表获取在线数据、QQ2表获取在线数据 … >>>> 将所有在线的数据显示出来

> 基本语法：
>
> ​      select 语句
>
> ​      union [union 选项]
>
> ​      select 语句;

### 9.2 union选项

distinct：去重 (默认)

all：保存所有结果

### 9.3 注意细节

1. union理论上只要保证字段数一样，不需要每次拿到的数据对应的字段类型一致。永远只保留第一个select语句对应的字段名
2. 在联合查询中，如果要使用order by，那么对应的select语句必须使用括号括起来
3. order by 在联合查询中若要生效，必须配合使用 limit + 限制数量（通常使用一个较大的大于对应表的记录数的值）

```
mysql> -- 使用 order by 必须使用括号，若要生效必须配合limit+数量
mysql> (select * from stu where gender = '男' order by stu_height asc limit 10)
    -> union
    -> (select * from stu where gender = '女' order by stu_height desc limit 10)
```

| stu_id  | stu_height | gender |
| :-----: | :--------: | :----: |
| stu0006 |    175     |   男   |
| stu0007 |    180     |   男   |
| stu0003 |    182     |   男   |
| stu0004 |    189     |   女   |
| stu0001 |    175     |   女   |
| stu0005 |    170     |   女   |
| stu0002 |    160     |   女   |

 ## 10. 连接查询

关系：一对一，一对多，多对多

将多张表连到一起进行查询（会导致记录数行和字段数列发生改变），保证数据的完整性

**分类**：

1. 交叉连接
2. 内连接
3. 外连接：左外连接（左连接）和右外连接（右连接）
4. 自然连接

### 10.1 交叉连接 cross join 

**记录数** = 第一张表记录数 * 第二张表记录数;（笛卡尔积）

**字段数** = 第一张表字段数  + 第二张表字段数;（笛卡尔积）

> 基本语法：<表1> cross join <表2>;

交叉连接产生的结果是笛卡尔积，没有实际应用

本质：from <表1>, <表2>;

### 10.2 内连接 inner join 

**记录数** = x (匹配成功的数目)；

**字段数** = 第一张表字段数  + 第二张表字段数

内连接：inner join，从一张表中取出所有的记录去另外一张表中匹配：利用匹配条件进行匹配，成功了保留，失败了放弃

**流程**：

1. 从第一张表中取出一条记录，然后去另外一张表中进行匹配

2. 利用匹配条件进行匹配: 

   2.1	匹配成功：保留，继续向下匹配

   2.2	匹配失败：向下继续，如果全表匹配失败，结束

> 基本语法：<表1> [inner] join <表2> on <匹配条件>;

```
mysql> select * from tbStudent as s inner join tbClass c on s.class_id = c.id;
```

**注意点：**

1. 如果内连接没有条件（允许），那么其实就是交叉连接（避免）

2. 使用匹配条件进行匹配，因为表的设计通常容易产生同名字段，尤其是ID，所以为了避免重名出现错误，通常使用 <表名.字段名> 来确保唯一性

3. 通常，如果条件中使用到对应的表名，而表名通常比较长，所以可以通过表别名来简化

4. 内连接匹配的时候，必须保证匹配到才会保存

5. 内连接因为不强制必须使用匹配条件（on）因此可以在数据匹配完成之后，使用where条件来限制，效果与on一样（建议使用on）

   ```
   mysql> select * from tbStudent as s inner join tbClass c where s.class_id = c.id;
   ```

**应用**:

内连接通常是在对数据有精确要求的地方使用：必须保证两种表中都能进行数据匹配。

### 10.3 外连接 outer join 

**记录数** >= x (主表的条目数)；

**字段数** = 第一张表字段数  + 第二张表字段数

左外连接（左连接）和右外连接（右连接）

外连接：outer join，按照某一张表作为主表（表中所有记录在最后都会保留），根据条件去连接另外一张表，从而得到目标数据。

 外连接分为两种：左外连接（left join），右外连接（right join）

左连接：左表是主表

右连接：右表是主表

**流程**：

1、	确定连接主表：左连接left join左边的表为主表；right join右边为主表

2、	拿主表的每一条记录，去匹配另外一张表（从表）的每一条记录

3、	如果满足匹配条件：保留；不满足即不保留

4、	如果主表记录在从表中一条都没有匹配成功，那么也要保留该记录：从表对应的字段值都为NULL

> 基本语法：
>
> 左连接：<主表> left join <从表> on <连接条件>;
>
> 右连接：<主表> right join <从表> on <连接条件>;

```
mysql> select * from tbStudent as s right join tbClass c on s.class_id = c.id;
```

| stu_id  | stu_name | class_id |  id  | name |
| :-----: | :------: | :------: | :--: | :--: |
| stu0001 |   xxx    |    1     |  1   | 1班  |
| stu0004 |   xxx    |    1     |  1   | 1班  |
| stu0002 |   xxx    |    1     |  1   | 1班  |
| stu0003 |   xxx    |    1     |  1   | 1班  |
| stu0006 |   xxx    |    2     |  2   | 2班  |
| stu0005 |   xxx    |    2     |  2   | 2班  |
| stu0007 |   xxx    |    2     |  2   | 2班  |
|  NULL   |   NULL   |   NULL   |  3   | 3班  |

**注意：**:

1. 左连接对应的主表数据在左边；右连接对应的主表数据在右边。
2. 左连接和右连接其实可以互相转换，但是数据对应的位置（表顺序）会改变
3. 外连接中主表数据记录一定会保存：连接之后不会出现记录数少于主表（内连接可能）

**应用**

常用的数据获取方式：获取主表和对应的从表数据（关联）

### 10.4 using关键字

**字段数** = 第一张表字段数  + 第二张表字段数 - on对应的字段数

在连接查询中代替on关键字进行条件匹配

**原理**

1. 在连接查询时，使用on的地方用using代替
2. 使用using的前提是对应的两张表连接的字段同名（类似自然连接自动匹配）
3. 如果使用using关键字，对应的同名字段在结果中只会保留一个

> 基本语法：<表1> [inner,left,right] join <表2> using(同名字段列表);

```
mysql> select * from tbStudent left join tbClass using(class_id);
```

| class_id | stu_id  | stu_name | name |
| :------: | :-----: | :------: | :--: |
|    1     | stu0001 |   xxx    | 1班  |
|    1     | stu0002 |   xxx    | 1班  |
|    2     | stu0003 |   xxx    | 2班  |
|    2     | stu0004 |   xxx    | 2班  |
|    1     | stu0005 |   xxx    | 1班  |
|    2     | stu0006 |   xxx    | 2班  |
|    1     | stu0007 |   xxx    | 1班  |

## 11. 子查询

子查询 (sub query) 是一种常用计算机语言SELECT-SQL语言中嵌套查询下层的程序模块。当一个查询是另一个查询的条件时，称之为子查询

**子查询和主查询的关系**

1. 子查询嵌入到主查询中
2. 子查询辅助主查询，作为条件或数据源
3. 子查询是一条完整的可独立存在的select语句

**子查询按功能分类**

* 标量子查询：结果是一个数据（一行一列）
* 列子查询：结果是一列（一列多行）
* 行子查询：结果是一行（一行多列）
* 表子查询：结果是多行多列（多行多列）
* exists子查询：返回结果1或0（类似布尔操作）

 **子查询按位置分类**

* where子查询：子查询出现的位置在where条件中（标量、列、行子查询）

* from子查询：子查询出现的位置在from数据源中，做数据源（表子查询）

### 11.1 标量子查询

标量子查询：子查询结果是一个数据（一行一列）

> 基本语法：
>
> select * from <数据源> where <条件判断 =/<> > (select <字段名> from <数据源> where <条件判断>); 

举例：

知道一个学生的名字，查询其班级名

1. 通过学生表获取班级id，得到一个数据（一行一列）
2. 通过班级id获取班级名

```
mysql> select * from tbClass where id = (select class_id from tbStudent where stu_name='xxx');
```

### 11.2 列子查询

列子查询：子查询结果是一列数据（一列多行）

> 基本语法：
>
> <主查询> where <条件> in (<列子查询结果>);

举例：

获取有学生的班级名

1. 查询学生表中所有班级id，得到一列数据（一列多行）
2. 通过班级id获取班级名

```
mysql> select name from tbClass where id in (select class_id from tbStudent);
```

### 11.3 行子查询

行子查询：子查询结果是一行数据（一行多列）

行元素：字段元素指一个字段对应的值，行元素对应多个字段，多个字段合作一个元素参与运算称为行元素

> 基本语法：
>
> <主查询> where (<行元素>) = (<行子查询>);

举例：

```
mysql> select * from tbStudent where (stu_age, stu_height) = (select max(stu_age), max(stu_height) from tbStudent);
```

### 11.4 表子查询

表子查询：子查询结果是多行多列数据（多行多列）

表子查询与行子查询相似，但行子查询需要构造行元素，而表子查询不需要，行子查询是用于where条件判断，表子查询是用于from数据源

> 基本语法：
>
> select <字段表> from (<表子查询>) as <别名> [ where]\[ group by]\[ having]\[ order by]\[ limit];

举例：

获取每班身高最高的学生（一个）

1. 将每个班最高的学生排在最前（order by）
2. 针对结果 group by 班级，保留每组第一个

```
mysql> select * from (select * from tbStudent order by stu_height desc) as tbTemp group by class_id;
```

### 11.5 exists子查询

exists子查询：根据子查询结果进行判断，1代表结果存在，0代表不存在

> 基本语法：
>
> where exists(<查询语句>);     -- where 1：永远为真

举例：

查询有学生的所有班级

```
mysql> select * from tbClass as c where exists(select stu_id from tbStudent as s where s.class_id = c.id);
```

|  id  | Name |
| :--: | :--: |
|  1   | 1班  |
|  2   | 2班  |

### 11.6 列子查询特定关键字

#### in

> <主查询> where <条件> in (<列子查询>);

```
mysql> select * from tbStudent where class_id in (select class_id from tbClass);
```

#### any

> = any(<列子查询>) ：条件在查询结果中有任意一个匹配即可，等价于 in，`1=any(1,2,3) `为 true
>
> <>any(<列子查询>)：条件在查询结果中不等于任意一个，`1<>any(1,2,3)`为true
>
> 如果字段结果为NULL则不参与匹配

```
mysql> -- =any 与 in 等价
mysql> select * from tbStudent where class_id = any(select class_id from tbClass);
mysql> -- 因为此处子查询的结果超过1个，所以实际结果与 =any 相同
mysql> select * from tbStudent where class_id <>any(select class_id from tbClass);
```

#### some

与any完全相同，在国外 some 与 any 正面含义一致，否定含义不同：not any 与 not some，开发者为消除语法上的差异重新设计了 some

#### all

> = all(<列子查询>)：等于所有
>
> <>all(<列子查询>)：不等于所有

```
mysql> select * from tbClass where id <> all(select class_id from tbStudent);
```

|  id  | Name |
| :--: | :--: |
|  3   | 3班  |

## 12. 外键

### 12.1 概念

foreign key : 一张表(从表)中有一个字段(外键)，保存的值指向另外一张表(主表)的主键 

### 12.2 外键的操作

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

### 12.3 外键基本要求

1. 外键字段与主表主键字段类型完全一致
2. 外键字段与主表主键字段基本属性相同
3. 如果是在表后增加外键，对数据有要求(从表数据与主表的关联关系)
4. 外键只能使用innodb存储引擎，myisam不支持

### 12.4 外键约束概念

外键约束主要约束主表操作，从表仅约束不能插入主表不存在的数据，外键约束约束了例如：

1. 从表插入数据，不能插入对应主表主键不存在的数据
2. 主表删除数据，不能删除被从表引入的数据

外键约束保证了数据的完整性(主表与从表数据一致)，外键强大的数据约束作用可能导致数据在后台变化的不可控，所以外键在实际开发中较少使用

### 12.5 外键约束模式

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

## 13. 视图

### 13.1 创建视图

视图的本质是SQL指令（select语句，单表数据/连接查询/联合查询/子查询）

> 基本语法：
>
> create view <视图名> as <select指令>;

```
mysql> create view stu_class_v as
    -> select s.*, c.name from tbStudent as s left join tbClass as c on s.class_id = c.class_id;
```

### 13.2 查看视图结构

图本身是虚拟表，所以关于表的操作都适用于视图

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

### 13.3 使用视图

视图本身没有数据，是临时执行select语句得到的结果，视图主要用于查询操作

> 基本语法：
>
> select <字段列表> from <视图名> [子句];

```
mysql> select * from stu_class_v;
```

### 13.4 修改视图

修改视图的查询语句

> 基本语法：
>
> alter view <视图名> as <新select指令>;

```
mysql> alter view stu_class_v as
    -> select * from tbStudent as s left join tbClass as c using(class_id);
```

### 13.5 删除视图

> 基本语法：
>
> drop view <视图名>;

```
mysql> drop view stu_class_v;
```



![](http://www.csxiaoyao.com/src/img/sign.jpg)