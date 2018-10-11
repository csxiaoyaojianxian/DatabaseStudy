# redis学习笔记

[TOC]

> Write By CS逍遥剑仙
> 我的主页: [www.csxiaoyao.com](http://www.csxiaoyao.com/)
> GitHub: [github.com/csxiaoyaojianxian](https://github.com/csxiaoyaojianxian)
> Email: sunjianfeng@csxiaoyao.com
> QQ: [1724338257](https://www.csxiaoyao.cn/blog/index.php/2018/09/18/02-2/wpa.qq.com/msgrd?uin=1724338257&site=qq&menu=yes)

## 1. 起步

### 1.1 NoSQL

NoSQL(Not Only SQL)，泛指非关系型数据库

**特点:** 通常是以key-value形式存储，不支持SQL语句，没有表结构

**优点:** 高并发读写性能、大数据量扩展(分布式存储)、配置简单、操作与数据模型灵活高效、成本 低廉

**缺点:** 没有统一的标准、没有正式的官方支持、各种产品还不算成熟

### 1.2 redis

Redis(Remote Dictionary Server 远程数据服务) 是一款**内存高速缓存数据库**，使用C语言编写，数据模型为key-value，为保证效率数据都缓存在内存中，也可以周期性把更新的数据写入磁盘或把修改操作写入追加的记录文件。 

**特点:** 

(1) 高速读取数据(in-memory)  

(2) 减轻数据库负担  

(3) 有集合计算功能(优于普通数据库和同类别产品) 

(4) 多种数据结构支持

### 1.3 与memcache比较

(1) **数据类型:** memcache支持的数据类型仅为字符串，redis支持的数据类型有字符串、哈希、链表、集合、有序集合

(2) **持久化:** redis和memcache数据存储在内存，但redis可以持久化，周期性保存数据到硬盘，重启或断电不会丢失数据

(3) **数据量:** memcahce一个键最多存储1M数据，redis的键最多存储1G数据

## 2. macos安装redis

```
$ brew install redis
```

安装完成后 `/usr/local/Cellar/redis/4.0.11/bin` 下的几个命令:

```
redis-benchmark						性能测试命令
redis-check-aof	/ redis-check-rdb	日志检测工具
redis-server						服务器启动命令
redis-cli							客户端连接服务器命令
```

修改配置文件

```
$ vi /usr/local/etc/redis.conf
# 配置redis服务后台执行
daemonize yes
```

启动redis服务

```
$ redis-server /usr/local/etc/redis.conf
```

检查是否启动成功，6379端口

```
$ redis-cli ping
```

客户端连接redis服务

```
$ redis-cli -h localhost -p 6379
# 连接到本地直接输入连接命令即可
$ redis-cli
```

关闭redis服务

```
# 方法1
$ redis-cli shutdown
# 方法2
$ pkill redis-server
```

## 3. 数据类型

### 3.1 字符串(string)

redis的string可以包含任何数据，包括jpg图片或序列化的对象，单个value值最大上限是1G字节

```
【 set 】
注意：重新设置则直接覆盖
> set name "csxiaoyao"

【 get 】
注意：key不存在返回nil
> get name

【 incr / incrby 】 
> set age 25
> incr age
> incrby age 10
```

### 3.2 哈希(hash)

hash可以存储mysql中的一行数据，类似于关联数组

```
【 hset 】设置哈希中field和vlaue值
hset <哈希名(键名)> <field> <value>
> hset user:id:1 id 1
> hset user:id:1 name csxiaoyao
> hset user:id:1 age 25
> hset user:id:2 id 2
> hset user:id:2 name sunshine
> hset user:id:2 age 26

【 hget 】获取哈希中指定field的value值
> hget user:id:1 name

【 hmset 】一次性设置多个field和value
> hmset user:id:3 id 3 name sun age 25

【 hmget 】一次性获取多个field的value
> hmget user:id:3 id name

【 hgetall 】获取指定哈希中所有field和value
> hgetall user:id:1
```

### 3.3 链表(list)

list类型实际为双向链表，通过push、pop操作从链表的头部或尾部添加删除元素，这使得list既可以用作栈，也可以用作队列。

```
【 lpush 】链表头部添加元素
> lpush list1 csxiaoyao
> lpush list1 sunshine
> lpush list1 sun
【 lrange 】获取链表元素
语法：lragne 链表名 开始下标 结束下标
注意：1. 开始下标0、结束下标-1则返回链表中所有元素;  2. 链表元素从0开始计数，类似索引数组
> lrange list1 0 -1  # sun sunshine csxiaoyao
> lrange list1 0 1  # sun sunshine
【 rpush 】链表尾部添加元素
> rpush list2 cs
> rpush list2 sun
> rpush list2 jianfeng
> lrange list2 0 -1  # cs sun jianfeng
【 ltrim 】保留指定范围元素
> ltrim list2 0 1
> lrange list2 0 -1  # cs sun
【 lpop 】链表头部删除元素，返回删除元素
> lpop list1
> lrange list1 0 -1  # sunshine csxiaoyao
```

### 3.4 集合(set)

redis的set是string类型的无序集合，set元素最大可以包含(2^32-1)(整型最大值)个元素。set类型除基本添加、删除操作，还包含集合取并集(union)、交集(intersection)、差集(difference)，通过这些操作可以轻松实现好友推荐功能。

```
【 sadd 】向集合中添加元素
> sadd set1 cs
> sadd set1 sun
> sadd set1 sunshine
> sadd set2 jianfeng
> sadd set2 sun
【 smembers 】获取集合中元素
> smembers set1  # sunshine sun cs
【 sdiff 】差集(在集合1中存在，不在集合2中存在)
> sdiff set1 set2  # cs sunshine
【 sinter 】交集
> sinter set1 set2  # sun
【 sunion 】并集
> sunion set1 set2  # cs sun sunshine jianfeng
【 scard 】获取集合中元素的个数
> scard set1  # 3
```

### 3.5 有序集合(zset)

sorted set是set的升级版，在set基础上增加一个**顺序属性**，这一属性在添加修改元素时可以指定，**每次指定后** zset会自动重新按新的值调整顺序。

```
【 zadd 】添加，如果元素存在，则更新其顺序
> zadd zset1 10 cs
> zadd zset1 3 sun
> zadd zset1 7 sunshine
【 zrange 】返回排序后名次[start,stop]的元素，默认升序
> zrange zset1 0 -1  # sun sunshine cs
【 zrevrange 】按序号降序获取有序集合中的内容
> zrevrange zset1 0 -1  # cs sunshine sun
```

## 4. Redis常用命令

```
【 keys 】返回当前数据库里的键，可使用通配符 * ?
> keys *
【 exists 】判断键是否存在
> exists name
【 del 】删除指定键
> del age
【 expire 】设置键有效期(s)
> expire name 60
【 ttl 】返回键剩余过期时间(s)
> ttl name
【 type 】返回数据类型
> type name
【 select 】选择数据库
redis里默认有0-15号数据库，默认是0号，可以通过redis.conf配置文件设置database
> select 3
【 dbsize 】返回当前数据库里键的个数
> dbsize
【 flushdb 】清空当前数据库里所有的键(慎用)
> flushdb
【 flushall 】清空所有数据库里所有的键(慎用)
> flushall
```

## 5. 安全认证

设置客户端连接后进行任何其他操作前需要使用的密码。

**设置redis配置文件(redis.conf)**

注意：设置的密码是明文的，因此要对redis.conf配置文件进行严格授权

```
requirepass <设置的密码>
```

**客户端验证方式**

方式一：通过客户端登录到服务器时，添加  -a 选项

```
$ redis-cli –a 19931128
```

方式二：登录到服务器端后，使用auth命令完成验证。

```
> auth sunshine
```

   ## 6. phpredis

在mac中的MAMP下安装phpredis

```
$ cd /Applications/MAMP/bin/php/php7.1.1
$ git clone https://github.com/phpredis/phpredis
$ cd phpredis
$ phpize
$ ./configure --with-php-config=/Applications/MAMP/bin/php/php7.1.1/bin/php-config
$ make
$ sudo make install
```

测试

```
<?php
	header("content-type:text/html;charset=utf-8");
	$redis = new Redis();
	$redis->connect('127.0.0.1',6379);
	// $redis->auth('pass');
	
	// 添加字符串数据
	$redis->set('string1','测试');
	// 设置有效时间
	$redis->setTimeout('string1',1800);
    // 添加哈希数据
    $redis->hmset('hash1',array('id'=>1,'name'=>'sunshine','age'=>25));
    // 添加链表数据
    $redis->lpush('list1','sunshine');
    $redis->lpush('list1','csxiaoyao');
    $redis->lpush('list1','sun');
    // 添加集合数据
    $redis->sadd('set1','sun');
    $redis->sadd('set1','csxiaoyao');
    // 添加有序集合数据
    $redis->zadd('zset1',10,'sunshine');
    $redis->zadd('zset1',20,'csxiaoyao');
    
	// 获取字符串类型数据
    var_dump($redis->get('string1')); echo '<br>';
    // 获取哈希类型数据
    var_dump($redis->hgetall('hash1')); echo '<br>';
    // 获取链表类型数据
    var_dump($redis->lrange('list1',0,-1)); echo '<br>';
    // 获取集合类型数据
    var_dump($redis->smembers('set1')); echo '<br>';
    // 获取有序集合类型数据
    var_dump($redis->zrange('zset1',0,-1)); echo '<br>';
```

## 7. 应用：redis实现秒杀

实现原理：使用redis链表中队列进行pop操作，利用pop操作原子性，即使很多用户同时到达，也是依次执行

**step1:** 设置库存，将商品库存入队列

```
<?php
    $store=1000;
    $redis=new Redis();
    $result=$redis->connect('127.0.0.1',6379);
    $goods_number = 100;
    for($i=0;$i<$goods_number;$i++){
        $redis->lpush('goods_store',1); // 模拟库存
    }
    echo $redis->llen('goods_store');
?>
```

**step2:** 秒杀开始操作

```
<?php
	$redis=new Redis();
    $result=$redis->connect('127.0.0.1',6379);
    // 设置库存缓存周期
    $redis->setTimeout('goods_store',60);
?>
```

**step3:** 客户端执行下单操作

```
<?php
    $redis=new Redis();
    $redis->connect('127.0.0.1',6379);
    // 下单前判断redis队列库存量
    $count=$redis->lpop('goods_store');
    if(!$count){
        echo '抢购失败';
        return;
    }
    // 跳转下单页面，完成下单操作
?>
```

## 8. 持久化机制

redis为了内存数据的安全考虑，会把内存中的数据以**文件**形式保存到硬盘，在服务器重启后会自动把硬盘的数据恢复到内存(redis)里。数据保存到硬盘的过程称为"持久化"。

redis支持两种持久化方式： 

(1) snapshotting(快照)默认方式 

(2) append-only file(缩写aof)的方式

注意：如果两种持久化方式都开启，则以aof为准

### 8.1 snapshotting快照

该持久化默认开启，一次性把redis中全部数据保存到硬盘中(备份文件名默认为dump.rdb)，如果数据非常多(10-20G)就不适合频繁进行该持久化操作。由于快照方式是在一定间隔执行一次，所以如果redis意外down掉会丢失最后一次快照后的所有修改。

```
> save 900 1  # 900秒内超过1个key被修改，则发起快照保存
> save 60 10000  # 60秒内超过10000个key被修改，则发起快照保存
```

注意：屏蔽该触发条件，即可关闭快照方式

备份文件名默认是dump.rdb，可以修改配置文件

```
dbfilename dump.rdb
```

**手动发起快照：**

```
# 方式一：在登录状态
> bgsave
# 方式二：在没有登录状态
$ ./redis-cli bgsave
```

### 8.2 append-only-file 追加方式持久化AOF

备份用户执行的"写"指令(添加、修改、删除)到文件中，还原数据时执行具体写指令。

配置redis.conf

```
# 启用 aof 持久化方式
appendonly yes
# 设置保存命令的文件(可以指定路径)
appendfilename appendonly.aof

# appendfsync always/everysec/no
#   - always 每次写命令立即强制写入磁盘，最慢但保证完全持久化，不推荐
#   - everysec 每秒钟强制写入磁盘一次，在性能和持久化间折中，推荐
#   - no 完全依赖os，性能最好，持久化没保证
appendfsync everysec
```

**aof文件的重写**

每个命令重写一次aof，频繁操作某个key导致aof文件很大。例如，当执行多次incr  number操作，aof 文件中会保存多条incr number命令，可以对aof文件重写，把重复命令压缩成一条命令，如执行10次incr number 压缩成set number 11。

```
# 方式一：在登录状态
> bgrewriteaof
# 方式二：在没有登录状态
$ ./redis-cli bgrewriteaof
```

![](https://raw.githubusercontent.com/csxiaoyaojianxian/ImageHosting/master/img/sign.jpg)