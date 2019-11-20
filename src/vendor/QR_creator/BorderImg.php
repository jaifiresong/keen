<?php

/**
 * 给图片加白边
 * imgpath : 图片路径 "1.jpeg"
 * savepath : 保存路径， "vcard/"
 * newWidth : 新生成的图片宽度 ，默认为100
 * newHeight : 新生成的图片高度，默认为100
 * borderWidth : 边框宽度，默认为10
 * borderColor : 边框颜色，默认为白色， 格式为 $borderColor="255,255,255"
 */
class BorderImg {

    private $_img_path = '';
    private $_img_width = '';
    private $_img_height = '';
    private $_new_width = '';
    private $_new_height = '';
    private $_new_path = '';
    private $_border_color = '';
    private $_border_width = '';

    public function __construct($imgpath, $savepath, $newWidth = 100, $newHeight = 100, $borderWidth = 10, $borderColor = '255,255,255') {
        $this->_img_path = $imgpath;
        $this->_new_width = $newWidth;
        $this->_new_height = $newHeight;

        if (file_exists($savepath)) {
            unlink($savepath);
        }

        $this->_new_path = $savepath;
        $this->_border_width = $borderWidth;
        $this->_border_color = $borderColor;

        $size = getimagesize($this->_img_path);

        $this->_img_width = $size[0];
        $this->_img_height = $size[1];
    }

    private function imgext() {
        $ext = substr($this->_img_path, strrpos($this->_img_path, '.'));
        if (empty($ext)) {
            return false;
        }

        switch (strtolower($ext)) {
            case '.jpg':
                $ext = ".jpeg";
            case '.jpeg':
                $ext = ".jpeg";
                break;
            default:
                return $ext;
        }

        return $ext;
    }

    private function _load_logo_image() {
        $ext = substr($this->_img_path, strrpos($this->_img_path, '.'));
        if (empty($ext)) {
            return false;
        }

        switch (strtolower($ext)) {
            case '.jpg':
                $img = @imagecreatefromjpeg($this->_img_path);
                break;
            case '.jpeg':
                $img = @imagecreatefromjpeg($this->_img_path);
                break;
            case '.gif':
                $img = @imagecreatefromgif($this->_img_path);
                break;
            case '.png':
                $img = @imagecreatefrompng($this->_img_path);
                break;
            default:
                return false;
        }
        return $img;
    }

    private function _out_logo_image($im) {
        $ext = substr($this->_img_path, strrpos($this->_img_path, '.'));
        if (empty($ext)) {
            return false;
        }

        switch (strtolower($ext)) {
            case '.jpg':
                $img = @imagejpeg($im, $this->_new_path, 100);
                $ext = ".jpeg";
                break;
            case '.jpeg':
                $img = @imagejpeg($im, $this->_new_path, 100);
                $ext = ".jpeg";
                break;
            case '.gif':
                $img = @imagegif($im, $this->_new_path);
                break;
            case '.png':
                $img = @imagepng($im, $this->_new_path);
                break;
            default:
                return false;
        }

        return $ext;
    }

    public function addBorder() {

        $logo = $this->_img_path;
        if (file_exists($logo)) {
            $logo = $this->_load_logo_image();
        } else {
            $logo = imagecreatefrompng(($logo));
        }
        $im = imagecreatetruecolor($this->_new_width + ($this->_border_width * 2), $this->_new_height + ($this->_border_width * 2));

        $borderColorArr = explode(",", $this->_border_color);
        if ($borderColorArr) {
            $background = imagecolorallocate($im, $borderColorArr[0], $borderColorArr[1], $borderColorArr[2]);
        } else {
            $background = imagecolorallocate($im, 255, 255, 255);
        }
        imagefill($im, 0, 0, $background);

        imagecopyresampled($im, $logo, $this->_border_width, $this->_border_width, 0, 0, $this->_new_width, $this->_new_height, $this->_img_width, $this->_img_height);
        $this->_out_logo_image($im);
    }

    public function mergedImg($bgimg, $logoimg, $newpath) {
        $im = imagecreatefrompng($bgimg);
        $logo = imagecreatefrompng($logoimg);

        $imSizeArr = getimagesize($bgimg);
        $imWidth = $imSizeArr[0];
        $imHeight = $imSizeArr[1];

        $logoSizeArr = getimagesize($logoimg);
        $logoWidth = $logoSizeArr[0];
        $logoHeight = $logoSizeArr[1];

        $x = ceil(($imWidth / 4) * 1.5);
        $y = ceil(($imHeight / 4) * 1.5);

        imagecopyresampled($im, $logo, $x, $y, 0, 0, ceil($imWidth / 4), ceil($imHeight / 4), $logoWidth, $logoHeight);
        imagepng($im, $newpath);
        return $newpath;
    }

}
