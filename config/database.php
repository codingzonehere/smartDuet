<?php
/**
 * Smart DUET Admission Management System
 *
 * File: config/database.php
 *
 * Purpose:
 * MySQL database connection using OOP MySQLi.
 */

class Database
{
    private string $host = 'localhost';
    private string $dbName = 'smart_duet';
    private string $username = 'root';
    private string $password = '';

    private ?mysqli $connection = null;

    /**
     * Create and return database connection
     */
    public function getConnection(): mysqli
    {
        // If connection already exists, return it
        if ($this->connection !== null) {
            return $this->connection;
        }

        // Create MySQLi connection
        $this->connection = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->dbName
        );

        // Check connection error
        if ($this->connection->connect_error) {
            die('Database connection failed.');
        }

        // Set character encoding
        $this->connection->set_charset('utf8mb4');

        return $this->connection;
    }
}