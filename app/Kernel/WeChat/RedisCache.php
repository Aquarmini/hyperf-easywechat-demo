<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Kernel\WeChat;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class RedisCache implements CacheInterface
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function __construct(ContainerInterface $container)
    {
        $this->redis = $container->get(\Redis::class);
    }

    public function get($key, $default = null)
    {
        $res = $this->redis->get($key);
        return json_decode($res, true);
    }

    public function set($key, $value, $ttl = null)
    {
        $value = json_encode($value);
        return $this->redis->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        return $this->redis->delete($key);
    }

    public function clear()
    {
        throw new BusinessException(ErrorCode::SERVER_ERROR, 'clear is not support!');
    }

    public function getMultiple($keys, $default = null)
    {
        throw new BusinessException(ErrorCode::SERVER_ERROR, 'getMultiple is not support!');
    }

    public function setMultiple($values, $ttl = null)
    {
        throw new BusinessException(ErrorCode::SERVER_ERROR, 'setMultiple is not support!');
    }

    public function deleteMultiple($keys)
    {
        throw new BusinessException(ErrorCode::SERVER_ERROR, 'deleteMultiple is not support!');
    }

    public function has($key)
    {
        return $this->redis->exists($key);
    }
}
