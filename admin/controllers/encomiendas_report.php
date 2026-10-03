<?php

class Encomiendas_Report extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("encomiendas_report/css/main.css");
        $this->view->js = array("encomiendas_report/js/main.js");
        $this->view->php = array("encomiendas_report/php/modal.php");
        $this->view->render("encomiendas_report/index");
    }

    public function dataTable()
    {
       echo json_encode($this->model->getDataTable($_POST));
    }

}
