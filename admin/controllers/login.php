<?php
Session::init();

class Login extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("login/js/main.js");
        $this->view->css = array("login/css/main.css");
        $this->view->render("login/index", true);
    }

    public function log_in()
    {
        echo json_encode($this->model->log_in($_POST));
    }
}
