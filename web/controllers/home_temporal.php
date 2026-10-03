<?php
// require_once("Models/TCategoria.php");
// require_once("Models/TProducto.php");
Session::init();

class Home extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function render()
	{
		$this->view->data = $this->model->get_info_home();
		$this->view->render("home/home_temporal");
	}
}
