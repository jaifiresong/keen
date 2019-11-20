<?php

class Creator {

    /**
     * 生成二维码
     * @param type $value 二维码内容  
     * @param type $logoimg 二维码中嵌入图片
     * @param type $outImgName 自定义二维码图片名
     * @param type $upload_path 输出二维码图片保存路径   
     * @return string　返回二维码地址
     */
    public function createQRCodeImg($value, $logoimg = null, $outImgName = null, $upload_path = './upload/qrcode/', $matrixPointSize = 25, $radius = 15, $borderWidth = 15) {
        require_once(dirname(__FILE__) . '/phpqrcode/phpqrcode.php');
        require_once(dirname(__FILE__) . '/BorderImg.php');
        require_once(dirname(__FILE__) . '/RoundedCorner.php');
        $errorCorrectionLevel = 'H'; //容错级别    
        //生成二维码图片   
        $upload_path = $upload_path . date("Ymd");
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, TRUE);
        }
        $codeimg = $upload_path . '/' . date("YmdH") . str_shuffle('0123456abcdef') . '.png';
        if ($outImgName) {
            $codeimg = $upload_path . '/' . $outImgName . '.png';
        }
        QRcode::png($value, $codeimg, $errorCorrectionLevel, $matrixPointSize, 2);
        $QR = $codeimg; //已经生成的原始二维码图   
        if (!empty($logoimg)) {
            //加logo图片
            $b = $matrixPointSize / 10;
            $logo_qr_width = 95 * $b;
            $logo_qr_height = 95 * $b;
            $suffix = explode('.', $logoimg);
            $logo = dirname(__FILE__) . '/' . str_shuffle('0123456789') . '.' . end($suffix);  //准备一张临时图片，后缀名要和原图一样
            $logoPath = $logo;  //等会$logo会成为资源
            //加白边
            $borderC = new BorderImg($logoimg, $logo, $logo_qr_width, $logo_qr_height, $borderWidth);  //第1个参数是原图，第二个参数是加边框后的图
            $borderC->addBorder();
            //圆角
            $rounder = new RoundedCorner($logo, $radius);
            $rounder->round_it($logo);
            $QR = imagecreatefromstring(file_get_contents($QR));  //二维码图
            $logo = imagecreatefromstring(file_get_contents($logo));  //logo图
            if (imageistruecolor($logo)) {
                imagetruecolortopalette($logo, false, 65535);
            }
            $QR_width = imagesx($QR); //二维码图片宽度   
            $QR_height = imagesy($QR); //二维码图片高度   
            $logo_width = imagesx($logo); //嵌入图片宽度   
            $logo_height = imagesy($logo); //嵌入图片高度   
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小   
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
            imagedestroy($logo);  //释放资源
            unlink($logoPath);
        } else {
            $QR = imagecreatefromstring(file_get_contents($QR));
        }
        //保存输出图片   
        imagepng($QR, $codeimg);
        return ltrim($codeimg, '.');
    }

}
