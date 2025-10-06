<?php
/*Here we define configuration settings and a database connection class.
this is  what we will use to make secure database connection throughout the application*/
class Config {
    const DB_HOST = 'localhost';
    const DB_NAME = 'element_counter';
    const DB_USER = 'root';
    const DB_PASS = '';
    const CACHE_DURATION = 5 * 60; // Cache duration in seconds (5 minutes)
}

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=utf8mb4",
                Config::DB_USER,
                Config::DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    //This we will use to get the instance of the database connection througout the application
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
?>