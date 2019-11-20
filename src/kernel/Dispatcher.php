<?php

spl_autoload_register(array('Dispatcher', 'autoloadApp'));


class Dispatcher {
    public $visitor;
    public static $config;
    public static $app;

    private function __construct() {
        $this->visitor = new Visitor();
    }

    public static function createWebApplication($config) {
        if (self::$app instanceof self) {
            return self::$app;
        }
        self::$app = new self();
        self::$config = $config;
        self::$config['root'] = rtrim(self::$config['root'], '/');
        self::$config['routes'] = rtrim(self::$config['routes'], '/');
        self::$config['middleware'] = rtrim(self::$config['middleware'], '/');
        self::$config['widgets'] = rtrim(self::$config['widgets'], '/');
        //导入路由
        $handle = opendir(self::$config['routes']);
        while (($file = readdir($handle)) !== false) {
            $php_file = self::$config['routes'] . DIRECTORY_SEPARATOR . $file;
            if (is_file($php_file) && '.php' == substr($file, -4, 4)) {
                require_once $php_file;
            }
        }
        return self::$app;
    }

    public static function autoloadApp($app_name) {
        require_once self::$config['root'] . DIRECTORY_SEPARATOR . str_replace('\\', '/', trim($app_name, '\\')) . '.php';
    }

    /**
     * 请求到达控制器方法
     * @param $controller
     * @param $action
     */
    private function arrive($controller, $action) {
        $app_path = str_replace('\\', '/', trim($controller, '\\'));
        require_once self::$config['root'] . DIRECTORY_SEPARATOR . $app_path . '.php';
        $handle = new $controller($this->visitor);
        $handle->module = dirname(dirname($app_path));
        $handle->controller = str_replace('controller', '', strtolower(basename($app_path)));
        $handle->action = $action;
        $handle->$action($this->visitor);
    }

    /**
     * 一个请求进入调度器
     * 1、调度器首先问请求去哪
     * 2、调度器知道请求去哪后，判断目的地会经过哪些地方，依次把请求放进去
     */
    public function run() {
        $uri = $this->visitor->where();
        $method = $this->visitor->method;
        //foreach (Route::$lines as $k => $v) {
        //  var_dump($k); //查看所有的路由地址
        //}
        $navigator = empty(Route::$lines[$method][$uri]) ? null : Route::$lines[$method][$uri];
        if (empty($navigator)) {
            header("HTTP/1.1 404 Not Found");
            header("status: 404 Not Found");
            //echo '路由不存在', $uri;
            exit;
        }
        if ($navigator->middleware) {
            foreach ($navigator->middleware as $item) {
                require_once self::$config['middleware'] . DIRECTORY_SEPARATOR . $item . '.php';
                $middleware = new $item();
                $middleware->handle($this->visitor);
            }
        }
        if (is_callable($navigator->target)) {
            $target = $navigator->target;
            echo $target($this->visitor);
        } else {
            list($class_name, $function) = explode('@', $navigator->target);
            $this->arrive($class_name, $function);
        }
        //整个访问结束
    }
}