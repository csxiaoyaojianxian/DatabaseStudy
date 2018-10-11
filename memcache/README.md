# memcache学习笔记

[TOC]

>Write By CS逍遥剑仙
>我的主页: [www.csxiaoyao.com](http://www.csxiaoyao.com/)
>GitHub: [github.com/csxiaoyaojianxian](https://github.com/csxiaoyaojianxian)
>Email: sunjianfeng@csxiaoyao.com
>QQ: [1724338257](https://www.csxiaoyao.cn/blog/index.php/2018/09/18/02-2/wpa.qq.com/msgrd?uin=1724338257&site=qq&menu=yes)

## 1. 安装与连接

```
$ brew search memcache
```

返回结果如下，memcached是服务器，libmemcached是客户端

```
libmemcached    memcache-top    memcached   memcacheq
```

进行安装

```
# 先装服务器
$ brew install memcached
# 查看安装结果
$ which memcached  # /usr/local/bin/memcached
$ memcached -h
# 安装libmemcached客户端
$ brew install libmemcached
```

启动服务

```
# 启动服务器 /usr/local/bin/memcached -d
# 以守护程序形式启动(-d)，分配1GB内存(-m 1024)，指定监听localhost，端口2048
$ /usr/local/bin/memcached -d -m 1024 -l localhost -p 2048
# 客户端访问
$ brew install telnet
$ telnet localhost 2048
```

## 2. 设置与删除数据

**(1) 添加数据**

语法：add key 是否压缩(0|1) 缓存时间(s) 数据长度(byte)

> #### 缓存周期问题
>
> 缓存周期两种设置方式：
>
> (1) 时间间隔(s)，不能超过2592000秒(30天)
> (2) 到期时间戳，必须大于当前时间戳才有效
>
> **注意：**如果缓存周期值设置为0表明此数据永不过期

注意：add时如果键已存在，则添加失败，不会覆盖

```
> add name 0 60 9
> csxiaoyao
> get name
```

**(2) 修改数据**

语法：replace key 0|1 缓存时间 数据长度

注意：replace时如果键不存在，则修改失败

```
> replace name 0 120 8
> sunshine
> get name
```

**(3) 设置数据**

语法：set key 0|1 缓存时间 数据长度

注意：如果键已存在，则修改，如果键不存在，则添加

```
> set name 0 120 8
> sunshine
> get name
```

**(4) 删除数据**

语法：delete  key

语法：flush_all  删除所有缓存项

```
> delete name
> get name
> flush_all
```

## 3. 其他指令(incr、decr、stats)

**(1) incr  增加值**

语法：incr key number

 ```
> set num 0 120 2
> 90
> incr num 10
> get num
 ```

**(2) decr  减少值**

```
> decr num 10
> get num
```

**(3) stats  状态**

```
> stats
...
STAT cmd_get 11					# 执行获取缓存项次数
STAT cmd_set 11					# 执行设置缓存项次数
STAT get_hits 5					# 命中率 = get_hits / cmd_get
STAT curr_items 0				# 当前存在的缓存项个数
STAT total_items 8				# 从启动到现在总共设置的缓存项个数，包括过期的
...
```

## 4. php操作memcached

### 4.1 macos安装php扩展

php作为客户端操作memcached需要安装PHP的memcache扩展

下载稳定版的memcache包，http://pecl.php.net/package/memcache

```javascript
$ tar -xzf memcache-2.2.7.tgz
$ cd memcache-2.2.7
$ phpize
$ ./configure --enable-memcache --with-php-config=/usr/local/opt/php54/bin/php-config --with-zlib-dir
$ make & make install 
```

编辑php.ini文件，加入扩展

```javascript
$ extension = memcache.so
```

重启php-fpm 和nginx

```javascript
$ killall php-fpm



$ /usr/local/opt/php54/sbin/php-fpm -D
$ nginx -s reload
```

### 4.2 数据操作

```
$memcache = new Memcache();
$memcache->connect('localhost','2048');
$memcache->add(键, 值, 是否压缩, 有效期);
$memcache->replace(键, 值, 是否压缩, 有效期);
$memcache->set(键, 值, 是否压缩, 有效期);
$memcache->increment(键, 步长);
$memcache->decrement(键, 步长);
$memcache->get(key);
$memcache->delete(key);
$memcache->flush(void);
$memcache->close();
```

### 4.3 应用

**存储sql查询结果**

注意：sql语句执行的结果数据要小于1MB，且键的长度要小于250字节，数据值的大小要小于1MB

## 5. 数据类型

数据类型分为标量和非标量两大类

标量：内容转换成字符串进行存储

```
$mem->set('string','csxiaoyao',0,120); // "csxiaoyao"
$mem->set('int',100,0,120); // "100"
$mem->set('float',100.100,0,120); // "100.1"
$mem->set('bool1',true,0,120); // "1"
$mem->set('bool2',false,0,120); // ""
```

非标量：序列化后存储到memcached服务器中，保存数据原有类型，获取数据时，再反序列化。序列化与反序列化在memcached客户端的set和get方法中完成，用户无需手动序列化

注意：资源类型不能被合理序列化

```
class Dog{}
$dog1 = new Dog();
$dog1->name = 'liuliu';
$res = fopen('./test.php','r');

$mem->set('array',array(1,2,'one'),0,120); // 返回数组
$mem->set('obj',$dog1,0,120); // 返回对象
$mem->set('res',$res,0,120); // int(0)
$mem->set('null',null,0,120); // NULL
```

> is_scalar() 可以判断是否是标量类型

## 6. 分布式缓存服务的搭建

### 6.1 概述

分布式的memcached集群能够提高性能，而寻址的分布式算法则由memcache客户端实现(php提供的memcached扩展)，利用key确定当前数据的目标操作服务器。

### 6.2 搭建方法

使用$memcache->addServer()方法添加多台memcached服务器

```
$memcache = new Memcache();
$memcache->connect('localhost','1024');
$memcache->connect('localhost','2048');
$memcache->set(键, 值, 是否压缩, 有效期);
var_dump($memcache->get(键));
$memcache->close();
```

## 7. session数据使用memcache

分布式服务器进行负载均衡，导致各台服务器的session零散，不利于用户登录等操作。可以通过配置将session存储到memcache中解决上述问题。

```
; 配置php.ini中session文件的存储方式
; session.save_handler = files
session.save_handler = memcache
; 配置session文件的存储路径，多个用逗号隔开
session.save_path = "tcp://127.0.0.1:1024"
```

具体的逻辑代码常规使用session即可。

```
// 注意:可以使用ini_set()函数设置，仅在当前页面有效
ini_set('session.save_handler','memcache');
ini_set('session.save_path','tcp://127.0.0.1:1024');

session_start();
$_SESSION['name'] = 'csxiaoyao';
echo session_id();
```

session信息存储到memcache以sessionid为键，失效时间与session相同。

## 8. 其他问题

### 8.1 memcache适合于存储的数据类型

(1) 安全性要求不高、允许丢失的数据，因为memcache服务器重启或关机会丢失所有数据

(2) 查询频繁、改动周期长的数据，如热点新闻等

(3) 键值数据量不大(小于1MB的数据)

### 8.2 安全完整性问题

memcache本身不提供认证机制，如果需要限制请求连接，需要通过防火墙等在操作系统上进行限制。常规的memcached服务器部署在内网环境。

### 8.3 缓存失效问题

memcached内部不会监视记录是否过期，而是在get时查看记录的时间戳，检查记录是否过

期，因此不会在过期监视上耗费CPU时间，这种技术被称为lazy expiration。

### 8.4 缓存已满，删除旧数据

算法：LRU，least Recently Used，最近最少使用算法

memcache在插入新数据时，空间不足会删除最不活跃的缓存项。

![](https://raw.githubusercontent.com/csxiaoyaojianxian/ImageHosting/master/img/sign.jpg)