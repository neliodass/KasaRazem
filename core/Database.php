<?php
require_once "Env.php";
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private $username;
    private $password;
    private $host;
    private $port;
    private $database;

    public function __construct()
    {
        Env::load('.env');

        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->host = $_ENV['DB_HOST'];
        $this->port = $_ENV['DB_PORT'];
        $this->database = $_ENV['DB_NAME'];
    }
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = $this->connect();
        }
        return $this->connection;
    }
    public function resetDatabase() {
        $db = $this->getConnection();
        $db->exec("DROP SCHEMA public CASCADE; CREATE SCHEMA public;");
        $initPath = __DIR__ . '/../docker/db/01-init.sql';
        $demoPath = __DIR__ . '/../docker/db/02-demo_data.sql';

        if (file_exists($initPath) && file_exists($demoPath)) {
            $db->exec(file_get_contents($initPath));
            $db->exec(file_get_contents($demoPath));
        } else {
            throw new Exception("SQL files for reset not found.");
        }
    }
    public function connect():PDO
    {
        try {
            $conn = new PDO(
                "pgsql:host=$this->host;port=$this->port;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode"  => "prefer"]
            );

            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function disconnect($conn)
    {
       $conn = null;
    }
}