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

        $this->username = $_ENV['USERNAME'];
        $this->password = $_ENV['PASSWORD'];
        $this->host = $_ENV['HOST'];
        $this->port = $_ENV['PORT'];
        $this->database = $_ENV['DATABASE'];
    }
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
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