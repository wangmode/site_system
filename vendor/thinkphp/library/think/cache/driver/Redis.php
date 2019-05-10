<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\cache\driver;

use think\cache\Driver;

/**
 * Redis缓存驱动，适合单机部署、有前端代理实现高可用的场景，性能最好
 * 有需要在业务层实现读写分离、或者使用RedisCluster的需求，请使用Redisd驱动
 *
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 * @author    尘缘 <130775@qq.com>
 */
class Redis extends Driver
{
    protected $options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '12345',
        'select'     => 0,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
        'serialize'  => true,
    ];

    /**
     * 架构函数
     * @access public
     * @param  array $options 缓存参数
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        if (extension_loaded('redis')) {
            $this->handler = new \Redis;

            if ($this->options['persistent']) {
                $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
            } else {
                $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
            }

            if ('' != $this->options['password']) {
                $this->handler->auth($this->options['password']);
            }

            if (0 != $this->options['select']) {
                $this->handler->select($this->options['select']);
            }
        } elseif (class_exists('\Predis\Client')) {
            $params = [];
            foreach ($this->options as $key => $val) {
                if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication', 'parameters'])) {
                    $params[$key] = $val;
                    unset($this->options[$key]);
                }
            }

            if ('' == $this->options['password']) {
                unset($this->options['password']);
            }

            $this->handler = new \Predis\Client($this->options, $params);

            $this->options['prefix'] = '';
        } else {
            throw new \BadFunctionCallException('not support: redis');
        }
    }

    /**
     * 判断缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return $this->handler->exists($this->getCacheKey($name));
    }

    /**
     * 读取缓存
     * @access public
     * @param  string $name 缓存变量名
     * @param  mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $this->readTimes++;

        $value = $this->handler->get($this->getCacheKey($name));

        if (is_null($value) || false === $value) {
            return $default;
        }

        return $this->unserialize($value);
    }

    /**
     * 写入缓存
     * @access public
     * @param  string            $name 缓存变量名
     * @param  mixed             $value  存储数据
     * @param  integer|\DateTime $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        $this->writeTimes++;

        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }

        if ($this->tag && !$this->has($name)) {
            $first = true;
        }

        $key    = $this->getCacheKey($name);
        $expire = $this->getExpireTime($expire);

        $value = $this->serialize($value);

        if ($expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
        }

        isset($first) && $this->setTagItem($key);

        return $result;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $this->writeTimes++;

        $key = $this->getCacheKey($name);

        return $this->handler->incrby($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $this->writeTimes++;

        $key = $this->getCacheKey($name);

        return $this->handler->decrby($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        $this->writeTimes++;

        return $this->handler->del($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param  string $tag 标签名
     * @return boolean
     */
    public function clear($tag = null)
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->getTagItem($tag);

            $this->handler->del($keys);

            $tagName = $this->getTagKey($tag);
            $this->handler->del($tagName);
            return true;
        }

        $this->writeTimes++;

        return $this->handler->flushDB();
    }

    /**
     * 缓存标签
     * @access public
     * @param  string        $name 标签名
     * @param  string|array  $keys 缓存标识
     * @param  bool          $overlay 是否覆盖
     * @return $this
     */
    public function tag($name, $keys = null, $overlay = false)
    {
        if (is_null($keys)) {
            $this->tag = $name;
        } else {
            $tagName = $this->getTagKey($name);
            if ($overlay) {
                $this->handler->del($tagName);
            }

            foreach ($keys as $key) {
                $this->handler->sAdd($tagName, $key);
            }
        }

        return $this;
    }

    /**
     * 更新标签
     * @access protected
     * @param  string $name 缓存标识
     * @return void
     */
    protected function setTagItem($name)
    {
        if ($this->tag) {
            $tagName = $this->getTagKey($this->tag);
            $this->handler->sAdd($tagName, $name);
        }
    }

    /**
     * 获取标签包含的缓存标识
     * @access protected
     * @param  string $tag 缓存标签
     * @return array
     */
    protected function getTagItem($tag)
    {
        $tagName = $this->getTagKey($tag);
        return $this->handler->sMembers($tagName);
    }

    /**
     * 将一个或多个值插入到列表头部
     * @param $key
     * @param $value
     * @return int
     */
    public function LPush($key, $data = null)
    {
        $data = $this->serialize($data);
        return $this->handler->lPush($key, $data);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public function LPop($key)
    {
        return $this->unserialize($this->handler->lPop($key));

    }

    /**
     * 获取列表的个数
     * @param string $key
     * @return string
     */
    public function Llen($key)
    {
        return $this->handler->llen($key);
    }

    /**
     * 删除指定字符
     * @param $key
     * @param $count
     * @param $value
     * @return mixed
     */
    public function Lrem($key,$value,$count)
    {
        return $this->handler->lrem($key,$value,$count);
    }

    /**
     * heah 储存
     * @param $key
     * @param $hashKey
     * @param $value
     * @return mixed
     */
    public function hset($key,$hashKey,$value)
    {
        $value = $this->serialize($value);
        return $this->handler->hset($key,$hashKey,$value);
    }

    /**
     * heah 获取所有数据
     * @param $key
     * @return mixed
     */
    public function hGetAll($key)
    {
        return $this->unserialize($this->handler->hGetAll($key));
    }

    /**
     * heah 获取key中的数量
     * @param $key
     * @return mixed
     */
    public function hlen($key)
    {
        return $this->handler->hlen($key);
    }

    /**
     * 删除指定key
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        return $this->handler->del($key);
    }

    /**
     * 查询指定范围的值
     * @param $key
     * @param $start
     * @param $end
     * @return mixed
     */
    public function lrange($key,$start,$end)
    {
        $data = $this->handler->lrange($key,$start,$end);
        foreach ($data as $key => $val){
            $newData[$key] = $this->unserialize($val);
        }
        return $newData;
    }

    /**
     * 删除指定范围的值
     * @param $key
     * @param $start
     * @param $end
     * @return mixed
     */
    public function ltrim($key,$start,$end)
    {
        return $this->handler->ltrim($key,$start,$end);
    }

    /**
     * 数据自增
     * @param $key
     * @return mixed
     */
    public function incr($key)
    {
        return $this->handler->incr($key);
    }
}
