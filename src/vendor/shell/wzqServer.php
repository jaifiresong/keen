<?php

$room = array(
    'id' => 'g1123123', //房间ID
    'name' => '高手进', //房间名
    'seat1' => '', //坐位1，对应玩家1
    'seat2' => ''  //坐位2，对应玩家2
);

$seat1 = array(
    'user_id' => '1',
    'user_name' => 'admin',
    'camp' => 'blakc',
);

class wzqServer {

    private $rooms;
    private $server;

    public function __construct($ip, $port) {
        $this->server = new swoole_websocket_server($ip, $port);
        $this->rooms = array();
    }

    public function start() {
        $this->server->on('open', function ($server, $request) {
            //$request->fd是服务瑞给客户端分配的ID，每次刷新都会重新分配
            echo "server: handshake success with fd{$request->fd}\n";
        });
        //message
        $this->server->on('message', function ($server, $client) {
            //$client是连接了服务器的客户端，他的请求里面有array('fd','finish','opencode','data')
            $this->dealMessage($client);
        });
        $this->server->on('close', function ($server, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->start();
    }

    private function showRoomsList($fd) {
        $response = array('type' => 'show_rooms_list', 'list' => $this->rooms);
        $this->server->push($fd, json_encode($response));
    }

    private function createRoom($fd, $msg) {
        $room_id = rand(10000, 99999);
        $this->rooms[] = array('id' => $room_id, 'name' => $msg['name'], 'seat1' => null, 'seat2' => null);
        $this->server->push($fd, json_encode(array('type' => 'create_room_success', 'id' => $room_id)));
    }

    private function findRoom($room_id) {
        foreach ($this->rooms as $k => $v) {
            if ($room_id === $v['id']) {
                return $k;
            }
        }
        return null;
    }

    private function enterRoom($fd, $msg) {
        $room_key = $this->findRoom($msg['room_id']);
        if (empty($this->rooms[$room_key])) {
            $this->server->push($fd, json_encode(array('type' => 'enter_room_status', 'status' => 0, 'msg' => '房间不存在')));
            return;
        }
        $room = $this->rooms[$room_key];
        $seat = array(
            'user_id' => $msg['user']['user_id'],
            'user_name' => $msg['user']['user_name'],
            'master' => false, //房主
            'camp' => null
        );
        if (empty($room['seat1']) && empty($room['seat2'])) {
            $seat['master'] = true;
        }
        if (empty($room['seat1'])) {
            $this->rooms[$room_key]['seat1'] = $seat;
            $this->server->push($fd, json_encode(array('type' => 'enter_room_status', 'status' => 1, 'msg' => 'ok', 'info' => $seat)));
        } elseif (empty($room['seat2'])) {
            $this->rooms[$room_key]['seat2'] = $seat;
            $this->server->push($fd, json_encode(array('type' => 'enter_room_status', 'status' => 1, 'msg' => 'ok', 'info' => $seat)));
        } else {
            //房间已满
            $this->server->push($fd, json_encode(array('type' => 'enter_room_status', 'status' => 3, 'msg' => '房间已满')));
        }
    }

    private function dealMessage($client) {
        $msg = json_decode($client->data, true);
        if (isset($msg['act'])) {
            if ('active_rooms' === $msg['act']) {
                $this->showRoomsList($client->fd); //发送房间列表给客户端
            }
            if ('create_room' === $msg['act']) {
                $this->createRoom($client->fd, $msg); //创建房间
            }
            if ('enter_room' === $msg['act']) {  //进入房间
                $this->enterRoom($client->fd, $msg);
            }
        }
    }

}

$server = new wzqServer("199.247.7.127", 9666);
$server->start();







