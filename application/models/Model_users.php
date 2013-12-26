<?php

Class Model_users extends Model{
    
    private $STH;
    private $page_size = 20;
    
            function __construct() {
                parent::__construct();
    }

    public function create_user($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM users WHERE email = ?");
        $this->STH->execute(array($data->email));
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $user_info = $this->STH->fetch();
        if ($user_info instanceof stdClass){
                    return $this->out_error('300');
        }
        if (!($user_info instanceof stdClass)){
           // $dir_for_user = (string)md5($data->email);
//            ob_start();
//            try {
//            mkdir("/var/www/roadster.su/users/" . $dir_for_user);
//            } catch (Exception $ex) {
//                echo $ex->getMessage();
//            }
//            $res = ob_get_clean();
//            //return $this->out_error($res);
            //mkdir("/var/www/roadster.su/users/" . $dir_for_user . "/avatar");
            $this->STH = 
                $this->DBH->prepare("INSERT INTO users (email, pass, firstname, secondname, about, partner, approved, device_token) "
                    . "VALUES (:email, :pass, :firstname, :secondname, :about, :partner, 'TRUE', :device_token)");    
            if ($this->STH->execute((array)$data)){
                    return $this->out_data($this->create_sid($data->email,$data->device_token));
                } else {
                    return $this->out_error('300');
                }
        } else{
                return $this->out_error('300');
        }
    }
    
    public function delete_user($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM users WHERE uid = :uid");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $user_info = $this->STH->fetch();
        if ($user_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("DELETE FROM users WHERE uid = :uid");
            $this->STH->execute($data);
            $this->STH = $this->DBH->prepare("DELETE FROM sessions WHERE uid = :uid");
            $this->STH->execute($data);
            return $this->out_error('200');
        } else {
            return $this->out_error('300');
        }
    }
    
    public function get_profile($data = array()) {
        $this->STH = $this->DBH->prepare("SELECT uid, email, time_reg, id_vk, id_fb, firstname, secondname, avatar, about, partner, approved FROM users WHERE uid = :uid");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $user_info = $this->STH->fetch();
        if ($user_info instanceof stdClass){
            return $this->out_data($user_info);
        } else {
            return $this->out_error('401');
        }
    }
    
    public function get_users($page) {
        $count = $this->page_size*$page;
        $this->new_query = mysql_query("SELECT * FROM users ORDER BY uid DESC LIMIT ".$count.", 20");
        $users = array ();
        while ($row = mysql_fetch_array($this->new_query)) {
            $user = array ('uid' => $row['uid'],
                       'e-mail' => $row['email']
                    );
                    array_push($users, $user);
        }
        if ($users !=''){
            return Model::out_data($users);
        } else {
            return Model::out_error('400');
        }
    }
    
    public function update_user($id, $data) {
        //return $this->out_data("OK");
        $this->STH = $this->DBH->prepare("SELECT * FROM users WHERE uid = :uid");
        $uid = array ("uid" => $id);
        $this->STH->execute($uid);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $user_info = $this->STH->fetch();
        if ($user_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("UPDATE users SET "
                    . "email = :email, "
                    . "pass = :pass, "
                    . "id_vk = :id_vk, "
                    . "id_fb = :id_fb, "
                    . "firstname = :firstname, "
                    . "secondname = :secondname, "
                    . "avatar = :avatar, "
                    . "about = :about "
                    . "WHERE uid = :uid");
            $new_data = array (
                'email' => $data->email,
                'pass' => $data->pass,
                'id_vk' => $data->id_vk,
                'id_fb' => $data->id_fb,
                'firstname' => $data->firstname,
                'secondname' => $data->secondname,
                'avatar' => $data->avatar,
                'about' => $data->about,
                'uid' => $id
            );
//            ob_start();
//            try {
//                $this->STH->execute($new_data);
//            } catch (PDOException $ex) {
//                echo $ex->getMessage();
//            }
//            $res = ob_get_clean();
            if ($this->STH->execute($new_data)){
                return $this->out_error('200');
            } else {
                return $this->out_error('300');
            }
        } else{
                return $this->out_error('300');
        }
    }
    
}
