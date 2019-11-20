<?php

if (!function_exists('echo_json')) {
    function echo_json($data) {
        echo json_encode($data);
        exit;
    }
}

function classLoader($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $kernel_path = __DIR__ . DIRECTORY_SEPARATOR . 'kernel' . DIRECTORY_SEPARATOR . $path . '.php';
    if (file_exists($kernel_path)) {
        require_once $kernel_path;
    }
    $db_path = __DIR__ . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . $path . '.php';
    if (file_exists($db_path)) {
        require_once $db_path;
    }
}

spl_autoload_register('classLoader');

/*
框架开发中的收获

命名空间
    声名命名空间必须占在代码最前面
    声名命名空时不能以 \ 开头
    \ 是指全局的，use 没有命名空间的类时，加不加 \ 都是一样的，因为没有命名空间就是全局的
    new一个有命名空间时的类时会把命名空间带上，这样可以利用命名空间来自己加载不同目录的类
    代码中用了命名空间那么它将只会在自己的空间中找相应的类，想要用其它地方的类就需要 use；没有用命名空间，就会在全局找相应的类
    例如，路由目录中的路由文件没有命名空间，就会自动从框架目录中加载，如果加上命名空间的话就找不到了。加上命名空间又想找到Route，那么就需要 use Route

路径
    php的执行路径是相对系统路径而言的，并不是相对项目路径

 * */