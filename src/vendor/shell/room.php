<?php
if (!isset($_SESSION)) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('location:index.php');
    die;
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
?>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            *{margin: 0;padding: 0;}
        </style>
    </head>
    <body>
        <div id="chessBox" style="margin-top: 150px;"></div>
        <script src="http://libs.baidu.com/jquery/2.0.0/jquery.js"></script>
        <script src="./1.js"></script>
        <script type="text/javascript">
            //房间，不同的URL参数代表不同的房间

            //玩家，向服务器请求，申请房间位置，如果没有位置的话就不能进入，房间有两个位置

            //玩家向服务器发送执子请求，执黑方或白方

            //先进入房间的玩家为房主，可以点击开始游戏，后进入房间的玩家点击准备

            var currentPlayer = 1;

            var player = new WzChess();
            player.createChessboard('#chessBox');
            player.player = 2;  //白子
            player.play(player);

            var user = {user_id: <?= $user_id ?>, user_name: '<?= $user_name ?>'};
            var wsurl = 'ws://199.247.7.127:9666';
            var websocket;
            if (window.WebSocket) {
                websocket = new WebSocket(wsurl);
                //连接建立
                websocket.onopen = function (evevt) {
                    try {
                        websocket.send(JSON.stringify({act: 'enter_room', room_id:<?= intval($_GET['id']) ?>, user: user}));
                    } catch (ex) {
                        console.log(ex);
                    }
                }

                //消息监听
                websocket.onmessage = function (event) {
                    var msg = JSON.parse(event.data);
                    console.log(msg);
                    if ('enter_room_status' === msg.type) {
                        if (1 === msg.status) {

                        } else if (3 === msg.status) {
                            alert(msg.msg);
                            location.href = 'index.php';
                        } else if (0 === msg.status) {
                            alert(msg.msg);
                            location.href = 'index.php';
                        }
                    }
                };
            }
        </script>
    </body>
</html>