# mysql高级数据操作

## 1. 新增数据

### 1.1 多数据插入

> 基本语法：insert into <表名> [(<字段列表>)] values(<值列表>), (<值列表>),…;

```
mysql> insert into tbTest values('sun', 25), ('jian', 26), ('feng', 27);
```

### 1.2 主键冲突

主键冲突的解决方案：

1. **主键冲突更新：**

> 基本语法：insert into <表名> [(<字段列表>)] values(<值列表>) on duplicate key update <字段> = <新值>;

```
mysql> insert into tbTest values('stu0001','sun') on duplicate key update stu_name = 'sun';
```

2. **主键冲突替换：**

> 基本语法：replace into <表名> [(<字段列表>)] values(<值列表>);

```
mysql> replace into tbTest values('stu0001','sun');
```

### 1.3 蠕虫复制

从已有数据中获取数据并插入到数据表中

> 基本语法：insert into <表名> [(<字段列表>)] select */<字段列表> from <表名>;

```
mysql> insert into tbTest(stu_name) select stu_name from tbTest; 
```

> 注意：
>
> 1. 蠕虫复制通常是重复数据，没有多少业务意义，可以在短期内快速增加表的数据量从而测试表压力，还可以通过大量数据来测试表的效率(索引)
> 2. 蠕虫复制时要注意主键冲突

## 2. 更新数据

更新数据时通常跟随where条件，如果没有条件，是全表更新数据，可以使用 limit 限制更新的数量

> 基本语法：update <表名> set <字段名> = <新值> [where <判断条件>] limit <数量>;

## 3. 删除数据

删除数据时通常跟随where条件，如果没有条件，是删除全表数据，可以使用 limit 限制删除的数量

delete 删除数据时无法重置 auto_increment

**truncate**

Truncate能够重置表的自增长选项，相当于先 `drop` 再 `create`

> 基本语法：truncate <表名>;

```
mysql> truncate tbTest;
```

## 4. 查询数据

完整的查询指令：

> SELECT  select选项  字段列表  FROM  数据源  WHERE  条件  GROUP BY  分组  HAVING  条件  ORDER BY  排序 LIMIT  限制;

### 4.1 select选项

系统处理查询结果的方式

**all** :   默认，表示保存所有记录

**distinct** :   去重，去除重复记录(所有字段都相同)

### 4.2 字段列表

若从多张表获取数据，可能存在不同表中有同名字段，需要使用别名 alias 进行区分

> 基本语法：<字段名> [as] <别名>

```
mysql> select distinct name as name1, name name2 from tbTest;
```

结果包含两个字段：name1, name2

### 4.3 from 数据源

from是为前面的查询提供数据，数据源只要是符合二维表结构的数据(如实体表、子查询)即可

**表查询**：

> 基本语法：from <表1>[, <表2>, <表3>, …]

**动态数据**：

> 基本语法：from (select <字段列表> from <表名>) as <别名>;

### 4.4 where

从数据表获取数据的时候进行条件筛选，where通过运算符进行结果比较来判断数据

### 4.5 group by

分组：根据指定的字段将数据进行分组，分组的目标是为了统计。group by 将数据按照指定的字段分组后，只会保留每组的第一条记录，如果仅想看数据显示，group by 没什么含义

> 基本语法：group by <字段名>;

#### 4.5.1 统计(聚合)函数

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

#### 4.5.2 多分组

将数据按某个字段分组后，对已分组的数据再次分组

先按照字段1分组，再按照字段2分组

> 基本语法：group by <字段1>,<字段2>;

#### 4.5.3 分组排序

mysql中分组默认有排序功能，默认升序

> 基本语法：group by <字段> [asc|desc], <字段> [asc|desc]

```
mysql> select class_id, gender, count(*), group_concat(stu_name) from tbTest group by class_id asc, gender desc;
```

| class_id | gender | count(*) | group_concat(stu_name) |
| :------: | :----: | :------: | :--------------------: |
|    1     |   女   |    2     |          张三          |
|    1     |   男   |    1     |       李四、王五       |
|    2     |   女   |    1     |      学生1、学生2      |
|    2     |   男   |    2     |         学生3          |

#### 4.5.4 回溯统计

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

### 4.6 having

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

### 4.7 order by

排序，默认asc升序

> 基本语法：order by <字段1> [asc|desc],  <字段2> [asc|desc];  

### 4.8 limit

限制记录获取数量，常用于分页

> 基本语法：limt <数量>;
>
> 基本语法：limit offset,length;

例如：limit 0,2;  表示获取前两条记录

## 5. 查询中的运算符

**1 - 算术运算符**：  +、-、*、/、%

通常不在条件中使用，用于结果运算(select中)，其中：除法运算结果均用浮点数表示，若除数为0结果为NULL，NULL进行任何运算结果均为NULL

**2 - 比较运算符**： \>、>=、<、<=、=、<>

通常用在条件中进行限定结果

特殊应用：在结果中进行比较运算

```
mysql> select '1'<=>1, 0.02<=>0, 0.02<>0;
```

| '1'<=>1 | 0.02<=>0 | 0.02<>0 |
| :-----: | :------: | :-----: |
|    1    |    0     |    1    |

mysql中数据会先转成同类型再进行比较，没有bool，1true，0flase

**3 - between** 闭区间查找

```
mysql> select * from tbTest where age between 20 and 30;
```

**4 - 逻辑运算符**

and、or、not 

**5 - in**

> 基本语法： in (<结果1>, <结果2>, <结果3>, …)

**6 - is**

专门用来判断字段是否为NULL的运算符

> 基本语法：is null / is not null

**7 - like**

模糊匹配字符串

> 基本语法：like '匹配模式';

 匹配模式中，有两种占位符：

`_`：匹配单个字符

`%`：匹配多个字符

## 6. 联合查询

UNION 联合查询是可合并多个相似的选择查询的结果集。等同于将一个表追加到另一个表，从而实现将两个表的查询组合到一起。纵向合并，字段数不变，多个查询的记录数合并

### 6.1 应用场景

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

### 6.2 union选项

distinct：去重 (默认)

all：保存所有结果

### 6.3 注意细节

1. union理论上只要保证字段数一样，不需要每次拿到的数据对应的字段类型一致。永远只保留第一个select语句对应的字段名
2. 在联合查询中，如果要使用order by，那么对应的select语句必须使用括号括起来
3. order by 在联合查询中若要生效，必须配合使用 limit + 限制数量（通常使用一个较大的大于对应表的记录数的值）

 ## 7. 连接查询

关系：一对一，一对多，多对多

将多张表连到一起进行查询（会导致记录数行和字段数列发生改变），保证数据的完整性

**分类**：

1. 交叉连接
2. 内连接
3. 外连接：左外连接（左连接）和右外连接（右连接）
4. 自然连接

### 7.1 交叉连接 cross join 

记录数 = 第一张表记录数 * 第二张表记录数；字段数 = 第一张表字段数  + 第二张表字段数（笛卡尔积）

> 基本语法：<表1> cross join <表2>;

交叉连接产生的结果是笛卡尔积，没有实际应用

本质：from <表1>, <表2>;

### 7.2 内连接 inner join 

内连接：inner join，从一张表中取出所有的记录去另外一张表中匹配：利用匹配条件进行匹配，成功了保留，失败了放弃

**流程**：

1. 从第一张表中取出一条记录，然后去另外一张表中进行匹配

2. 利用匹配条件进行匹配: 

   2.1	匹配到：保留，继续向下匹配

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

### 7.3 外连接 outer join 

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
mysql> select * from tbStudent as s left join tbClass c on s.class_id = c.id;
```

**注意：**:

1. 左连接对应的主表数据在左边；右连接对应的主表数据在右边。
2. 左连接和右连接其实可以互相转换，但是数据对应的位置（表顺序）会改变
3. 外连接中主表数据记录一定会保存：连接之后不会出现记录数少于主表（内连接可能）

**应用**

常用的一种获取的数据方式：作为数据获取对应主表以及其他数据（关联）

### 7.4 using关键字

在连接查询中用来代替对应的on关键字的，进行条件匹配。

**原理**

1. 在连接查询时，使用on的地方用using代替
2. 使用using的前提是对应的两张表连接的字段是同名（类似自然连接自动匹配）
3. 如果使用using关键字，那么对应的同名字段，最终在结果中只会保留一个

> 基本语法：<表1> [inner,left,right] join <表2> using(同名字段列表);

```
mysql> select * from tbStudent left join tbClass using(class_id);
```

## 8. 子查询 sub query

子查询是一种常用计算机语言SELECT-SQL语言中嵌套查询下层的程序模块。当一个查询是另一个查询的条件时，称之为子查询。

**子查询和主查询的关系**:

1. 子查询是嵌入到主查询中的；
2. 子查询辅助主查询：要么作为条件，要么作为数据源
3. 子查询其实可以独立存在：是一条完整的select语句

## **子查询分类**

### **按功能分**

标量子查询：子查询返回的结果是一个数据（一行一列）

​	列子查询：返回的结果是一列（一列多行）

​	行子查询：返回的结果是一行（一行多列）

​	表子查询：返回的结果是多行多列（多行多列）

​	Exists子查询：返回的结果1或者0（类似布尔操作）

 

### **按位置分**

​	Where子查询：子查询出现的位置在where条件中

​	From子查询：子查询出现的位置在from数据源中（做数据源）

# **标量子查询**