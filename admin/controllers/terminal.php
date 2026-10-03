<?php
// Session::verify_permission("p_terminal");

class Terminal extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("terminal/css/main.css");
        $this->view->js = array("terminal/js/main.js");
        $this->view->php = array("terminal/php/modal.php");
        $this->view->render("terminal/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_terminal"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function get_terminal_personal()
    {
        echo json_encode($this->model->get_terminal_personal($_POST));
    }
}
