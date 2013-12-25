<?php

Class Model_packs extends Model{
    
    private $STH;
    private $page_size = 20;
    
            function __construct() {
                parent::__construct();
    }

    public function create_pack($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM sessions WHERE sid = ?");
        $this->STH->execute(array($data->sid));
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $session = $this->STH->fetch();
        
        if (!($session instanceof stdClass)){
                    return $this->out_error('300');
        }
        if ($session instanceof stdClass){
            $this->STH = 
                $this->DBH->prepare("INSERT INTO packs (date_start, date_end, coords_start, coords_end, start, end, price, weight, uid) "
                    . "VALUES (:date_start, :date_end, :coords_start, :coords_end, :start, :end, :price, :weight, :uid)");    

            unset($data->sid);
            $data->uid = $session->uid;
//            return $this->out_error('201');
            ob_start();
            try {
                $this->STH->execute((array)$data);
            } catch (PDOException $ex) {
                echo $ex->getMessage();
            }
            $res = ob_get_clean();
            return $this->out_error('OK');
            $this->out_error('201');
        } else{
                return $this->out_error('300');
        }
    }
    
    public function delete_pack($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM packs WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $pack_info = $this->STH->fetch();
        if ($pack_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("DELETE FROM packs WHERE id = :id");
            $this->STH->execute($data);
            return $this->out_data('200');
        } else {
            return $this->out_error('300');
        }
    }
    
    public function get_pack($data = array()) {
        $this->STH = $this->DBH->prepare("SELECT * FROM packs WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $pack_info = $this->STH->fetch();
        if ($pack_info instanceof stdClass){
            return $this->out_data($pack_info);
        } else {
            return $this->out_error('401');
        }
    }
    
    public function get_packs($page) {
        $count = $this->page_size*$page;
        $this->STH = $this->DBH->prepare("SELECT * FROM packs ORDER BY uid DESC LIMIT :count, 20");
        $data = array ('count' => $count);
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_ASSOC);
        $packs_all = $this->STH->fetch();
        $packs = array ();
        while ($row = $packs_all) {
                    array_push($packs, $row);
        }
        if ($packs !=''){
            return Model::out_data($packs);
        } else {
            return Model::out_error('400');
        }
    }
    
    public function update_pack($id, $data) {
        //return $this->out_data("OK");
        $this->STH = $this->DBH->prepare("SELECT * FROM packs WHERE id = :id");
        $id = array ("id" => $id);
        $this->STH->execute($id);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $pack_info = $this->STH->fetch();
        if ($pack_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("UPDATE packs SET "
                    . "date_start = :date_start, "
                    . "date_end = :date_end, "
                    . "coords_start = :icoords_start, "
                    . "coords_end = :coords_end, "
                    . "start = :start, "
                    . "end = :end, "
                    . "price = :price, "
                    . "weight = :weight "
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
