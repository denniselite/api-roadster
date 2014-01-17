<?php

class Controller_images extends Controller {
    
    
    private $request;
    private $method;
    private $data_method;
    private $model;
    private $view;
    private $post_data;
    private $sid_status;
    
    function __construct() {
        $this->model = new Model_images();
        $this->model->ConnectToDB();
        $this->view = new View();
        $this->request = new Request();
        $this->method = $this->request->method();
        $this->data_method = $this->request->data();
        $this->post_data = json_decode($this->data_method, TRUE);
    }

    public function Action_index($id) {
            if ($id==""){
                if ($this->method == "GET"){
                    $this->get_images();
                } else
                if ($this->method == "POST"){
                    $this->create_uri_for_upload();
                }
                else {
                    $this->model->out_error("402");
                    }
            } else{
                if ($this->method == "GET"){
                    $this->get_image($id);
                } else 
                if ($this->method == "POST"){
                    $this->upload_image($id);
                } else
                if ($this->method == "DELETE"){
                    $this->delete_image($id);
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
    
    private function get_images() {
        $this->check_data();
        $page = $this->post_data['page'];
        $this->view->generate('images', $this->model->get_images(--$page));
    }
    
    private function create_uri_for_upload(){
        $this->check_data();
        return 
        $this->view->generate('images',$this->model->create_uri_for_upload((object)$this->post_data));
           // $this->view->generate('images', $this->data_method);
    }
    
    private function delete_image($id){
        $this->check_data();
        $data = array ('id' => $id);
        if ($this->sid_status){
            return
                $this->view->generate('images', $this->model->delete_image($data));
        } else {
            $this->model->out_error("301");
        }
    }
    
    private function get_image($id){
        //$this->check_data();
        $data = new stdClass();
        $data->id = $id;
         return $this->view->generate('images',$this->model->get_image($data));
//        $data->size = $this->post_data['size'];
        if ($this->sid_status){
            return $this->view->generate('images',$this->model->get_image($data));
        } else {
            $this->model->out_error("301");
        }
        
    }
    
    private function upload_image($id){
        //$this->check_data();
        //if ($this->sid_status){
            return
                $this->view->generate('images',$this->model->upload_image($id));
//        } else {
//            $this->model->out_error("301");
//        }
    }
}

