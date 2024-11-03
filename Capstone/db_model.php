<?php
class DatabaseModel {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "school_rfid_db";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            echo "Database connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
