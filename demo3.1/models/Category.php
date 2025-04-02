<?php
require_once __DIR__ . '/../config/database.php';

class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $created_by; // Thêm thuộc tính này

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, created_by) VALUES (:name, :created_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":created_by", $this->created_by);
        return $stmt->execute();
    }    
    public function getAllCategories() {
        $query = "SELECT id, name FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategoryByName($name) {
        $query = "SELECT id, name FROM " . $this->table_name . " WHERE name = :name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
?>
