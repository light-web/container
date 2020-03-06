<?php

namespace zeni18\container\build;

use Closure;
use ReflectionClass;

/**
 * Class Container Core.
 */
class Core implements \ArrayAccess
{
    /**
     * @var array
     */
    public $bindings = [];

    /**
     * @var array
     */
    public $instances = [];

    /**
     * @var array
     */
    public $items = [];

    /**
     * @param $name
     *
     * @return null|mixed
     */
    public function __get($name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        if (isset($this->bindings[$name])) {
            return $this->make($name);
        }
        if (isset($this->items[$name])) {
            return $this->items[$name];
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * @param $name
     * @param Closure $closure
     * @param bool    $share
     *
     * @return object
     */
    public function bind($name, Closure $closure, $share = false)
    {
        $this->bindings[$name] = compact('closure', 'share');

        return $this;
    }

    /**
     * @param $name
     * @param $object
     */
    public function instance($name, $object)
    {
        $this->instances[$name] = $object;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function make($name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        //获得实现提供者
        $closure = $this->getClosure($name);
        //创建实例
        $object = $this->build($closure);

        //cache实例
        if (isset($this->bindings[$name]['share']) && $this->bindings[$name]['share']) {
            $this->instances[$name] = $object;
        }

        return $object;
    }

    /**
     * @param $className
     *
     * @return mixed
     */
    public function build($className)
    {
        //闭包直接执行并且传入container实例
        if ($className instanceof Closure) {
            return $className($this);
        }

        //获取类信息
        $reflect = new ReflectionClass($className);

        //检查类是否可以实例化
        if (!$reflect->isInstantiable()) {
            throw new ContainerException(sprintf('$s: 不能实例化', $className));
        }

        //获取类的构造函数
        $constructor = $reflect->getConstructor();

        //没有构造类直接实例化
        if (is_null($constructor)) {
            return $reflect->newInstance();
        }

        //获取构造函数参数
        $parameters = $constructor->getParameters();

        $dependencies = $this->getDependencies($parameters);

        return $reflect->newInstanceArgs($dependencies);
    }

    /**
     * 执行函数自动注入参数.
     *
     * @param $name
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function callFunction($name)
    {
        $reflectFunction = new \ReflectionFunction($name);

        $parameters = $reflectFunction->getParameters();

        $dependencies = $this->getDependencies($parameters);

        return $reflectFunction->invokeArgs($dependencies);
    }

    /**
     * 执行方法自动注入参数.
     *
     * @param $class
     * @param $method
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function callMethod($class, $method)
    {
        $reflectMethod = new \ReflectionMethod($class, $method);

        $parameters = $reflectMethod->getParameters();

        $dependencies = $this->getDependencies($parameters);

        return $reflectMethod->invokeArgs($this->build($class), $dependencies);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @param ServiceProviderInterface $provider
     *
     * @return $this
     */
    public function register(ServiceProviderInterface $provider)
    {
        $provider->register($this);

        return $this;
    }

    /**
     * 获得实例实现.
     *
     * @param $name  创建实例方式：类名或者闭包
     *
     * @return mixed
     */
    private function getClosure($name)
    {
        return isset($this->bindings[$name]) ? $this->bindings[$name]['closure'] : $name;
    }

    /**
     * 递归解析参数.
     *
     * @param $parameters
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    private function getDependencies($parameters)
    {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            //获取参数类型
            $dependency = $parameter->getClass();

            if (is_null($dependency)) {
                $denpendencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->build($dependency->name);
            }
        }

        return $dependencies;
    }

    /**
     * 提取默认参数.
     *
     * @param \ReflectionParameter $parameter
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    private function resolveNonClass(\ReflectionParameter $parameter)
    {
        //有默认值则返回默认值
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new ContainerException('参数无默认值');
    }
}
