<?php

require_once "Repository.php";
require_once "src/entities/Category.php";

class CategoryRepository extends Repository
{
    private static $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        parent::__construct();
    }

    public function getCategoryById(int $id): ?Category
    {
        $query = $this->conn->prepare(
            'SELECT * FROM categories WHERE id = :id'
        );
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $category = new Category();
        $category->id = (int)$data['id'];
        $category->name = $data['name'];

        return $category;
    }

    public function getAll(): array
    {
        $query = $this->conn->prepare('SELECT * FROM categories');
        $query->execute();

        $categories = [];
        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
            $category = new Category();
            $category->id = (int)$data['id'];
            $category->name = $data['name'];
            $categories[] = $category;
        }

        return $categories;
    }
}

