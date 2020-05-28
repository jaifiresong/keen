<?php


class Visitor
{
    //session name
    const VAR_USER_SESSION_NAME = 'session57f417b438b12da673e661e998e5bb92';
    //REQUEST_METHOD: "GET",
    public $method;
    //HTTP_HOST: "phpstudy.my",
    public $domain;
    //REMOTE_ADDR: "127.0.0.1",
    public $visitor_addr;
    //QUERY_STRING: "x=1&b=2",
    public $query_raw;
    //REQUEST_URI: "/nn/?x=1&b=2",
    public $uri;
    //PHP_SELF: "/nn/index.php", # 程序本身
    public $php_self;
    //###########↓服务器信息↓###########
    //REQUEST_SCHEME: "http",
    public $scheme;
    //SERVER_PORT: "80",
    public $server_port;
    //SERVER_ADDR: "127.0.0.1",
    public $server_addr;
    //DOCUMENT_ROOT: "E:\songcj.com\www\test", # 项目绝对路径
    public $base_path;
    //SCRIPT_FILENAME: "E:\songcj.com\www\test/nn/index.php",# 被执行程序绝对路径
    public $script_path;
    //###########↓request data↓###########
    public $query;
    public $post;
    public $input;
    //###########↓登录信息↓###########
    private $_id; //注意不能用empty函数来判断

    public function __construct()
    {
        // visitor info
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->domain = $_SERVER['HTTP_HOST'];
        $this->visitor_addr = $_SERVER['REMOTE_ADDR'];
        $this->query_raw = $_SERVER['QUERY_STRING'];
        $this->uri = $_SERVER['REQUEST_URI'];
        // server info
        $this->scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
        $this->server_port = $_SERVER['SERVER_PORT'];
        $this->server_addr = $_SERVER['SERVER_ADDR'];
        $this->base_path = $_SERVER['DOCUMENT_ROOT'];
        $this->script_path = $_SERVER['SCRIPT_FILENAME'];
        $this->php_self = $_SERVER['PHP_SELF'];
        // params
        $this->query = new ArrayForm($_GET);
        $this->post = new ArrayForm($_POST);
        $this->input = new ArrayForm(array_merge($_GET, $_POST));
        # 登录
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!empty($_SESSION[Visitor::VAR_USER_SESSION_NAME])) {
            $this->_id = $_SESSION[Visitor::VAR_USER_SESSION_NAME];
        }
    }

    /**
     * 当取得不存在的或私有的属性时调用
     * @param string $attribute # 属性名称
     * @return object
     */
    public function __get($attribute)
    {
        if ('id' == $attribute) {
            return $this->_id;
        }
        return $this->$attribute;
    }


    /**
     * 当设置不存在或私有的属性时调用，注意：当同一个对象的某个属性第一次__set过后，再调用的时候__set是不运行的
     * @param string $attribute
     * @param string $value
     */
    public function __set($attribute, $value)
    {
        //代码追踪调试
        //$backtrace = debug_backtrace();
        //var_dump($backtrace);
        $this->$attribute = $value;
        if ('id' == $attribute) {
            //更新session信息
            $this->login($value);
        }
    }

    public function login($user_id)
    {
        //session是可以存数组的
        $_SESSION[Visitor::VAR_USER_SESSION_NAME] = $user_id;
        $this->_id = $user_id;
    }

    public function logout()
    {
        $_SESSION[Visitor::VAR_USER_SESSION_NAME] = null;
    }

    public function isAjaxRequest()
    {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 路由地址
     */
    public function where()
    {
        /*
         * "/index.php"
         * "/1/2/3/4/5?a=1&b=2&c=3"
         */
        $uri = str_replace($this->php_self, '', $this->uri); // php_self是入口文件，需要把它去掉
        $uri = trim($uri, '/');
        $route = explode('?', $uri);
        return empty($route[0]) ? '/' : $route[0];
    }

    /**
     * 站点相对目录：站点到入口文件之间的路径
     * uri:标识符 不可重复，像身份证编号一样
     * url:定位符 可重复，像名字一样
     */
    public function base_uri()
    {
        $path = trim(dirname($this->php_self), '/');
        $path = trim($path, '\\');
        return $path;
    }

    /**
     * 当前URL
     * @param array $params
     * @return string
     */
    public function getUrl($params = array())
    {
        if ($params) {
            return '/' . trim($this->where(), '/') . '?' . http_build_query($params);
        }
        return '/' . trim($this->where(), '/');
    }

    /**
     * 完整URL
     */
    public function fullUrl()
    {
        return $this->scheme . '://' . $this->domain . $this->uri;
    }

}
