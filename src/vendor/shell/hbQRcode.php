<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_include_path(".:" . dirname(dirname(__FILE__)) . PATH_SEPARATOR);  //设置include的位置，设置以后引用就就能用./或/这样路径
set_time_limit(0);
$params = getopt('i:');  //接收红包生成条件ID：php hbQRcode.php -i 98
$id = intval($params['i']);
empty($id) && exit('not find id!');

require_once 'QR_creator/Creator.php';
require_once 'utility/PinYin.php';
require_once 'utility/pclzip.lib.php';

define('PROCESS_MAX', 16); //最大进程数

$fun_sql = new ProMySQL();
$condition = $fun_sql->find('wwy_product_condition', 'id = ' . $id);  //条件
$product = $fun_sql->find('wwy_product', 'id = ' . $condition['pid']);
$hongbao = $fun_sql->findAll('wwy_product_hongbao', 'condition_id = ' . $condition['id']);  //红包
$conf = $fun_sql->find('wwy_web_set', 'id = 1');
$img = $fun_sql->find('wwy_images', 'id = ' . intval($conf['logo_img']));
if ($img) {
    define('LOGO', dirname(dirname(dirname(__FILE__))) . $img['src']);  //二维码LOGO    
} else {
    define('LOGO', null);  //二维码LOGO
}

//调用
if (!empty($hongbao)) {
    run($hongbao);
}

/**
 * 生成二维码
 * @param array $hb 红包信息
 */
function hbQR_code($hb) {
    //echo $hb['id'] . PHP_EOL;
    global $condition;
    global $product;
    $fun_sql = new ProMySQL();
    $qr = new Creator();
    $py = new PinYin();
    $url = trim($hb['url']) . '&id=' . NumberEncrypt::encrypt(intval($hb['id']));
    $path_root = dirname(dirname(dirname(__FILE__)));
    $path_site = '/upload/qrcode/' . $condition['id'] . '/';
    $path = $qr->createQRCodeImg($url, LOGO, $py->to_pinyin($product['name'], 'UTF8') . '_' . $py->to_pinyin($condition['batch'], 'UTF8') . '_' . $hb['hb_sn'], $path_root . $path_site);
    $fun_sql->update('wwy_product_hongbao', array('url' => $url, 'qrcode' => str_replace($path_root, '', $path)), 'id = ' . $hb['id']);
    $doneQR = $fun_sql->count('wwy_product_hongbao', 'qrcode != "" AND condition_id = ' . $condition['id']);
    if ($doneQR == $condition['hb_total']) {
        $_condition = $fun_sql->find('wwy_product_condition', 'id = ' . $condition['id']);  //条件
        if (empty($_condition['qrcode_path'])) {
            //打包二维码zip
            $zip_name = $path_root . '/upload/qrcode/' . $py->to_pinyin($product['name'], 'UTF8') . $condition['id'] . '_' . $py->to_pinyin($condition['batch'], 'UTF8') . '.zip';
            $archive = new PclZip($zip_name);
            //参数说明，依次为，1准备压缩文件目录，2移除path,3移除的path,4增加path,5增加的path
            $res = $archive->create($path_root . $path_site, PCLZIP_OPT_REMOVE_PATH, $path_root . $path_site, PCLZIP_OPT_ADD_PATH, 'images');
            if ($res == 0) {
                die("Error : " . $archive->errorInfo(true));
            }
            $fun_sql->update('wwy_product_condition', array('qrcode_path' => str_replace($path_root, '', $zip_name)), 'id = ' . $condition['id']);
        }
    }
}

/**
 *  创建多进程
 */
function run($hbArr) {
    $batch_num = ceil(count($hbArr) / PROCESS_MAX);
    //最早的进程，也就是原始父进程
    $parentPid = getmypid();
    //开启子进程
    for ($i = 0; $i < PROCESS_MAX; $i++) {
        $batch = array_slice($hbArr, $i * $batch_num, $batch_num);
        $pid = pcntl_fork();  //这里执行后可能就创建了一个进程
        if ($pid == -1) {
            exit("Could not fork!\n");
        }
        if (0 == $pid) {
            //父进程
        } else {
            //子进程
            foreach ($batch as $hb) {
                hbQR_code($hb);
            }
            exit(); //子进程逻辑执行完后，马上退出，以免往下走再fork子进程，不好控制  
        }
    }
    //exec("sudo /usr/local/php5.4/bin/php /data/www/lywh/shell/createZip.php -i $cardId -n $remainder -t $total");
    echo "successful";
}
