<?php
/**
 * @desc RedisLock.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2023/9/18 20:38
 */
declare(strict_types=1);


namespace tinywan;

class RedisLock
{
    /**
     * @var \Redis $_redis
     */
    private $_redis;

    /**
     * RedisLock constructor.
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->_redis = $redis;
    }

    /**
     * @desc 投递
     * @param string $unique
     * @param string $value
     * @param int $expire
     * @return bool
     * @author Tinywan(ShaoBo Wan)
     */
    public function startDeliver(string $unique, string $value = 'true', int $expire = 1200): bool
    {
        $scriptShaKey = 'REDIS:LOCK:SCRIPT:SHA';
        $scriptSha = $this->_redis->get($scriptShaKey);
        if (!$scriptSha) {
            $script = <<<tinywan
        if redis.call('SETNX', KEYS[1], ARGV[1]) == 1 then
            redis.call('EXPIRE', KEYS[1], ARGV[2])
            return true
        else
            return false
        end    
tinywan;
            $scriptSha = $this->_redis->script('load', $script);
            $this->_redis->set($scriptShaKey, $scriptSha);
        }
        return (bool)$this->_redis->evalSha($scriptSha, [$unique, $value, $expire], 1);
    }

    /**
     * @desc 是否重复投递消息
     * @param string $unique
     * @return bool
     * @author Tinywan(ShaoBo Wan)
     */
    public function isRepeatedDeliver(string $unique): bool
    {
        return (bool) $this->_redis->exists($unique);
    }

    /**
     * @desc: 删除投递
     * @param string $unique
     * @return int
     * @author Tinywan(ShaoBo Wan)
     */
    public function deleteDeliver(string $unique): int
    {
        return $this->_redis->del($unique);
    }

    /**
     * @desc: 静态调用
     * @param string $method
     * @param $arguments
     * @return mixed
     * @author Tinywan(ShaoBo Wan)
     */
    public static function __callStatic(string $method, $arguments)
    {
        var_dump($method, $arguments);
//        return self::driver()->{$method}(...$arguments);
    }
}