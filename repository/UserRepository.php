<?php
require_once "Repository.php";
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
    public function getUsers(): ?array
    {

        $query = $this->conn->prepare(
            'SELECT * FROM users'
        );

        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function getUserByEmail(string $email): ?array
    {

        $query = $this->conn->prepare(
            'SELECT * FROM users WHERE email = :email'
        );
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user;
    }
    public function getUserById(string $id): ?array
    {

        $query = $this->conn->prepare(
            'SELECT * FROM users WHERE id = :id'
        );
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
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
                    VALUES (?,?,?,?,?);
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