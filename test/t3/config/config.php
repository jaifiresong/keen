<?php

return array(
    'database' => include __DIR__ . '/database.php',
    /* 以下配置是为了让框知道从哪里加载文件，所以入口文件位置可以自己任意定义
     * 比如以entrance目录为准，可以写相对entrance目录的路径，也可以写绝对路径
     * */
    'root' => '../',                  //根目录位置 dirname(__DIR__)
    'routes' => '../routes',          //路由位置
    'middleware' => '../middleware',  //中间件位置
    'widgets' => '../widgets',        //小部件位置
);