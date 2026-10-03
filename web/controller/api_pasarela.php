<?php

require("libs/modules/ApiPasarela.php");
class ApiPasarela extends Controller
{
    public $api_pasarela;

    public function __construct()
    {
        parent::__construct();
    }

    final public static function generarToken_pasarela()
    {
        echo("Hola mundo llegando hasta el controlador"); die;
        ApiPasarela::generarToken_pasarela($_POST["monto_pagar"]);
    }
    
}
