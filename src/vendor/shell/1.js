function WzChess() {
    this.chessCoordinate = [];
    this.chess_diameter = 40;
    this.board_left = 0;
    this.board_top = 0;
    this.player;

    /**
     * @param str box 棋盘放在哪个元素里
     */
    this.createChessboard = function (box) {
        var row = '';
        var board_width = this.chess_diameter * 14 + 2;
        var board = '<div id="chessboard" style="margin: auto;position: relative;width:' + board_width + 'px"><table id="playchess" style="border: 1px solid; width: ' + board_width + 'px; height: ' + board_width + 'px;" border="1" cellspacing="0"></table></div>';
        $(box).html($(board));
        this.board_left = parseInt((document.documentElement.clientWidth - board_width) / 2);
        this.board_top = document.getElementById('chessboard').offsetTop;
        //得到棋盘所有棋位格子
        for (var y = 0; y < 15; y++) {
            if (y > 0) {
                row += '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
            }
            for (var x = 0; x < 15; x++) {
                var X = x * this.chess_diameter + this.board_left;
                var Y = y * this.chess_diameter + this.board_top;
                this.chessCoordinate.push({x: X, y: Y, p: 0});
            }
        }
        $('#chessboard').find('table').html($(row));
    };

    this.play = function (player, judgment) {
        $('#playchess').click(function () {
            var pos = player.pointer(event);  //鼠标点击的位置
            var chessBoardPos = player.inspectPos(pos);  //准备落子的位置
            if (player.dropChess(chessBoardPos, player.player)) {  //落子，1为黑，2为白
                //alert('落子成功');
            } else {
                alert('此处已被占领了！');
            }
            var v = player.victory();
            if (v > 0) {
                if ('function' === typeof (judgment)) {
                    judgment();
                } else {
                    if (1 === v) {
                        alert('黑方胜');
                    } else {
                        alert('白方胜');
                    }
                }
            }
        });
    };

    //得到鼠标点击的位置
    this.pointer = function (event) {
        var resX = event.pageX || (event.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft));
        var resY = event.pageY || (event.clientY + (document.documentElement.scrollTop || document.body.scrollTop));
        console.log('bodyX:' + resX);
        console.log('bodyY:' + resY);
        return {x: resX, y: resY};
    };

    //传入点击的鼠标位置，返回离它最近的棋位格子
    this.inspectPos = function (pos) {
        var x = Math.round((pos['x'] - this.board_left) / this.chess_diameter);
        var y = Math.round((pos['y'] - this.board_top) / this.chess_diameter);
        return {x: this.chessCoordinate[x * 15 + x]['x'], y: this.chessCoordinate[y * 15 + y]['y']};
    };

    //根据棋盘坐标落子
    this.dropChess = function (pos, player) {
        var res = this.actRecord(pos, player); //记录落子位置
        if (res) {
            var chess_width = parseInt(this.chess_diameter - 4);  //子与子之前留出2个像素
            var left = pos['x'] - Math.round(this.chess_diameter / 2) - this.board_left + 2;
            var top = pos['y'] - Math.round(this.chess_diameter / 2) - this.board_top + 2;
            var color = 1 == player ? '#000' : '#CCC';
            var chess = '<div style="width:' + chess_width + 'px;height:' + chess_width + 'px;border-radius:50px;border:1px solid #ccc;background-color:' + color + ';position:absolute;top:' + top + ';left:' + left + ';"></div>';
            $('#chessboard').append($(chess));
        }
        return res;
    };

    //记录已经落了子的位置
    this.actRecord = function (pos, player) {
        for (var i in this.chessCoordinate) {
            if (pos['x'] == this.chessCoordinate[i]['x'] && pos['y'] == this.chessCoordinate[i]['y']) {  //找到了准备落子的位置
                if (0 == this.chessCoordinate[i]['p']) {
                    this.chessCoordinate[i]['p'] = player;
                    return true;  //能落子返回真
                }
            }
        }
        return false; //不能落子返回false
    };

    //检查胜利
    this.victory = function () {
        var p1 = [];
        var p2 = [];
        for (var i in this.chessCoordinate) {
            if (1 == this.chessCoordinate[i]['p']) {
                var t = this.chessCoordinate[i];
                t.pos = parseInt(i);
                p1.push(t);
            }
            if (2 == this.chessCoordinate[i]['p']) {
                var t = this.chessCoordinate[i];
                t.pos = parseInt(i);
                p2.push(t);
            }
        }
        if (this.proVictory(p1)) {
            return 1;
        }
        if (this.proVictory(p2)) {
            return 2;
        }
        return 0;
    };

    //传入某方玩家的落子坐标，判断其有没有玩家胜利
    this.proVictory = function (p) {
        var direction = [1, 14, 15, 16]; //横，竖，斜(14,16)三个方向 
        for (var index in direction) {
            for (var i = 0; i < p.length; i++) {
                vic = 4;
                first = p[i]['pos'];
                for (var ii = i + 1; ii < p.length; ii++) {
                    for (var pos in p) {
                        if (first + vic * direction[index] == p[pos]['pos']) {
                            if (1 == direction[index]) {  //当步进为1时，后4个子的位置不用判断，它们不可能胜
                                var _continue = false;
                                for (var x = 0; x < 15; x++) {
                                    if (first >= 11 + x * 15 && first <= 14 + x * 15) {
                                        _continue = true;
                                    }
                                }
                                if (_continue) {
                                    continue;
                                }
                            }
                            vic -= 1;
                        }
                    }
                    if (vic <= 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    };
}

//机器人
var robot =
        {
            name: 'robot',
            playchess: function () {
                var canDropChess = [];
                var p1Pos = [];
                for (var i in this.chessCoordinate) {
                    if (0 == this.chessCoordinate[i]['p']) {
                        var t = this.chessCoordinate[i];
                        t.pos = parseInt(i);
                        canDropChess.push(t);
                    }
                    if (1 == this.chessCoordinate[i]['p']) {
                        var t = this.chessCoordinate[i];
                        t.pos = parseInt(i);
                        p1Pos.push(t);
                    }
                }
                var direction = shulff_array([1, 14, 15, 16]); //横，竖，斜三个方向 
                var count = [];  //用来记录三个方向中连子的出现个数
                var bestPos = {};
                for (var index in direction) {
                    count.push(0);
                    for (var i = 0; i < p1Pos.length; i++) {
                        first = p1Pos[i]['pos'];
                        vic = 1;
                        for (var ii = i + 1; ii < p1Pos.length; ii++) {
                            for (var pos in p1Pos) {
                                if (first + vic * direction[index] == p1Pos[pos]['pos']) {
                                    vic += 1;
                                    count[index] += 1;  //出现连子就加1
                                }
                            }
                        }
                    }
                }
                //判断连子最多方向，取得它的前后坐标
                var maxDirection = direction[arrayMaxIndex(count)];  //连子最多的方向
                var bestPos = p1Pos[0];  //最佳落子点,目前先不考虑这个位置能不能放棋
                var bestPosOne = p1Pos[0];
                for (var i = 0; i < p1Pos.length; i++) {
                    var first = p1Pos[i]['pos'];
                    var n = 1;
                    for (var ii = i + 1; ii < p1Pos.length; ii++) {
                        for (var pos in p1Pos) {
                            if (first + n * maxDirection == p1Pos[pos]['pos']) {
                                if (p1Pos[0] == bestPosOne) {
                                    bestPosOne = p1Pos[i];
                                }
                                bestPos = p1Pos[pos];
                            }
                        }
                    }
                }
                //在能放子的位置对比最佳落子点
                var willPos = willDropPos(canDropChess, bestPos, bestPosOne, direction);
                console.log(willPos);
                dropChess(this.chessCoordinate[willPos], 2);
            }
        };

//传入能放棋的位置@[],最佳位置1&2@{}和方向@int，返回能放棋的最佳位置@int
function willDropPos(canPos, bestPos, bestPosOne, direction) {
    bestPos_pos = parseInt(bestPos['pos']) + parseInt(direction);
    bestPosOne_pos = parseInt(bestPosOne['pos']) - parseInt(direction);
    var canPos = shulff_array(canPos);
    for (var can in canPos) {
        if (canPos[can]['pos'] == bestPos_pos) {
            return bestPos_pos;
        }
        if (canPos[can]['pos'] == bestPosOne_pos) {
            return bestPosOne_pos;
        }
    }
    //两个最佳都被占了
    var count = 1;
    var dArr = shulff_array([1, 14, 15, 16]);
    var returnArr = [];
    while (true) {
        for (var can in canPos) {
            for (var d in dArr) {
                if (bestPos_pos + count * dArr[d] == canPos[can]['pos']) {
                    returnArr.push(canPos[can]['pos']);
                }
            }
        }
        if (count > canPos.length) {
            break;
        }
        count += 1;
    }
    return shulff_array(returnArr)[0];
}

//数组中最大值的索引
function arrayMaxIndex(list) {
    var index = 0;
    var max = list[0];
    for (var i in list) {
        if (list[i] > max) {
            max = list[i];
            index = i;
        }
    }
    return index;
}

function shulff_array(arr) {
    //sort 是对数组进行排序
    //他的是这样工作的。每次从数组里面挑选两个数 进行运算。
    //如果传入的参数是0 两个数位置不变。
    //如果参数小于0 就交换位置
    //如果参数大于0就不交换位置
    //接下来用刚才的较大数字跟下一个进行比较。这样循环进行排序。
    /*恰好。我们利用了这一点使用了0.5 - Math.random  这个运算的结果要么是大于0,要么是小于0.
     这样要么交换位置，要么不交换位置。当然大于或者小于0是随即出现的。所以数组就被随即排序了。*/
    return arr.sort(function () {
        return 0.5 - Math.random();
    });
}