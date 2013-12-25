<?php

Class Model_dialogs extends Model{
    
    private $STH;
    private $page_size = 20;
    private $dialog;
            
            function __construct() {
                parent::__construct();
    }

    public function create_dialog($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM sessions WHERE sid = ?");
        $this->STH->execute(array($data->sid));
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $session = $this->STH->fetch();
        
        if (!($session instanceof stdClass)){
                    return $this->out_error('300');
        }
        if ($session instanceof stdClass){
           if ($data->dialog_id == '')
           {
               $this->STH = $this->DBH->prepare("INSERT INTO dialogs (uid) VALUES (:send_id)");
               $this->STH->execute(array($session->uid));
               $this->STH = $this->DBH->prepare("SELECT * FROM dialogs WHERE uid = :send_id ORDER BY id DESC LIMIT 1");
               $this->STH->execute(array($session->uid));
               $this->STH->setFetchMode(PDO::FETCH_OBJ);
               $this->dialog = $this->STH->fetch();
           }
            $this->STH = 
                $this->DBH->prepare("INSERT INTO messages (send_id, recip_id, dialog_id, text, attachment, attach_body) "
                    . "VALUES (:send_id, :recip_id, :dialog_id, :text, :attachment, :attach_body)");    
            
            unset($data->sid);
            $data->send_id = $session->uid;
            $data->dialog_id = $this->dialog->id;
//            return $this->out_error('201');
            ob_start();
            try {
                $this->STH->execute((array)$data);
            } catch (PDOException $ex) {
                echo $ex->getMessage();
            }
            $res = ob_get_clean();
            $this->STH = $this->DBH->prepare("SELECT * FROM messages WHERE dialog_id = :dialog_id ORDER BY time DESC LIMIT 1");
            $this->STH->execute(array($session->uid));
            $this->STH->setFetchMode(PDO::FETCH_OBJ);
            $data_for_msg = array ('dialog_id' => $data->dialog_id);
            $this->STH->execute($data_for_msg);
            $message_info = $this->STH->execute();
            $this->STH = $this->DBH->prepare("UPDATE dialogs SET last_msg_id = :last_msg_id WHERE id = :id ");
            $update_data = array ('last_msg_id' => $message_info->id);
            $this->STH->execute($update_data);
            return $this->out_error('201');
        } else{
                return $this->out_error('300');
        }
    }
    
    public function delete_dialog($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM dialogs WHERE id = :id");
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $dialog_info = $this->STH->fetch();
        if ($dialog_info instanceof stdClass){
            $this->STH = $this->DBH->prepare("DELETE FROM dialogs WHERE id = :id");
            $this->STH->execute($data);
            $this->STH = $this->DBH->prepare("DELETE FROM messages WHERE dialog_id = :dialog_id");
            $dialog_id = array ('dialog_id' => $dialog_info->id);
            $this->STH->execute($dialog_id);
            return $this->out_data('200');
        } else {
            return $this->out_error('300');
        }
    }
    
    public function get_dialog($data) {
        $this->STH = $this->DBH->prepare("SELECT * FROM dialogs WHERE id = :id");
        $id = array ('id' => $data->dialog_id);
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_OBJ);
        $dialog_info = $this->STH->fetch();
        if ($dialog_info instanceof stdClass){
            $count = $this->page_size*$data->page;
            $this->STH = $this->DBH->prepare("SELECT * FROM messsages WHERE dialog_id = :dialog_id ORDER BY time DESC LIMIT :count, 20");
            $data_for_msg = array ('dialog_id' => $data->dialog_id ,'count' => $count);
            $this->STH->execute($data_for_msg);
            $this->STH->setFetchMode(PDO::FETCH_ASSOC);
            $messages = $this->STH->fetch();
            $dialog = array ('dialog_info' => $dialog_info, 'messages' => $messages);
            return $this->out_data($dialog);
        } else {
            return $this->out_error('401');
        }
    }
    
    public function get_dialogs($page) {
        $count = $this->page_size*$page;
        $this->STH = $this->DBH->prepare("SELECT * FROM dialogs ORDER BY uid DESC LIMIT :count, 20");
        $data = array ('count' => $count);
        $this->STH->execute($data);
        $this->STH->setFetchMode(PDO::FETCH_ASSOC);
        $dialogs_all = $this->STH->fetch();
        $dialogs = array ();
        while ($row = $dialogs_all) {
                    array_push($dialogs, $row);
        }
        if ($dialogs !=''){
            return Model::out_data($dialogs);
        } else {
            return Model::out_error('400');
        }
    }
    
//    public function update_dialog($id, $data) {
//        return $this->out_data("OK");
//        $this->STH = $this->DBH->prepare("SELECT * FROM dialogs WHERE id = :id");
//        $id = array ("id" => $id);
//        $this->STH->execute($id);
//        $this->STH->setFetchMode(PDO::FETCH_OBJ);
//        $dialog_info = $this->STH->fetch();
//        if ($dialog_info instanceof stdClass){
//            $this->STH = $this->DBH->prepare("UPDATE dialogs SET "
//                    . "date_start = :date_start, "
//                    . "date_end = :date_end, "
//                    . "coords_start = :icoords_start, "
//                    . "coords_end = :coords_end, "
//                    . "start = :start, "
//                    . "end = :end, "
//                    . "price = :price, "
//                    . "weight = :weight "
//                    . "WHERE id = :id");
//            unset($data->sid);
//            $data->id = $id;
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
//            if ($this->STH->execute($data)){
//                return $this->out_error('200');
//            } else {
//                return $this->out_error('300');
//            }
//        } else{
//                return $this->out_error('300');
//        }
//    }
    
}
