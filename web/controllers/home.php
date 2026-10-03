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
		$this->view->css = array("home/css/main.css", "home/css/encomienda.css", "home/css/buscador.css");
		$this->view->js = array("home/js/main.js", "home/js/viajes.js", "home/js/programaciones.js", "home/js/buscador.js");
		$this->view->render("home/index");
	}
	
	public function reclamo()
	{
		$this->view->css = array("home/css/reclamo.css");
		$this->view->render("home/php/reclamo", true);
	}

	public function login()
	{
		//$this->view->css = array("home/css/login.css");
		$this->view->js = array("home/js/login.js");
		$this->view->render("home/php/login", true);
	}

	public function admin_reclamo()
	{
		$this->view->css = array("home/css/admin_reclamo.css");
		$this->view->js = array("home/js/admin_reclamo.js");
		$this->view->render("home/php/admin_reclamo", true);
	}

	public function buscar_programaciones()
	{
		echo json_encode($this->model->buscar_programaciones($_POST));
		//echo json_encode($this->model->consultar_cdr($_POST));
	}

	public function about()
	{
		$this->view->css = array("home/css/main.css");
		$this->view->js = array("home/js/main.js");
		$this->view->render("home/about");
	}

	public function service()
	{
		$this->view->css = array("home/css/main.css");
		$this->view->js = array("home/js/main.js");
		$this->view->render("home/service");
	}

	public function contact()
	{
		$this->view->css = array("home/css/main.css");
		$this->view->js = array("home/js/main.js");
		$this->view->render("home/contact");
	}
}
