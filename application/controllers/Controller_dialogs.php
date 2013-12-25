<?php

class Controller_dialogs extends Controller {
    
    
    private $request;
    private $method;
    private $data_method;
    private $model;
    private $view;
    private $post_data;
    private $sid_status;
    
    function __construct() {
        $this->model = new Model_dialogs();
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
                    $this->create_dialog();
                } else
                if ($this->method == "GET"){
                    $this->get_dialogs();
                } else {
                    $this->model->out_error("402");
                    }
            } else{
                if ($this->method == "GET"){
                    $this->get_dialog($id);
                }
//                if ($this->method == "PUT"){
//                    $this->update_dialog($id);
//                }
                if ($this->method == "DELETE"){
                    $this->delete_dialog($id);
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
    
    private function get_dialogs() {
        $this->check_data();
        $page = $this->post_data['page'];
        $this->view->generate('dialogs', $this->model->get_dialogs(--$page));
    }
    
    private function create_dialog(){
        return 
        $this->view->generate('dialogs',$this->model->create_dialog((object)$this->post_data));
           // $this->view->generate('dialogs', $this->data_method);
    }
    
    private function delete_dialog($id){
        $this->check_data();
        $data = array ('id' => $id);
        if ($this->sid_status){
            return
                $this->view->generate('dialogs', $this->model->delete_dialog($data));
        } else {
            $this->model->out_error("301");
        }
    }
    
    private function get_dialog($id){
        $this->check_data();
        //$data = array ('id' => $id);
        if ($this->sid_status){
            return $this->view->generate('dialogs',$this->model->get_dialog($this->post_data));
        } else {
            $this->model->out_error("301");
        }
        
    }
    
//    private function update_dialog($id){
//        $this->check_data();
//        if ($this->sid_status){
//            return
//                $this->view->generate('dialogs',$this->model->update_dialog($id,(object)$this->post_data));
//        } else {
//            $this->model->out_error("301");
//        }
//    }
}

