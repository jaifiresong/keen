<?php

Class WebChatServer {

    public $master;  // 连接 server 的 client
    public $sockets = array(); // 不同状态的 socket 管理
    public $handshake = false; // 判断是否握手

    function __construct($address, $port) {
        $this->address = $address;
        $this->port = $port;
        // 建立一个 socket 套接字
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() failed");
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) or die("socket_option() failed");
        socket_bind($this->master, $address, $port) or die("socket_bind() failed");
        socket_listen($this->master, 2) or die("socket_listen() failed");
        $this->sockets[] = $this->master;
        // debug
        echo("Master socket  : " . $this->master . "\n");
    }

    public function run() {
        echo "start listen...\n";
        while (true) {
            echo "ready\n";
            //自动选择来消息的 socket 如果是握手 自动选择主机
            $actSockets = $this->sockets;
            $write = NULL;
            $except = NULL;
            socket_select($actSockets, $write, $except, NULL);  //获取$actSockets数组中活动的socket，并且把不活跃的从$actSockets数组中删除
            //如果有新的连接
            if (in_array($this->master, $actSockets)) {  //如果没有建立连接的socket还在$actSockets里面，说明$this->handshake为False
                $client = socket_accept($this->master);
                //接受并加入新的socket连接
                $this->sockets[] = $client;
                $header = socket_read($client, 1024);
                //通过socket获取数据执行handshake
                $this->perform_handshaking($header, $client);
                $ip = null;
                socket_getpeername($client, $ip); //获取client ip 编码json数据,并发送通知
                $response = $this->mask(json_encode(array('type' => 'system', 'message' => 'welcome\(^o^)/ ', 'ip' => $ip)));
                $this->send_message($client, $response);  //向client发送欢迎消息
                echo "connect client successful\n";
                continue;
            }
            //var_dump($actSockets);  //发现第一次握手的时候的好个socket是一个没有连接的socket,第二次就有了，并且两次socket的ID都不一样
            foreach ($actSockets as $socket) {
                //和client通信
                while (socket_recv($socket, $buffer, 1024, 0) > 0) {
                    if ($this->handshake) {
                        // 如果已经握手，接受数据，并处理
                        $buffer = $this->unmask($buffer);
                        $this->pro_message($socket, $buffer);
                    } else {
                        $err_code = socket_last_error();
                        $err_test = socket_strerror($err_code);
                        echo "client " . (int) $socket . " has closed[$err_code:$err_test]\n";
                    }
                    echo "send info end!\n";
                    break 2;  //跳出两层循环
                }
                $this->pro_disconnect($socket);
            }
            echo "ready end!\n";
        }
    }

    /**
     * des：处理客户端发送过来的消息
     * @param resource $client 当前客户端
     * @param String $name 客户端发送过来的消息
     */
    private function pro_message($client, $msg) {
        $msgArr = json_decode($msg, True);
        if (!empty($msgArr['first'])) {
            //发送欢迎消息
            $response = $this->mask(json_encode(array('type' => 'system', 'name' => $msgArr['name'], 'message' => "欢迎{$msgArr['name']}加入聊天")));
        } else if (!empty($msgArr['name'])) {
            //客户端主动发送的
            $response = $this->mask(json_encode(array('type' => 'usermsg', 'name' => $msgArr['name'], 'message' => $msgArr['message'])));
        } else {
            $response = $this->mask(json_encode(array('type' => 'tip', 'message' => 'client reconnect')));
        }
        $this->send_message_all($response);  //向client发送通知
    }

    //处理断开的client;测试发现它不能放在socket_recv的前面
    private function pro_disconnect($socket) {
        $result = @socket_read($socket, 1024, PHP_NORMAL_READ);
        if (False === $result) {
            $err_code = socket_last_error();
            $err_test = socket_strerror($err_code);
            echo "client " . (int) $socket . " has closed[$err_code:$err_test]\n";
            unset($this->sockets[array_search($socket, $this->sockets)]);
        }
    }

    //编码数据
    private function mask($text) {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        if ($length <= 125)
            $header = pack('CC', $b1, $length);
        elseif ($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
        elseif ($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);
        return $header . $text;
    }

    //解码数据
    private function unmask($text) {
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        return $text;
    }

    //发送消息给指定用户
    private function send_message($client, $msg) {
        socket_write($client, $msg, strlen($msg));
        return true;
    }

    //发送消息给全部用户
    private function send_message_all($msg) {
        foreach ($this->sockets as $socket) {
            @socket_write($socket, $msg, strlen($msg));
        }
        return true;
    }

    //tcp握手（建立TCP连接）
    private function perform_handshaking($header, $client) {
        $host = $this->address;
        $port = $this->port;
        $headers = array();
        $lines = preg_split("/\r\n/", $header);
        foreach ($lines as $line) {
            $line = chop($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "WebSocket-Origin: $host\r\n" .
                "WebSocket-Location: ws://$host:$port\r\n" .
                "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        socket_write($client, $upgrade, strlen($upgrade));
        $this->handshake = True;
    }

}

//使用4000端口启动服务
$ws = new WebChatServer('127.0.0.1', 4000);
$ws->run();
