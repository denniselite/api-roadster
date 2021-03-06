<?php

class Controller_subscribers extends Controller{
    
    private $request;
    private $method;
    private $data_method;
    private $model;
    private $view;
    
    function __construct() {
        parent::__construct();
        $this->model = new Model_subscribers();
        $this->model->ConnectToDB();
        $this->view = new View();
        $this->request = new Request();
        $this->method = $this->request->method();
        $this->data_method = $this->request->data();
    }
    public function Action_index($id){
        if ($id==''){
            if ($this->method == "GET") $this->Action_subscribers();
                else Controller::out_error("402");
        } else {
            if ($this->method == "GET") $this->Action_subscriber($id);
                else Controller::out_error("402");
        }
    }

    public function Action_subscribers(){
        $this->view->generate('subscribers', $this->model->getSubscribers());
    }
    
    public function Action_subscriber($id_subs){
        $this->view->generate('subscribers', $this->model->getSubscriber($id_subs));
    }
}