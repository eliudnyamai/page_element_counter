<?php
/*Here we define configuration settings and a database connection class.
this is  what we will use to make secure database connection throughout the application
i Assume I do not have to store the database connection details securely in environment variables
just for this test
*/
class Config {
    const DB_HOST = 'db.fr-pari1.bengt.wasmernet.com';
    const DB_NAME = 'page_element_counter';
    const DB_USER = '42daaa2f7db180005e7fd85f4b39';
    const DB_PORT='10272';
    const DB_PASS = '068e42da-aa2f-7f3d-8000-f96b72b661fe';
    const CACHE_DURATION = 5 * 60; // Cache duration in secons (5 minutes)
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