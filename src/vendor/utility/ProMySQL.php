<?php

class ProMySQL {

    private $conn_handle;

    public function __construct() {
        $this->conn_handle = mysqli_connect('127.0.0.1', 'root', 'yz83YQz');
        if (mysqli_connect_errno() > 0) {
            trigger_error("数据库连接失败:" . mysqli_connect_errno() . ',' . mysqli_connect_error(), E_USER_ERROR);
        }
        mysqli_select_db($this->conn_handle, 'hbissue');
        mysqli_query($this->conn_handle, "set names 'utf8'");
    }

    public function into($tableName, $data) {
        $data_name = '';
        $data_value = '';
        foreach ($data as $key => $value) {
            $data_name .= $key . ',';
            $data_value .= '"' . addslashes($value) . '",';
        }
        $data_name = substr($data_name, 0, -1);
        $data_value = substr($data_value, 0, -1);
        $sql = "INSERT INTO " . $tableName . " (" . $data_name . ") VALUES(" . $data_value . ")";
        mysqli_query($this->conn_handle, $sql);
        return mysqli_insert_id($this->conn_handle);
    }

    public function update($tableName, $data, $where) {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= $key . '="' . addslashes($value) . '",';
        }
        $set = substr($set, 0, -1);
        $sql = " UPDATE " . $tableName . "  SET " . $set . " where " . $where;
        mysqli_query($this->conn_handle, $sql);
    }

    public function find($tableName, $condition) {
        $sql = "SELECT * FROM $tableName WHERE $condition";
        $result = mysqli_query($this->conn_handle, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function findAll($tableName, $condition) {
        $sql = "SELECT * FROM $tableName WHERE $condition";
        $result = mysqli_query($this->conn_handle, $sql);
        $r = array();
        while ($data = mysqli_fetch_assoc($result)) {
            $r[] = $data;
        }
        return $r;
    }

    public function count($tableName, $condition = '1') {
        $sql = "SELECT count(*) as num FROM $tableName WHERE $condition";
        $result = mysqli_query($this->conn_handle, $sql);
        if ($result) {
            $data = mysqli_fetch_assoc($result);
            return $data['num'];
        }
        return 0;
    }

}
