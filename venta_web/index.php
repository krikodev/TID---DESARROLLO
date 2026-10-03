<?php

require('../admin/config.php');

function banshee_autoload($class)
{
    require LIBS_WEB . $class . ".php";
}

spl_autoload_register("banshee_autoload");

$app = new App();
