<?php
// 有效时间，0 代表永久有效，单位 s
define('REDIS_TTL', 15);
error_reporting(E_ALL);
ini_set('display_errors','On');

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', '6379');
define('REDIS_AUTH', '');

class MyRedis{
    private static $handler;
    private static function handler(){
        if(!self::$handler){
            self::$handler = new Redis();
            self::$handler -> connect(REDIS_HOST,REDIS_PORT);
            self::$handler -> auth(REDIS_AUTH);
        }
        return self::$handler;
    }
    public static function get($key){
        $value = self::handler() -> get($key);
        $value_serl = @unserialize($value);
        if(is_object($value_serl)||is_array($value_serl)){
            return $value_serl;
        }
        return $value;
    }
    public static function set($key,$value,$timeDiff=0){
        if(is_object($value)||is_array($value)){
            $value = serialize($value);
        }
        if(REDIS_TTL){
            return self::handler() -> setex($key,REDIS_TTL+$timeDiff,$value);
        }else{
            return self::handler() -> set($key,$value);
        }
    }
}

$redisKey = "sunshine";
$result = ["csxiaoyao","csxiaoyao"];
MyRedis::set($redisKey, $result);
var_dump(MyRedis::get($redisKey));