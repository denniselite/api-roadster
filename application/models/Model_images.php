<?php

Class Model_images extends Model{
    
    private $STH;
    private $page_size = 20;
    
            function __construct() {
                parent::__construct();
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
            $dir_for_images = md5($session->uid . "f96f44a304885f78f0b7dd2858333a5e");
            $dir = ("/var/www/roadster.su/users/" . $dir_for_images);
            if (!file_exists($dir)){
                mkdir($dir);
            }
            if ($data->avatar == "TRUE"){
                
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
    
    public function get_image($data = array()) {
        $this->STH = $this->DBH->prepare("SELECT * FROM images WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $image_info = $this->STH->fetch();
        if ($image_info instanceof stdClass){
            return $this->out_data($image_info);
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
    
    public function update_image($id, $data) {
        //return $this->out_data("OK");
        $this->STH = $this->DBH->prepare("SELECT * FROM images WHERE id = :id");
        $id = array ("id" => $id);
        $this->STH->execute($id);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $image_info = $this->STH->fetch();
        if ($image_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("UPDATE images SET "
                    . "time = :time, "
                    . "start = :start, "
                    . "end = :end, "
                    . "price = :price, "
                    . "trip-type = :trip-type, "
                    . "packs-mode = :packs-mode, "
                    . "repeat = :repeat, "
                    . "seats = :seats, "
                    . "3days = :3days, "
                    . "reserve_uid = :reserve_uid "
                    . "WHERE id = :id");
            unset($data->sid);
            $data->id = $id;
//            $new_data = array (
//                'email' => $data->email,
//                'pass' => $data->pass,
//                'id_vk' => $data->id_vk,
//                'id_fb' => $data->id_fb,
//                'firstname' => $data->firstname,
//                'secondname' => $data->secondname,
//                'avatar' => $data->avatar,
//                'about' => $data->about,
//                'uid' => $id
//            );
//            ob_start();
//            try {
//                $this->STH->execute($new_data);
//            } catch (PDOException $ex) {
//                echo $ex->getMessage();
//            }
//            $res = ob_get_clean();
            if ($this->STH->execute($data)){
                return $this->out_error('200');
            } else {
                return $this->out_error('300');
            }
        } else{
                return $this->out_error('300');
        }
    }
    
}
