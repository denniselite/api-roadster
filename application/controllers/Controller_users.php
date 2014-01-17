<?php

class Controller_users extends Controller {
    
    
    private $request;
    private $method;
    private $data_method;
    private $model;
    private $view;
    private $post_data;
    private $sid_status;
    
    function __construct() {
        $this->model = new Model_users();
        $this->model->ConnectToDB();
        $this->view = new View();
        $this->request = new Request();
        $this->method = $this->request->method();
        $this->data_method = $this->request->data();
        $this->post_data = json_decode($this->data_method, TRUE);
    }

    public function Action_index($id) {
            if ($id==""){
                if ($this->method == "POST"){
                    $this->create_user();
                } else
                if ($this->method == "GET"){
                    $this->get_users();
                } else {
                    $this->model->out_error("402");
                    }
            } else{
                if ($this->method == "GET"){
                    $this->get_profile($id);
                } else
                if ($this->method == "PUT"){
                    $this->update_profile($id);
                } else
                if ($this->method == "DELETE"){
                    $this->delete_user($id);
                } else {
                    $this->model->out_error("402");
                }
            }
    }
    
     private function check_data(){
        if (!$this->post_data){
            Controller::out_error("302");
            exit;
        }
        if (!isset($this->post_data['sid'])){
            Controller::out_error("302");
            exit;
        }
        if ($this->model->sid_check($this->post_data['sid'])){
            $this->sid_status = true;
        } else {
            $this->sid_status = false;
        }
    }
    
//    private function get_users() {
//        $this->check_data();
//        $page = $this->post_data['page'];
//        $this->view->generate('users', $this->model->get_users(--$page));
//    }
    
    private function create_user(){
        return 
        $this->view->generate('users',$this->model->create_user((object)$this->post_data));
            //$this->view->generate('users', $this->data_method);
    }
    
    private function delete_user($id){
        $this->check_data();
        $data = array ('uid' => $id);
        if ($this->sid_status){
            return
                $this->view->generate('users', $this->model->delete_user($data));
        } else {
            $this->model->out_error("301");
        }
    }
    
    private function get_profile($id){
        $this->check_data();
        $data = array ('uid' => $id);
        //return $this->view->generate('users',$this->model->get_profile($data));
        if ($this->sid_status){
            return $this->view->generate('users',$this->model->get_profile($data));
        } else {
            $this->model->out_error("301");
        }
        
    }
    
    private function update_profile($id){
        $this->check_data();
        if ($this->sid_status){
            return
                $this->view->generate('users',$this->model->update_user($id,(object)$this->post_data));
        } else {
            $this->model->out_error("301");
        }
    }
}

