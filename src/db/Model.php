<?php


abstract class Model extends Connection implements ArrayAccess {

    public $tableName;
    private $data;    //每条数据都是一个对象，具体键值放在data里面
    private $PK;      //表主键
    public $pagingName = 'page';  // 分布变量名称

    public function __construct() {
        $this->connect();
        $this->tableName = $this->tableName();
        //查询主键
        $statement = $this->db->query('DESC ' . $this->tableName);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        while ($row = $statement->fetch()) {
            if ('PRI' === $row['Key']) {
                $this->PK = $row['Field'];
                //一个表可能有多个主键，但只用第一个主键作主键
                break;
            }
        }
    }

    /**
     * 设置模型的表名
     * return 'tableName';
     */
    abstract protected function tableName();

    /**
     * 子类需重写该方法
     * $modelName = __CLASS__;
     * return new $modelName;
     */
    abstract public static function model();

    /* 下面两个方法用来存取对象属性 */

    public function __get($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    public function __set($offset, $value) {
        $this->data[$offset] = $value;
    }

    /* 下面4个方法是必须实现的ArrayAccess接口 */

    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * 发送查询,预处理方式执行
     * @param string $sqlStr
     * @param array $params example array(':id'=>'1')
     * @return object PDOStatement对象
     */
    private function query_exec($sqlStr, $params) {
        $prepare = $this->db->prepare($sqlStr);
        foreach ($params as $field => $value) {
            $prepare->bindParam($field, $value);
        }
        $prepare->execute();
        return $prepare;
    }

    public function paging($name) {
        $this->pagingName = $name;
        return $this;
    }

    /* ---------------------------------------------------以下为查询方法--------------------------------------------------- */

    /**
     * 数据分面
     * @param int $pageSize 第页条数
     * @param $condition
     * @param $params
     * @return array
     */
    public function paginate($pageSize = 10, $condition = '1', $params = array()) {
        if (is_array($condition)) {
            $where_str = $condition['condition'];
        } else {
            $where_str = $condition;
            $condition = array();
            $condition['condition'] = $where_str;
        }
        $paging = array(
            'pageSize' => $pageSize,//每页显示条数
            'total' => $this->count($where_str, $params),//数据条数
            'currentPage' => 1,//当前页
            'pageCount' => 0,//页数
            'pagingName' => $this->pagingName
        );
        if (Dispatcher::$app->visitor->query[$this->pagingName]) {
            $paging['currentPage'] = intval(Dispatcher::$app->visitor->query[$this->pagingName]);
        }
        $paging['pageCount'] = ceil($paging['total'] / $paging['pageSize']);
        $start = ($paging['currentPage'] - 1) * $pageSize;
        $condition['limit'] = "$start,$pageSize";
        $data = $this->findAll($condition, $params);
        return array('data' => $data, 'paging' => $paging);
    }

    public function findByPk($pk) {
        if (!empty($this->PK)) {
            $data = $this->find("{$this->PK} = $pk");
            //$r = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);  //生成一个能用对象又能用数组方式访问的数据
            return $data;
        }
        return null;
    }

    public function find($condition = '1', $params = array()) {
        $data = $this->findAll($condition, $params);
        if (empty($data)) {
            return null;
        }
        return reset($data);
    }

    /**
     * 查询多条
     * @param string $condition
     * @param array $params 预处理参数
     * @return array
     */
    public function findAll($condition = '1', $params = array()) {
        $sqlStr = 'SELECT * FROM ' . $this->tableName . ' WHERE ';
        if (is_array($condition)) {
            if (isset($condition['field'])) {
                $sqlStr = str_replace('*', $condition['field'], $sqlStr);
            }
            if (isset($condition['condition'])) {
                $sqlStr .= $condition['condition'];
            }
            if (isset($condition['group'])) {
                $sqlStr .= ' GROUP BY ' . $condition['group'];
            }
            if (isset($condition['order'])) {
                $sqlStr .= ' ORDER BY ' . $condition['order'];
            }
            if (isset($condition['having'])) {
                $sqlStr .= ' HAVING ' . $condition['having'];
            }
            if (isset($condition['limit'])) {
                $sqlStr .= ' LIMIT ' . $condition['limit'];
            }
        } else {
            $sqlStr .= $condition;
        }
        $result = $this->query_exec($sqlStr, $params);
        //注意：设置结果集的读取方式，这里用的是关联的方式进行读取，如果不设置的话读取出来的是一个关联加索引的数组
        $result->setFetchMode(PDO::FETCH_ASSOC);
        $data = array();
        while ($row = $result->fetch()) {
            $obj = $this->model();
            $obj->data = $row;
            $data[] = $obj;
        }
        return $data;
    }

    public function count($condition = '1', $params = array()) {
        $sqlStr = "SELECT count(*) as num FROM {$this->tableName} WHERE $condition";
        $result = $this->query_exec($sqlStr, $params);
        $data = $result->fetch();
        return $data['num'];
    }

    public function update($data, $condition, $params = array()) {
        $set = '';
        foreach ($data as $field => $value) {
            if (null === $value) {
                $set .= "$field = null,";
            } else {
                $set .= sprintf('%s = "%s",', $field, addslashes($value));
            }
        }
        $set = substr($set, 0, -1);
        $sqlStr = "UPDATE {$this->tableName} SET $set WHERE $condition";
        $result = $this->query_exec($sqlStr, $params);
        return $result->rowCount();
    }

    public function delete($condition, $params = array()) {
        $sqlStr = "DELETE FROM {$this->tableName} WHERE $condition";
        $result = $this->query_exec($sqlStr, $params);
        return $result->rowCount();
    }

    /**
     * 增加或修改
     */
    public function save() {
        list($set, $data_field, $data_value) = array('', '', '');
        foreach ($this->data as $field => $value) {
            $data_field .= "$field,";
            if (null === $value) {
                $set .= "$field = null,";
                $data_value .= 'null,';
            } else {
                $set .= sprintf('%s = "%s",', $field, addslashes($value));
                $data_value .= sprintf('"%s",', addslashes($value));
            }
        }
        //UPDATE or INSERT
        if (isset($this->data[$this->PK]) && $this->data[$this->PK] > 0) {
            $set = substr($set, 0, -1);
            $sqlStr = "UPDATE {$this->tableName} SET $set WHERE {$this->PK} = {$this->data[$this->PK]}";
        } else {
            $data_field = substr($data_field, 0, -1);
            $data_value = substr($data_value, 0, -1);
            $sqlStr = "INSERT INTO {$this->tableName} ($data_field) VALUES($data_value)";
        }
        try {
            $result = $this->db->query($sqlStr);
        } catch (Exception $e) {
            die("Error : " . $e->getMessage());
        }
        if ($this->PK && empty($this->data[$this->PK])) {
            $this->data[$this->PK] = $this->db->lastInsertId();  //模型insert时赋值PK
        }
        return $result;
    }
}
