<?php

$files = array(
    'App/Demo/Common/DemoController.php' => <<<T
<?php

namespace App\Demo\Common;

use CController;

class DemoController extends CController {

}
T
, 'App/Demo/Controllers/DefaultController.php' => <<<T
<?php

namespace App\Demo\Controllers;

use App\Demo\Common\DemoController;


class DefaultController extends DemoController {
    public function index () {
        \$this->render(\$this->action);
    }
}
T
, 'App/Demo/views/default/index.php' => <<<T
<?= '<h1>欢迎使用</h1>' ?>
T
, 'App/Demo/views/layouts/main.php' => <<<T
<h1>头<h1>
<?= \$content ?>
<h1>尾<h1>
T
, 'Common/LoginController.php' => <<<T
<?php

namespace Common;

use CController;
use Models\User;

class LoginController extends CController {
    public function login(\$request) {
        \$this->renderWidget('login');
    }
}
T
, 'config/config.php' => <<<T
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
T
, 'config/database.php' => <<<T
<?php

return array(
    'mysql' => array(
        'driver' => 'mysql',
        'host' => '',
        'port' => 3306,
        'username' => '',
        'password' => '',
        'db_name' => '',
        'tablePrefix' => '',
        'charset' => 'utf8',
        'long' => false  //保持连接
    ),
    'rds' => array(
        'driver' => 'mysql',
        'host' => '',
        'port' => 3306,
        'username' => '',
        'password' => '',
        'db_name' => '',
        'tablePrefix' => '',
        'charset' => 'utf8',
        'long' => false  //保持连接
    ),
);
T
, 'entrance/index.php' => <<<T
<?php

require_once __DIR__ . '/../../../src/keen.php';
\$config = require_once '../config/config.php';
Dispatcher::createWebApplication(\$config)->run();
T
, 'entrance/.htaccess' => <<<T
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controllers...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
T
, 'entrance/html/readme.txt' => <<<T
静态文件目录
T
, 'middleware/SayHello.php' => <<<T
<?php

class SayHello {
    public function handle(\$request) {
        echo '<br />';
        echo 'hello', \$request->visitor_addr;
        echo '<br />';
    }
}
T
, 'middleware/SayBye.php' => <<<T
<?php
 
class SayBye {
    public function handle(\$request) {
        echo '<br />';
        echo 'bye bye', \$request->visitor_addr;
        echo '<br />';
    }
}
T
, 'Models/User.php' => <<<T
<?php

namespace Models;

use Model;

/*
 * 公用modules
 * 各app的modules可以创建到各自的目录下
 */

class User extends Model {
    public function tableName() {
        return 'tableName';
    }

    public static function model() {
        \$modelName = __CLASS__;
        return new \$modelName;
    }
}
T
, 'routes/demo.php' => <<<T
<?php

Route::any('/')->to('App\Demo\Controllers\DefaultController@index')->middleware(['SayHello', 'SayBye']);
Route::any('/login')->to('Common\LoginController@login');

Route::group(['prefix' => '/test', 'middleware' => ['SayHello', 'SayBye']])->add(
    Route::get('t1')->to(function () {
        return 't1为get请求';
    })->middleware(['SayHello', 'SayBye']),
    Route::post('t2')->to(function () {
        return 't2为post请求';
    })
);

Route::group(['to' => 'App\Demo\Controllers\DefaultController@index'])->add(
    Route::get('/index'),
    Route::get('/index/index')
);
T
, 'widgets/login.php' => <<<T
<form>
    <div>name:<input type="text"></div>
    <div>pass:<input type="password"></div>
</form>
T
, 'readme.md' => <<<T
约定：有命名空间的目录都大写字母开头
T
);

//############################################################

if (empty($argv[1])) {
    echo '路径不能为空';
    exit;
}
if (empty($argv[2])) {
    echo '项目名字不能为空';
    exit;
}

$project_path = $argv[1];
$project_name = $argv[2];

if (is_dir($project_path . DIRECTORY_SEPARATOR . $project_name)) {
    echo $project_name . ' 已存在，创建失败';
    exit;
}

foreach ($files as $p => $code) {
    $path = dirname($p);
    $final_path = $project_path . DIRECTORY_SEPARATOR . $project_name . DIRECTORY_SEPARATOR . $path;
    if (!is_dir($final_path)) {
        mkdir($final_path, 0777, true);
    }
    $file = basename($p);
    $final_file = $final_path . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($final_file)) {
        touch($final_file);
        $fp = fopen($final_file, 'w');
        fwrite($fp, $code);
        fclose($fp);
    }
}

echo '创建项目' . $argv[2] . '成功';