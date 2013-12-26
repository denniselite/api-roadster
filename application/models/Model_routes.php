<?php

Class Model_routes extends Model{
    
    private $STH;
    private $page_size = 20;
    
            function __construct() {
                parent::__construct();
    }

    public function create_route($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM sessions WHERE sid = ?");
        $this->STH->execute(array($data->sid));
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $session = $this->STH->fetch();
        
        if (!($session instanceof stdClass)){
                    return $this->out_error('300');
        }
        if ($session instanceof stdClass){
            $this->STH = 
                $this->DBH->prepare("INSERT INTO routes (uid, time, start, end, price, trip-type, packs-mode, repeat, seats, 3days, reserve_uid) "
                    . "VALUES (:uid, :time, :start, :end, :price, :trip-type, :packs-mode, :repeat, :seats, :3days, :reserve_uid)");    

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
    
    public function delete_route($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM routes WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $route_info = $this->STH->fetch();
        if ($route_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("DELETE FROM routes WHERE id = :id");
            $this->STH->execute($data);
            return $this->out_data('200');
        } else {
            return $this->out_error('300');
        }
    }
    
    public function get_route($data = array()) {
        $this->STH = $this->DBH->prepare("SELECT * FROM routes WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $route_info = $this->STH->fetch();
        if ($route_info instanceof stdClass){
            return $this->out_data($route_info);
        } else {
            return $this->out_error('401');
        }
    }
    
    public function get_routes($page) {
        $count = $this->page_size*$page;
        $this->STH = $this->DBH->prepare("SELECT * FROM routes ORDER BY id DESC LIMIT :count, 20");
        $data = array ('count' => $count);
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_ASSOC);
        $routes_all = $this->STH->fetch();
        $routes = array ();
        while ($row = $routes_all) {
                    array_push($routes, $row);
        }
        if ($routes !=''){
            return Model::out_data($routes);
        } else {
            return Model::out_error('400');
        }
    }
    
    public function update_route($id, $data) {
        //return $this->out_data("OK");
        $this->STH = $this->DBH->prepare("SELECT * FROM routes WHERE id = :id");
        $id = array ("id" => $id);
        $this->STH->execute($id);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $route_info = $this->STH->fetch();
        if ($route_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("UPDATE routes SET "
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
