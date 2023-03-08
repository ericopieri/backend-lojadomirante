<?php
class Connection
{
    private $servername = "localhost:3306";
    private $username = "root";
    private $password = "111111";
    private $dbname = "desafio";
    protected $connection;

    public function __construct()
    {
        try {
            $pdo = new PDO("mysql:host={$this->servername};dbname={$this->dbname}", $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection = $pdo;
        } catch (PDOException $err) {
            die($err->getMessage());
        }
    }
}
