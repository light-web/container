<?php

namespace zeni18\container;

use zeni18\container\build\Core;

/**
 * Class Container.
 */
class Container
{
    //连接的驱动
    /**
     * @var null
     */
    protected static $link = null;

    /**
     * 类静态方法调用.
     *
     * @param $name
     * @param $arguments
     * @param mixed $method
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([static::single(), $method], $arguments);
    }

    /**
     * 实例调用方法.
     *
     * @param $name
     * @param $arguments
     * @param mixed $method
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([static::single(), $method], $arguments);
    }

    /**
     * 单例化.
     */
    public static function single()
    {
        if (is_null(static::$link)) {
            static::$link = new  Core();
        }

        return static::$link;
    }
}
