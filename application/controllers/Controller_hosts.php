<?php

class Controller_hosts extends Controller {
    
    
    private $request;
    private $method;
    private $data_method;
    private $model;
    private $view;
    private $post_data;
    private $sid_status;
    
    function __construct() {
        $this->model = new Model_hosts();
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
                    $this->create_host();
                } else
                if ($this->method == "GET"){
                    $this->get_hosts();
                } else {
                    $this->model->out_error("402");
                    }
            } else{
                if ($this->method == "GET"){
                    $this->get_host($id);
                }
                if ($this->method == "PUT"){
                    $this->update_host($id);
                }
                if ($this->method == "DELETE"){
                    $this->delete_host($id);
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
    
    private function get_hosts() {
        $this->check_data();
        $page = $this->post_data['page'];
        $this->view->generate('hosts', $this->model->get_hosts(--$page));
    }
    
    private function create_host(){
        return 
        $this->view->generate('hosts',$this->model->create_host((object)$this->post_data));
           // $this->view->generate('hosts', $this->data_method);
    }
    
    private function delete_host($id){
        $this->check_data();
        $data = array ('id' => $id);
        if ($this->sid_status){
            return
                $this->view->generate('hosts', $this->model->delete_host($data));
        } else {
            $this->model->out_error("301");
        }
    }
    
    private function get_host($id){
        $this->check_data();
        $data = array ('id' => $id);
        if ($this->sid_status){
            return $this->view->generate('hosts',$this->model->get_host($data));
        } else {
            $this->model->out_error("301");
        }
        
    }
    
    private function update_host($id){
        $this->check_data();
        if ($this->sid_status){
            return
                $this->view->generate('hosts',$this->model->update_host($id,(object)$this->post_data));
        } else {
            $this->model->out_error("301");
        }
    }
}

