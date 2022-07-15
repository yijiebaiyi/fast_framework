<?php
declare (strict_types=1);

namespace fast;


use fast\helper\Arr;
use ReflectionException;

class Container
{
    /**
     * 注入类的参数
     * @var array
     */
    private array $_params = [];

    /**
     * 当前容器生成的对象
     * @var array
     */
    private array $_objects = [];

    /**
     * 当前容器依赖
     * @var array
     */
    private array $_dependencies = [];

    /**
     * 注入类的反射类
     * @var array
     */
    private array $_reflections = [];

    /**
     * 获取容器中的实例
     * @param string $className
     * @param array $params
     * @return object
     * @throws Exception
     * @throws ReflectionException
     */
    public function get(string $className, array $params = []): object
    {
        if (isset($this->_objects[$className])) {
            return $this->_objects[$className];
        }

        if (isset($this->_params[$className])) {
            $this->_params[$className] = Arr::arrayMergeBase($this->_params[$className], $params);
        } else {
            $this->_params[$className] = $params;
        }
        return $this->build($className, $this->_params[$className]);
    }

    /**
     * 将类注入到容器
     * @param string $className
     * @param array $params
     * @return $this
     */
    public function set(string $className, array $params = []) :self
    {
        $this->_params[$className] = $params;
        return $this;
    }

    /**
     * 创建对象
     * @param string $className
     * @param array $params
     * @return object|null
     * @throws Exception
     * @throws ReflectionException
     */
    public function build(string $className, array $params = []): ?object
    {
        if (isset($this->_reflections[$className])) {
            $reflection = $this->_reflections[$className];
        } else {
            try {
                $reflection = new \ReflectionClass($className);
            } catch (ReflectionException $exception) {
                throw new Exception("Failed to reflect class " . $className . ", error: " . $exception->getMessage());
            }
            $this->_reflections[$className] = $reflection;
        }

        if (!$reflection->isInstantiable()) {
            throw new Exception("Is not instantiable:" . $reflection->name);
        }

        $dependencies = [];
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            $constructorParameters = $constructor->getParameters();
            foreach ($constructorParameters as $param) {
                if (version_compare(PHP_VERSION, '5.6.0', '>=') && $param->isVariadic()) {
                    break;
                } elseif ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    $c = $param->getClass();
                    $dependencies[] = $this->get($c->getName(), $this->_params[$c->getName()] ?? []);
                }
            }
        }

        $this->_dependencies[$className] = Arr::arrayMergeBase($dependencies, $params);
        $object = $reflection->newInstanceArgs($this->_dependencies[$className]);
        $this->_objects[$className] = $object;
        return $object;
    }
}