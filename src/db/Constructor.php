<?php


class Constructor extends Connection
{
    private $selectStr;
    private $fromStr;
    private $leftJoinStr;
    private $whereStr;
    private $groupStr;
    private $orderStr;
    private $limitStr;
    private $sqlStr;  //如果不为null就直接执行这条sql
    private $params = array();  //预处理参数

    private function __construct()
    {

    }

    public static function connection($conf = null)
    {
        $constructor = new self();
        if ($conf) {
            $constructor->conf = $conf;
        }
        $constructor->connect();
        return $constructor;
    }

    public function createCommand($sqlStr = null, $params = array())
    {
        if ($sqlStr) {
            $this->sqlStr = $sqlStr;
        }
        $this->params = $params;
        return $this;
    }

    public function where($str, $params = array())
    {
        if (!empty($str)) {
            $this->whereStr = "WHERE $str ";
        }
        $this->params = $params;
        return $this;
    }

    public function select($str = '*')
    {
        $this->selectStr = "SELECT $str ";
        return $this;
    }

    public function from($str)
    {
        $this->fromStr = "FROM $str ";
        return $this;
    }

    public function leftJoin($str, $on)
    {
        $this->leftJoinStr .= "LEFT JOIN $str ON $on ";
        return $this;
    }

    public function group($str)
    {
        if (!empty($str)) {
            $this->groupStr = "GROUP BY $str ";
        }
        return $this;
    }

    public function order($str)
    {
        if (!empty($str)) {
            $this->orderStr = "ORDER BY $str ";
        }
        return $this;
    }

    public function limit($offset, $start = 0)
    {
        if ($offset) {
            $this->limitStr = "LIMIT $start,$offset ";
        }
        return $this;
    }

    public function query()
    {
        $prep = $this->query_prep();
        $prep->setFetchMode(PDO::FETCH_ASSOC);
        //var_dump($prep->rowCount()); //影响的行数
        //var_dump($this->db->lastInsertId());//新增ID
        return array('rowCount' => $prep->rowCount(), 'lastInsertId' => (int)$this->db->lastInsertId());
    }

    public function queryRow()
    {
        $prep = $this->query_prep();
        $prep->setFetchMode(PDO::FETCH_ASSOC);
        return $prep->fetch();
    }

    public function queryAll()
    {
        $prep = $this->query_prep();
        $prep->setFetchMode(PDO::FETCH_ASSOC);
        $data = array();
        while ($row = $prep->fetch()) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 该方法预处理方式执行
     * @return object PDOStatement对象
     */
    private function query_prep()
    {
        $sql_str = $this->createSqlStr();
        $prepare = $this->db->prepare($sql_str);
        foreach ($this->params as $field => $value) {
            //$prepare->bindParam($field, $value);//地址绑定
            $prepare->bindValue($field, $value);//值绑定
        }
        try {
            $prepare->execute();
        } catch (Exception $e) {
            die("Error : " . $e->getMessage() . ' the sql was : ' . $sql_str);
        }
        return $prepare;
    }

    private function createSqlStr()
    {
        if ($this->sqlStr) {
            return $this->sqlStr;
        }
        return $this->selectStr . $this->fromStr . $this->leftJoinStr . $this->whereStr . $this->groupStr . $this->orderStr . $this->limitStr;
    }
}