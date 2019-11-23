## 框架目录结构
- createProject.php    # 项目创建脚本，预定义一个简单的demo
- keen.php             # 框架入口文件，项目将从这个文件导入框架
- db/
    - Connection.php   # 数据库连接，同样的连接只会有一次
    - Constructor.php  # 查询构造器
    - Model.php        # 数据model，每一条数据就是一个model实例，属性可用数组的形式存取
- kernel/
    - ArrayForm.php    # 请求的表单将会是ArrayForm的实例
    - CController.php  # 主控制器，提供render方法
    - Dispatcher.php   # 调度器，由调度器来分配请求该何去何从
    - Route.php        # 路由管理类，所有的路由都存在这个类里面，由调度器来取
    - Visitor.php      # 访客会成为这个类的实例，提url解析和登录登出方法


## 运行流程
1. 一个http请求(Visitor)到达站点后，使用调度器（Dispatcher）创建一个webApp并导入配置。Dispatcher使用了单例模式，所以一个Dispatcher服务一个Visitor，真正的一对一VIP服务。
2. 调度器首先把这个请求实例化成一个Visitor
3. 调度器导入所有的路由（Route）
4. 调度器会问Visitor想要去的目的地，然后更据目的地找出相应的路由
5. 得到路由之后，调度器会根据路由判断途经的地点（middleware），并把Visitor依次放进去
6. Visitor最终来到目的地，通常一个控制器方法


## 创建项目

```
php createProject.php project_path project_name # 第一个参数为项目路径，第二个参数为项目名称
```

## 项目目录结构
- App/
    - Demo/Controllers/   # 模块控制器目录
    - Demo/views/demo     # 模块视图目录
    - Demo/views/layouts  # 模块视图布局目录
- Common/                 # 建议公共方法目录
- config/                 
    - config.php          # 配置文件
    - database.php        # 数据库配置
- entrance/               # 建议入口文件目录
- middleware/             # 默认中间件目录，可在配置中修改
- Models/                 # 建议公共model目录
- routes/                 # 默认路由目录，可在配置中修改
- widgets/                # 默认网页部件目录，可在配置中修改


## 路由
- 同时注册get,post请求
```
# 注册根目录到DefaultController的index方法，经过中间件SayHello，SayBye
Route::any('/')->to('App\Demo\Controllers\DefaultController@index')->middleware(['SayHello', 'SayBye']);
```

- 路由分组
```
# group 接收一个数组，三元素当作参数：prefix=>公共地址，middleware=>中间件，to=>目的地
Route::group(['prefix' => '/test', 'middleware' => ['SayHello', 'SayBye']])->add(
    Route::get('t1')->to(function () {
        return 't1为get请求';
    })->middleware(['SayHello', 'SayBye']),
    Route::post('t2')->to(function () {
        return 't2为post请求';
    })
);
```

- 多个路由指向一个位置
```
# add方法里面的路由to方法将失效
Route::group(['to' => 'App\Demo\Controllers\DefaultController@index'])->add(
    Route::get('/index'),
    Route::get('/index/index')
);
```

## 中间件
> 中间件约定需要一个handle方法，调度器将会调用这个方法，并传入一个访问实例

## 目的地
> 目的地通常是一个控制器方法，CController提供了render方法方便渲染视图文件

```
# render方法接收两个参数，1、视图名称，2、需要用到的变量数组
renderFile      # 返回渲染后的字符串
renderPartial   # 渲染视图，不带布局
render          # 渲染视图，带布局，布局文件中约定写<?= $content ?>
renderWidget    # 渲染公共网页元素
renderCustom    # 渲染自定义文件
```

## 数据库操作
> 首先在配置文件中配置好数据库信息

1. 使用model

```
<?php

namespace Models;

use Model;

/*
 * 必须实现下面两个方法
 */

class User extends Model {
    public $conf = 'rds'; // 更改$conf来连接不同的数据库，$conf的值和配置文件中配置的数据库键名一样
    
    # 返回数据表名
    public function tableName() {
        return 'tableName';
    }
    
    # 返回一个实例
    public static function model() {
        $modelName = __CLASS__;
        return new $modelName;
    }
}

$model = new User();

// 分页paginate 
$model->paginate($pageSize, $condition, $params)
# pageSize 每页展示数量
# condition 查询条件
# params 预处理参数

// 根据主键查询findByPk 
$model->findByPk($id);
# id 主键

// 查询一条数据find
$model->find($condition, $params)
# condition 查询条件 可以传入数组如：{field=>'*',condition=>'1',group=>'id',order=>'id DESC',having=>'id < 10',limit=>'2'}
# params 预处理参数

// 查询全部数据findAll
$model->findAll($condition, $params)
# condition 查询条件 可以传入数组如：{field=>'*',condition=>'1',group=>'id',order=>'id DESC',having=>'id < 10',limit=>'2'}
# params 预处理参数

// 统计count
$model->count($condition, $params)
# condition 查询条件
# params 预处理参数

// 更新update
$model->update($data, $condition, $params)
# data 关联数组
# condition 查询条件
# params 预处理参数

// 删除delete
$model->delete($condition, $params)
# condition 查询条件
# params 预处理参数

// save方法会根据主键值来自动判断是更新数据还是插入数据
$model->save()
```

2. 使用查询构造器（Constructor）
```
Constructor::connection()  // connection接收数据库配置来连接不同的数据库，值和配置文件中配置的数据库键名一样
    ->createCommand() // createCommand可以接收一个sql语句，当传入sql语句时后面的构造方法将不会运行，直接运行query方法
    ->select($field)
    ->from($tableName)
    ->where($where, $prepare)
    ->order($order)
    ->limit($limit)
    ->queryAll(); // queryAll返回一个二维数据  queryRow返回一个一维数据 query返回执行情况
```