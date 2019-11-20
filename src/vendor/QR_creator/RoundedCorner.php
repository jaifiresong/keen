<?php

class RoundedCorner {

    private $_r;
    private $_g;
    private $_b;
    private $_image_path;
    private $_radius;

    function __construct($image_path, $radius, $r = 255, $g = 0, $b = 0) {
        $this->_image_path = $image_path;
        $this->_radius = $radius;
        $this->_r = (int) $r;
        $this->_g = (int) $g;
        $this->_b = (int) $b;
    }

    private function _get_lt_rounder_corner() {
        $radius = $this->_radius;
        $img = imagecreatetruecolor($radius, $radius);
        $bgcolor = imagecolorallocate($img, $this->_r, $this->_g, $this->_b);
        $fgcolor = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $bgcolor);
        imagefilledarc($img, $radius, $radius, $radius * 2, $radius * 2, 180, 270, $fgcolor, IMG_ARC_PIE);
        imagecolortransparent($img, $fgcolor);
        return $img;
    }

    private function _load_source_image() {
        $ext = substr($this->_image_path, strrpos($this->_image_path, '.'));
        if (empty($ext)) {
            return false;
        }
        switch (strtolower($ext)) {
            case '.jpg':
                $img = @imagecreatefromjpeg($this->_image_path);
                break;
            case '.gif':
                $img = @imagecreatefromgif($this->_image_path);
                break;
            case '.png':
                $img = @imagecreatefrompng($this->_image_path);
                break;
            default:
                return false;
        }
        return $img;
    }

    public function round_it($newImage) {
        // load the source image
        $src_image = $this->_load_source_image();
        if ($src_image === false) {
            die('Sorry, can\'t load the image');
        }
        $image_width = imagesx($src_image);
        $image_height = imagesy($src_image);

        // create a new image, with src_width, src_height, and fill it with transparent color
        $image = imagecreatetruecolor($image_width, $image_height);
        $trans_color = imagecolorallocate($image, $this->_r, $this->_g, $this->_b);
        imagefill($image, 0, 0, $trans_color);

        // then overwirte the source image to the new created image
        imagecopymerge($image, $src_image, 0, 0, 0, 0, $image_width, $image_height, 100);

        // then just copy all the rounded corner images to the 4 corners
        $radius = $this->_radius;
        // lt
        $lt_corner = $this->_get_lt_rounder_corner();
        imagecopymerge($image, $lt_corner, 0, 0, 0, 0, $radius, $radius, 100);
        // lb
        $lb_corner = imagerotate($lt_corner, 90, $trans_color);
        imagecopymerge($image, $lb_corner, 0, $image_height - $radius, 0, 0, $radius, $radius, 100);
        // rb
        $rb_corner = imagerotate($lt_corner, 180, $trans_color);
        imagecopymerge($image, $rb_corner, $image_width - $radius, $image_height - $radius, 0, 0, $radius, $radius, 100);
        // rt
        $rt_corner = imagerotate($lt_corner, 270, $trans_color);
        imagecopymerge($image, $rt_corner, $image_width - $radius, 0, 0, 0, $radius, $radius, 100);

        // set the transparency
        imagecolortransparent($image, $trans_color);
        // display it
       // header('Content-Type: image/png');
        imagepng($image, $newImage);

        imagedestroy($src_image);
        imagedestroy($image);
        imagedestroy($lt_corner);
        imagedestroy($lb_corner);
        imagedestroy($rb_corner);
        imagedestroy($rt_corner);
    }

}
