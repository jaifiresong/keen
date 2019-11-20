<?php
if (!isset($_SESSION)) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = rand(1000, 9999);
    $_SESSION['user_name'] = $_SESSION['user_id'];
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
?>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            *{margin: 0;padding: 0;}
            .room{
                width: 120px;
                height: 120px;
                border: 1px solid #C3C3C3;
                background-color: #0099CC;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <input type="text" value="" name="roomname" />
        <button onclick="createRoom()">创建房间</button>
        <div id="rooms_container" style="margin-top: 100px;">

        </div>

        <script src="http://libs.baidu.com/jquery/2.0.0/jquery.js"></script>
        <script>



            var wsurl = 'ws://199.247.7.127:9666';
            var websocket;
            if (window.WebSocket) {
                websocket = new WebSocket(wsurl);
                //连接建立
                websocket.onopen = function (event) {
                    try {
                        //向服务器请求获得房间列表
                        websocket.send(JSON.stringify({act: 'active_rooms'}));
                    } catch (ex) {
                        console.log(ex);
                    }
                };

                //消息监听
                websocket.onmessage = function (event) {
                    var msg = JSON.parse(event.data);
                    console.log(msg);
                    if ('show_rooms_list' === msg.type) {
                        for (var i in msg.list) {
                            var room = '';
                            var seat1 = msg.list[i]['seat1'];
                            var seat2 = msg.list[i]['seat2'];
                            var master = true === seat1.master ? seat1.user_name : seat2.user_name;
                            if (null != seat1 && null != seat2) {  //已满
                                room = '<div class="room"><h4>' + msg.list[i]['name'] + '</h4><a href="javascript:alert(\'房间已满\')">已满</a><p>房主：<span>' + master + '</span></p></div>';
                            } else {
                                room = '<div class="room"><h4>' + msg.list[i]['name'] + '</h4><a href="room.php?id=' + msg.list[i]['id'] + '">进入</a><p>房主：<span>' + master + '</span></p></div>';
                            }
                            $('#rooms_container').append($(room));
                        }
                    }
                    if ('create_room_success' === msg.type) {
                        alert('创建成功');
                        location.href = './room.php?id=' + msg.id;
                    }
                };

                function createRoom() {
                    var name = $('input[name="roomname"]').val();
                    if ('' == name) {
                        alert('请输入房间名');
                        return;
                    }
                    websocket.send(JSON.stringify({act: 'create_room', name: name, user: {user_id: <?= $user_id ?>, user_name: '<?= $user_name ?>'}}));
                    $('input[name="roomname"]').val('')
                }
            } else {
                alert('浏览器不支持websocket');
            }
        </script>
    </body>
</html>