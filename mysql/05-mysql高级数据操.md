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



自然连接

### 7.1 交叉连接 



### 7.2 内连接 



### 7.3 外连接 

左外连接（左连接）和右外连接（右连接）



