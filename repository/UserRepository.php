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
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if(!$data)
        {
            return null;
        }
        return User::fromArray($data);
    }
    public function getUserById(string $id): ?User
    {
        $query = $this->conn->prepare(
            'SELECT * FROM users WHERE id = :id'
        );
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if(!$data)
        {
            return null;
        }
        return User::fromArray($data);
    }
    public function save(User $user): User
    {
        if ($user->id !== null) {
            $query = $this->conn->prepare(
                'UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email, password = :password, profile_picture = :profile_picture, enabled = :enabled WHERE id = :id'
            );

            $query->bindValue(':firstname', $user->firstname, PDO::PARAM_STR);
            $query->bindValue(':lastname', $user->lastname, PDO::PARAM_STR);
            $query->bindValue(':email', $user->email, PDO::PARAM_STR);
            $query->bindValue(':password', $user->password, PDO::PARAM_STR);
            $query->bindValue(':profile_picture', $user->profile_picture, PDO::PARAM_STR);
            $query->bindValue(':enabled', (int)$user->enabled, PDO::PARAM_INT);
            $query->bindValue(':id', $user->id, PDO::PARAM_INT);

            $query->execute();
            return $user;
        }

        $supportsReturning = true;

        if ($supportsReturning) {
            $query = $this->conn->prepare(
                'INSERT INTO users (firstname, lastname, email, password, profile_picture, enabled) VALUES (:firstname, :lastname, :email, :password, :profile_picture, :enabled) RETURNING id'
            );
            $query->bindValue(':firstname', $user->firstname, PDO::PARAM_STR);
            $query->bindValue(':lastname', $user->lastname, PDO::PARAM_STR);
            $query->bindValue(':email', $user->email, PDO::PARAM_STR);
            $query->bindValue(':password', $user->password, PDO::PARAM_STR);
            $query->bindValue(':profile_picture', $user->profile_picture, PDO::PARAM_STR);
            $query->bindValue(':enabled', (int)$user->enabled, PDO::PARAM_INT);

            $query->execute();

            $newId = $query->fetchColumn();
            if ($newId === false) {
                $newId = (int)$this->conn->lastInsertId();
            }
        } else {
            $query = $this->conn->prepare(
                'INSERT INTO users (firstname, lastname, email, password, profile_picture, enabled) VALUES (:firstname, :lastname, :email, :password, :profile_picture, :enabled)'
            );
            $query->bindValue(':firstname', $user->firstname, PDO::PARAM_STR);
            $query->bindValue(':lastname', $user->lastname, PDO::PARAM_STR);
            $query->bindValue(':email', $user->email, PDO::PARAM_STR);
            $query->bindValue(':password', $user->password, PDO::PARAM_STR);
            $query->bindValue(':profile_picture', $user->profile_picture, PDO::PARAM_STR);
            $query->bindValue(':enabled', (int)$user->enabled, PDO::PARAM_INT);

            $query->execute();
            $newId = (int)$this->conn->lastInsertId('users_id_seq');
        }

        $user->id = (int)$newId;

        return $user;
    }

}