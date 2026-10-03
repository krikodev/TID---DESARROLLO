<?php

require('config.php');
require_once("Helpers/helpers.php");
require __DIR__ . '/vendor/autoload.php';

function banshee_autoload($class)
{
    require LIBS . $class . ".php";
}

spl_autoload_register("banshee_autoload");

$app = new App();
