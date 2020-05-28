<?php

class Route
{
    // string 分组路由前缀，如果前缀是null那么注册路由时直接加到$lines里，反之
    public static $prefix;
    // array 分组路由的中间件
    public static $group_middleware = [];
    // 分组路由的target
    public static $group_target;
    // array 所有的路由
    public static $lines = [];
    // array 路由经过的中间件
    public $middleware = [];
    // string|function 路由到达的地方
    public $target;
    // string 路由在$lines中的index
    public $index;
    // string 路由方式
    public $method;

    public static function group($config = array('prefix' => '/', 'middleware' => [], 'to' => null))
    {
        if (!empty($config['prefix'])) {
            self::$prefix = trim($config['prefix'], '/');
        }
        if (!empty($config['middleware'])) {
            self::$group_middleware = $config['middleware'];
        }
        if (!empty($config['to'])) {
            self::$group_target = $config['to'];
        }
        return new self();
    }

    public static function add()
    {
        $items = func_get_args();
        foreach ($items as $item) {
            $index = trim(self::$prefix . '/' . $item->index, '/');
            $item->index = $index;
            $item->middleware(self::$group_middleware);
            if (self::$group_target) {
                $item->to(self::$group_target);
            }
            if ('ANY' === $item->method) {
                self::$lines['GET'][$index] = $item;
                self::$lines['POST'][$index] = $item;
            } else {
                self::$lines[$item->method][$index] = $item;
            }
        }
        // 为了不影响后面的路由注册，分组完成后清空
        self::$prefix = null;
        self::$group_middleware = [];
        self::$group_target = null;
    }

    public static function get($uri)
    {
        $index = trim($uri, '/');
        $index = empty($index) ? '/' : $index;
        $route = new self();
        $route->index = $index;
        if (is_null(self::$prefix)) {
            self::$lines['GET'][$index] = $route;
        }
        $route->method = 'GET';
        return $route;
    }

    public static function post($uri)
    {
        $index = trim($uri, '/');
        $index = empty($index) ? '/' : $index;
        $route = new self();
        $route->index = $index;
        if (is_null(self::$prefix)) {
            self::$lines['POST'][$index] = $route;
        }
        $route->method = 'POST';
        return $route;
    }

    public static function any($uri)
    {
        $index = trim($uri, '/');
        $index = empty($index) ? '/' : $index;
        $route = new self();
        $route->index = $index;
        if (is_null(self::$prefix)) {
            self::$lines['GET'][$index] = $route;
            self::$lines['POST'][$index] = $route;
        }
        $route->method = 'ANY';
        return $route;
    }

    /**
     * 路由经过哪里
     * @param array $pathway
     * @return object
     */
    public function middleware($pathway)
    {
        $this->middleware = array_merge($this->middleware, $pathway);
        return $this;
    }

    /**
     * 路由目的地
     * @param null $target
     * @return object
     */
    public function to($target)
    {
        $this->target = $target;
        return $this;
    }
}