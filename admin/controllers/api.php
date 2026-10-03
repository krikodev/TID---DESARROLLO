<?php

require("libs/modules/ApiQuery.php");
class Api extends Controller
{
    public $api;

    public function __construct()
    {
        parent::__construct();
    }

    final public static function reniec()
    {
        ApiQuery::reniec($_POST["docu"]);
    }

    public function sunat()
    {
        ApiQuery::sunat($_POST["docu"]);
    }

    public function generate_pass()
    {
        ApiQuery::generate_pass();
    }
}
