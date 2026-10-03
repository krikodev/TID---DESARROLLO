<?php

class Errors extends Controller
{

    function __construct()
    {
        parent::__construct();
    }

    function render()
    {
        $this->view->css = array("error/css/main.css");
        $this->view->render("error/index");
    }
}
