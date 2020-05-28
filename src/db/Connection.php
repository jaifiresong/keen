<?php


class Connection
{
    private static $db_pool = array();
    protected $db;
    public $conf = 'mysql'; //默认数据库名

    public function connect()
    {
        $this->db = empty(self::$db_pool[$this->conf]) ? null : self::$db_pool[$this->conf];
        if (empty($this->db)) {
            $connConf = Dispatcher::$config['database'][$this->conf];
            // echo "<br/>连接 : {$connConf['host']}<br/>";
            $connectionString = "{$connConf['driver']}:host={$connConf['host']};port={$connConf['port']};dbname={$connConf['db_name']}";
            try {
                if ($connConf['long']) {
                    //默认这个不是长连接，如果需要数据库长连接，需要最后加一个参数：array(PDO::ATTR_PERSISTENT => true)。长连接可以减少数据库连接的开销从而提升响应速度
                    $this->db = new PDO($connectionString, $connConf['username'], $connConf['password'], array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                } else {
                    $this->db = new PDO($connectionString, $connConf['username'], $connConf['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                }
            } catch (PDOException $e) {
                die("Error!: " . $e->getMessage());
            }
            $this->db->query("set names '{$connConf['charset']}'");
            self::$db_pool[$this->conf] = $this->db;
        } else {
            // echo "<br>已存在  {$this->conf}</br>";
        }
    }

}