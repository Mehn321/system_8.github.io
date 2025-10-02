<?php
/**
 * Database Connection Class
 * Handles MySQLi connections and basic database operations
 */

class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'borrowing_system';
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->connection = mysqli_connect(
            $this->host,
            $this->username,
            $this->password,
            $this->database
        );

        if (!$this->connection) {
            die("Database connection failed: " . mysqli_connect_error());
        }

        mysqli_set_charset($this->connection, 'utf8');
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        $result = mysqli_query($this->connection, $sql);

        if (!$result) {
            die("Query failed: " . mysqli_error($this->connection));
        }

        return $result;
    }

    public function escape($string) {
        return mysqli_real_escape_string($this->connection, $string);
    }

    public function insert_id() {
        return mysqli_insert_id($this->connection);
    }

    public function affected_rows() {
        return mysqli_affected_rows($this->connection);
    }

    public function close() {
        if ($this->connection) {
            mysqli_close($this->connection);
        }
    }

    public function __destruct() {
        $this->close();
    }
}
?>