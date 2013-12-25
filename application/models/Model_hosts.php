<?php

Class Model_hosts extends Model{
    
    private $STH;
    private $page_size = 20;
    
            function __construct() {
                parent::__construct();
    }

    public function create_host($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM sessions WHERE sid = ?");
        $this->STH->execute(array($data->sid));
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $session = $this->STH->fetch();
        
        if (!($session instanceof stdClass)){
                    return $this->out_error('300');
        }
        if ($session instanceof stdClass){
            $this->STH = 
                $this->DBH->prepare("INSERT INTO hosts (uid, time, location, price, repeat) "
                    . "VALUES (:uid, :time, :location, :price, :repeat)");    

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
    
    public function delete_host($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM hosts WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $host_info = $this->STH->fetch();
        if ($host_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("DELETE FROM hosts WHERE id = :id");
            $this->STH->execute($data);
            return $this->out_data('200');
        } else {
            return $this->out_error('300');
        }
    }
    
    public function get_host($data = array()) {
        $this->STH = $this->DBH->prepare("SELECT * FROM hosts WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $host_info = $this->STH->fetch();
        if ($host_info instanceof stdClass){
            return $this->out_data($host_info);
        } else {
            return $this->out_error('401');
        }
    }
    
    public function get_hosts($page) {
        $count = $this->page_size*$page;
        $this->STH = $this->DBH->prepare("SELECT * FROM hosts ORDER BY id DESC LIMIT :count, 20");
        $data = array ('count' => $count);
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_ASSOC);
        $hosts_all = $this->STH->fetch();
        $hosts = array ();
        while ($row = $hosts_all) {
                    array_push($hosts, $row);
        }
        if ($hosts !=''){
            return Model::out_data($hosts);
        } else {
            return Model::out_error('400');
        }
    }
    
    public function update_host($id, $data) {
        //return $this->out_data("OK");
        $this->STH = $this->DBH->prepare("SELECT * FROM hosts WHERE id = :id");
        $id = array ("id" => $id);
        $this->STH->execute($id);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $host_info = $this->STH->fetch();
        if ($host_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("UPDATE hosts SET "
                    . "time = :time, "
                    . "location = :location, "
                    . "price = :price, "
                    . "repeat = :repeat"
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
