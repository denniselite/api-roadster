<?php

Class Model_images extends Model{
    
    private $STH;
    private $page_size = 20;
    
            function __construct() {
                parent::__construct();
                error_reporting(0);
    }  

    public function create_uri_for_upload($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM sessions WHERE sid = ?");
        $this->STH->execute(array($data->sid));
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $session = $this->STH->fetch();
        if (!($session instanceof stdClass)){
                    return $this->out_error('300');
        }
        if ($session instanceof stdClass){
            $dir_for_images = $session->uid;
            $dir = ("/var/www/roadster.su/users/" . $session->uid);
            if (!file_exists($dir)){
                mkdir($dir);
                //$this->STH = $this->DBH->prepare("UPDATE  WHERE sid = ?");
                //$this->STH->execute(array($data->sid));
            }
            if ($data->avatar == "TRUE"){
                $dir = ("/var/www/roadster.su/users/" . $dir_for_images . "/avatar");
                if (!file_exists($dir)){
                    mkdir($dir);
                } 
                $upload_key =md5($session->uid . "aaca0f5eb4d2d98a6ce6dffa99f8254b" . time());
                $this->STH = $this->DBH->prepare("SELECT * FROM images WHERE uid = :uid AND avatar = TRUE");
                $uid = array('uid' => $session->uid);
                $this->STH->execute($uid);
                $this->STH->setFetchMode(PDO::FETCH_OBJ);
                $image = $this->STH->fetch();
                if (!($image instanceof stdClass)){
                    $this->STH = $this->DBH->prepare("INSERT INTO images (uid, image_path, avatar, upload_key) VALUES (:uid, :image_path, '1', :upload_key)");
                    $image = array(
                        'uid' => $session->uid,
                        'image_path' => 'http://roadster.su/users/' . $dir_for_images . '/avatar/',
                        'upload_key' => $upload_key,
                            );
                    $this->STH->execute($image);
                } else {
                    $this->STH = $this->DBH->prepare("UPDATE images SET "
                            . "image_path = :image_path, "
                            . ""
                            . "upload_key = :upload_key WHERE (uid = :uid AND avatar = '1')");
                    $image = array(
                        'image_path' => 'http://roadster.su/users/' . $dir_for_images . '/avatar/',
                        'upload_key' => $upload_key,
                        'uid' => $session->uid
                            );
                    $this->STH->execute($image);
                }
                $upload_key = array ('upload_key' => $upload_key);
                return $this->out_data($upload_key);
            } else {
                $this->STH = $this->DBH->prepare("INSERT INTO images (uid, image_path, avatar, upload_key) VALUES (:uid, :image_path, 'FALSE', :upload_key)");
                $upload_key = md5($session->uid . time() . $session->uid);    
                $image = array(
                        'uid' => $session->uid,
                        'image_path' => 'http://roadster.su/users/' . $dir_for_images,
                        'upload_key' => $upload_key
                            );
                    $this->STH->execute($image);
                $upload_key = array ('upload_key' => $upload_key);
                return $this->out_data($upload_key);
            }
            
        } else{
                return $this->out_error('300');
        }
    }
    
    public function delete_image($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM images WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $image_info = $this->STH->fetch();
        if ($image_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("DELETE FROM images WHERE id = :id");
            $this->STH->execute($data);
            return $this->out_data('200');
        } else {
            return $this->out_error('300');
        }
    }
    
    public function get_image($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM images WHERE id = :id");
        $id = array ('id' => $data->id);
        $this->STH->execute($id);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $image_info = $this->STH->fetch();
        if ($image_info instanceof stdClass){
            if (false){
                $ext = substr($image_info->image_name, 1 + strrpos($image_info->image_name, "."));
                $name = substr($image_info->image_name, 0, strrpos($image_info->image_name, "."));
                echo ($image_info->image_path . "/" . $name . "_small." . $ext);
            }
            echo ($image_info->image_path . "/" . $image_info->image_name);
        } else {
            return $this->out_error('401');
        }
    }
    
    public function get_images($page) {
        $count = $this->page_size*$page;
        $this->STH = $this->DBH->prepare("SELECT * FROM images ORDER BY id DESC LIMIT :count, 20");
        $data = array ('count' => $count);
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_ASSOC);
        $images_all = $this->STH->fetch();
        $images = array ();
        while ($row = $images_all) {
                    array_push($images, $row);
        }
        if ($images !=''){
            return Model::out_data($images);
        } else {
            return Model::out_error('400');
        }
    }
    
    public function upload_image($id) {
        $valid_types = array("jpg", "png", "jpeg");
        $this->STH = $this->DBH->prepare("SELECT * FROM images WHERE upload_key = :upload_key");
        $data = array ('upload_key' => $id);
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $image_info = $this->STH->fetch();
        $uri_for_upload = $image_info->uid;
        $uri_for_upload = '/var/www/roadster.su/users/' . $uri_for_upload . '/';
        if ($image_info->avatar == '1'){
            $uri_for_upload .= "avatar/";
            unlink($uri_for_upload . $image_info->image_name . ".jpg");
            unlink($uri_for_upload . $image_info->image_name . "_small.jpg");
        }
        //return $this->out_error($uri_for_upload);
        if (file_exists($uri_for_upload)){
            $ext = substr($_FILES['userfile']['name'], 
			1 + strrpos($_FILES['userfile']['name'], "."));
            if (!in_array($ext, $valid_types)){
                $this->out_error('300');
            }
            if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uri_for_upload . 
                    md5($_FILES['userfile']['name'] . time()) . "." . $ext)) {
                
                $params = array(
                    'width' => 200,
                    'height' => 200,
                    'aspect_ratio' => true,
                    //'rgb' => '0x000000',
                    'crop' => true
                );
                $ini_path = $uri_for_upload . md5($_FILES['userfile']['name'] . time()) . "." . $ext;
                if ($ext != "jpg"){
                $this->convert_to_jpg($ini_path, $uri_for_upload . md5($_FILES['userfile']['name'] . time()) . ".jpg");
                unlink($ini_path);
                }
                $ini_path = $uri_for_upload . md5($_FILES['userfile']['name'] . time()) . ".jpg";
                $dest_path = $uri_for_upload . md5($_FILES['userfile']['name'] . time()) . "_small.jpg";
                $this->img_resize($ini_path, $dest_path, $params);
                $this->STH = $this->DBH->prepare("UPDATE images SET "
                    . "image_name = :image_name "
                    . "WHERE id = :id");
                $image = array(
                    'image_name' => md5($_FILES['userfile']['name'] . time()),
                    'id' => $image_info->id,
                );
                 $this->STH->execute($image);
                 $this->STH = $this->DBH->prepare("UPDATE users SET "
                    . "avatar = :image_name "
                    . "WHERE id = :id");
                $image = array(
                    'image_name' => md5($_FILES['userfile']['name'] . time()),
                    'id' => $image_info->id,
                );
                 $this->STH->execute($image);
                 $data = array ('id' => $image_info->id);
                return $this->out_data($data);
            }
        }
    }
    
    private function img_resize($ini_path, $dest_path, $params = array()) {
    $width = !empty($params['width']) ? $params['width'] : null;
    $height = !empty($params['height']) ? $params['height'] : null;
    $constraint = !empty($params['constraint']) ? $params['constraint'] : false;
    $rgb = !empty($params['rgb']) ?  $params['rgb'] : 0xFFFFFF;
    $quality = !empty($params['quality']) ?  $params['quality'] : 100;
    $aspect_ratio = isset($params['aspect_ratio']) ?  $params['aspect_ratio'] : true;
    $crop = isset($params['crop']) ?  $params['crop'] : true;
 
    if (!file_exists($ini_path)) return false;
 
 
    if (!is_dir($dir=dirname($dest_path))) mkdir($dir);
 
    $img_info = getimagesize($ini_path);
    if ($img_info === false) return false;
 
    $ini_p = $img_info[0]/$img_info[1];
    if ( $constraint ) {
        $con_p = $constraint['width']/$constraint['height'];
        $calc_p = $constraint['width']/$img_info[0];
 
        if ( $ini_p < $con_p ) {
            $height = $constraint['height'];
            $width = $height*$ini_p;
        } else {
            $width = $constraint['width'];
            $height = $img_info[1]*$calc_p;
        }
    } else {
        if ( !$width && $height ) {
            $width = ($height*$img_info[0])/$img_info[1];
        } else if ( !$height && $width ) {
            $height = ($width*$img_info[1])/$img_info[0];
        } else if ( !$height && !$width ) {
            $width = $img_info[0];
            $height = $img_info[1];
        }
    }
 
    preg_match('/\.([^\.]+)$/i',basename($dest_path), $match);
    $ext = $match[1];
    $output_format = ($ext == 'jpg') ? 'jpeg' : $ext;
 
    $format = strtolower(substr($img_info['mime'], strpos($img_info['mime'], '/')+1));
    $icfunc = "imagecreatefrom" . $format;
 
    $iresfunc = "image" . $output_format;
 
    if (!function_exists($icfunc)) return false;
 
    $dst_x = $dst_y = 0;
    $src_x = $src_y = 0;
    $res_p = $width/$height;
    if ( $crop && !$constraint ) {
        $dst_w  = $width;
        $dst_h = $height;
        if ( $ini_p > $res_p ) {
            $src_h = $img_info[1];
            $src_w = $img_info[1]*$res_p;
            $src_x = ($img_info[0] >= $src_w) ? floor(($img_info[0] - $src_w) / 2) : $src_w;
        } else {
            $src_w = $img_info[0];
            $src_h = $img_info[0]/$res_p;
            $src_y    = ($img_info[1] >= $src_h) ? floor(($img_info[1] - $src_h) / 2) : $src_h;
        }
    } else {
        if ( $ini_p > $res_p ) {
            $dst_w = $width;
            $dst_h = $aspect_ratio ? floor($dst_w/$img_info[0]*$img_info[1]) : $height;
            $dst_y = $aspect_ratio ? floor(($height-$dst_h)/2) : 0;
        } else {
            $dst_h = $height;
            $dst_w = $aspect_ratio ? floor($dst_h/$img_info[1]*$img_info[0]) : $width;
            $dst_x = $aspect_ratio ? floor(($width-$dst_w)/2) : 0;
        }
        $src_w = $img_info[0];
        $src_h = $img_info[1];
    }
 
    $isrc = $icfunc($ini_path);
    $idest = imagecreatetruecolor($width, $height);
    if ( ($format == 'png' || $format == 'gif') && $output_format == $format ) {
        imagealphablending($idest, false);
        imagesavealpha($idest,true);
        imagefill($idest, 0, 0, IMG_COLOR_TRANSPARENT);
        imagealphablending($isrc, true);
        $quality = 0;
    } else {
        imagefill($idest, 0, 0, $rgb);
    }
    imagecopyresampled($idest, $isrc, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    $res = $iresfunc($idest, $dest_path, $quality);
 
    imagedestroy($isrc);
    imagedestroy($idest);
 
    return $res;
    }
    
    private function convert_to_jpg($originalFile, $outputFile){
    if (file_exists($originalFile)) {
        $type = getimagesize($originalFile);
        if ($type['mime'] == "image/png") {
            $source = imagecreatefrompng($originalFile);
            $image = imagecreatetruecolor(imagesx($source), imagesy($source));
          
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);
       
            imagecopy($image, $source, 0, 0, 0, 0, imagesx($image), imagesy($image));
    
            imagejpeg($image, $outputFile, 75);
            imagedestroy($image);
            imagedestroy($source);
         } else {
            copy($originalFile, $outputFile);
         }
      }
   }
    
}
