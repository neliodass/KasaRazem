<?php
require_once "Repository.php";
require_once "src/entities/User.php";
class UserRepository extends Repository
{
    private static UserRepository $repository;
    public static function getInstance(): UserRepository
    {
        if (!isset(self::$repository)) {
            self::$repository = new self();
        }
        return self::$repository;
    }
    private function __construct()
    {
        parent::__construct();
    }


    public function getUserByEmail(string $email): ?User
    {

        $query = $this->conn->prepare(
            'SELECT * FROM users WHERE email = :email'
        );
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, User::class);
        $user = $query->fetch();
        if(!$user)
        {
            return null;
        }
        return $user;
    }
    public function getUserById(string $id): ?User
    {

        $query = $this->conn->prepare(
            'SELECT * FROM users WHERE id = :id'
        );
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, User::class);
        $user = $query->fetch();
        if(!$user)
        {
            return null;
        }
        return $user;
    }

    public function createUser(
        string $email,
        string $hashedPassword,
        string $firstName,
        string $lastName,
        string $bio = ''
    )
    {

        $query = $this->conn->prepare(
            "
                    INSERT INTO users (firstname, lastname, email, password, bio, enabled)
                    VALUES (?,?,?,?,?,?);
                  "
        );
        $query->execute(
            [$firstName,
                $lastName,
                $email,
                $hashedPassword,
                $bio,
                1]
        );
        return $this->conn->lastInsertId();
    }


}