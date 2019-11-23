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
        $modelName = __CLASS__;
        return new $modelName;
    }
}