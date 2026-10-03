<?php
class Database
{
    private $host;
    private $db;
    private $user;
    private $password;
    private $charset;
    private $pdo;
    private $isTransactionActive = false; // Estado de la transacción

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->db = DB_NAME;
        $this->user = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }

    public function connect()
    {
        if ($this->pdo == null) {
            $options = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->charset,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            );
            try {
                $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->db}", $this->user, $this->password, $options);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }

    // Métodos para manejar transacciones
    public function beginTransaction()
    {
        if (!$this->isTransactionActive) {
            $this->connect()->beginTransaction();
            $this->isTransactionActive = true;
        }
    }

    public function commit()
    {
        if ($this->isTransactionActive) {
            $this->connect()->commit();
            $this->isTransactionActive = false;
        }
    }

    public function rollBack()
    {
        if ($this->isTransactionActive) {
            $this->connect()->rollBack();
            $this->isTransactionActive = false;
        }
    }

    // Método para verificar si hay una transacción activa
    public function inTransaction()
    {
        return $this->isTransactionActive;
    }
}
