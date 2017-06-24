# MongoDB

## 1 安装

### 1.1 使用 curl 命令下载安装

```shell
# 进入 /usr/local
cd /usr/local
# 下载
sudo curl -O https://fastdl.mongodb.org/osx/mongodb-osx-ssl-x86_64-3.4.5.tgz
# 解压
sudo tar -zxvf mongodb-osx-ssl-x86_64-3.4.5.tgz
# 重命名为 mongodb 目录
sudo mv mongodb-osx-ssl-x86_64-3.4.5.tgz mongodb
```

添加 PATH

```Shell
export PATH=/usr/local/mongodb/bin:$PATH
```

### 1.2 使用 brew 安装

```shell
sudo brew install mongodb
```

如果安装支持 TLS/SSL

```shell
sudo brew install mongodb --with-openssl
```

安装最新开发版

```Shell
sudo brew install mongodb --devel
```

## 2 运行

### 2.1 创建存储目录

```shell
sudo mkdir -p /data/db
```

### 2.2 启动

启动 mongodb，默认数据库目录即为 /data/db：

```shell
sudo mongod
# 如果没有添加 PATH
cd /usr/local/mongodb/bin
sudo ./mongod
```

### 2.3 执行

```shell
cd /usr/local/mongodb/bin 
./mongo
```

## 3 与关系型数据库对比

| RDBMS | MongoDB                     |
| ----- | --------------------------- |
| 数据库   | 数据库                         |
| 表格    | 集合                          |
| 行     | 文档                          |
| 列     | 字段                          |
| 表联合   | 嵌入文档                        |
| 主键    | 主键 (MongoDB 提供了 key 为 _id ) |

## 4 数据类型

| 数据类型               | 描述                                       |
| ------------------ | ---------------------------------------- |
| String             | 字符串。存储数据常用的数据类型。在 MongoDB 中，UTF-8 编码的字符串才是合法的。 |
| Integer            | 整型数值。用于存储数值。根据你所采用的服务器，可分为 32 位或 64 位。   |
| Boolean            | 布尔值。用于存储布尔值（真/假）。                        |
| Double             | 双精度浮点值。用于存储浮点值。                          |
| Min/Max keys       | 将一个值与 BSON（二进制的 JSON）元素的最低值和最高值相对比。      |
| Arrays             | 用于将数组或列表或多个值存储为一个键。                      |
| Timestamp          | 时间戳。记录文档修改或添加的具体时间。                      |
| Object             | 用于内嵌文档。                                  |
| Null               | 用于创建空值。                                  |
| Symbol             | 符号。该数据类型基本上等同于字符串类型，但不同的是，它一般用于采用特殊符号类型的语言。 |
| Date               | 日期时间。用 UNIX 时间格式来存储当前日期或时间。你可以指定自己的日期时间：创建 Date 对象，传入年月日信息。 |
| Object ID          | 对象 ID。用于创建文档的 ID。                        |
| Binary Data        | 二进制数据。用于存储二进制数据。                         |
| Code               | 代码类型。用于在文档中存储 JavaScript 代码。             |
| Regular expression | 正则表达式类型。用于存储正则表达式。                       |



**By CS逍遥剑仙**

[www.csxiaoyao.con](www.csxiaoyao.con)