<?php

require_once(dirname(__FILE__) . "/../admin/config.php");
class Conexion
{
  private $servername = DB_HOST;
  private $username = DB_USER;
  private $password = DB_PASS;
  private $dbname = DB_NAME;
  private $conn;

  public function __construct()
  {
    try {
      $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);
      // Configurar el modo de error de PDO a excepción
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      throw new Exception("La conexión ha fallado: " . $e->getMessage());
    }
  }

  public function getConnection()
  {
    return $this->conn;
  }
}

?>