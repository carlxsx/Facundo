<?php
// Database Configuration for InfinityFree
define('DB_HOST', 'sql111.byetcluster.com');
define('DB_USER', 'if0_40582828');
define('DB_PASS', '96aSE646qDTxd'); // CHANGE THIS PASSWORD FOR SECURITY!
define('DB_NAME', 'if0_40582828_websitecars');

// Create database connection
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;
    
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->dbname,
                $this->user,
                $this->pass,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}

// Create a global database instance
$database = new Database();
$db = $database->connect();
?>