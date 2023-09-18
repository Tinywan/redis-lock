## 安装

```shell
composer require tinywan/redis-lock
```

## 使用

### 生成令牌

```php
$redis = new Redis();
$redis->connect('127.0.0.1');
$redis->auth('123456');

$lock = new \tinywan\RedisLock($redis);

// 全局唯一标识符
$orderSn = md5(uniqid());
// 检查是否重复投递消息
if ($lock->isRepeatedDeliver($orderSn)) {
    var_dump('消息幂等投注失败');
}

// 开始投递消息
$execResult = $lock->startDeliver($orderSn);
if (false === $execResult) {
    var_dump('消息幂等投注失败, 重复投递消息，直接返回 ');
}

try {
    // 根据业务唯一标识的Key做幂等处理
    sleep(1);
} catch (\Exception $e) {
    var_dump('处理失败 ' . $e->getMessage());
} finally {
    //  使用完毕后删除
    $lock->deleteDeliver($orderSn);
}

var_dump('处理完成');
```