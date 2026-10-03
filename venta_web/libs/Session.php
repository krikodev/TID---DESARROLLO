<?php

class Session
{

	public static function init()
	{
		@session_start();
	}

	public static function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	public static function get($key)
	{
		if (isset($_SESSION[$key]))
			return $_SESSION[$key];
	}

	public static function destroy()
	{
		session_destroy();
	}

	public static function verify_permission($permiso)
	{
		Session::init();
		$data_permisos = Session::get("data_permisos");
		$data_permisos[$permiso] ? '' : header('location: ' . URL . 'error');
	}
}
