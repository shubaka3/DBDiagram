<?php
require_once __DIR__ . '/../config/database.php';
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $detail; // Thêm thuộc tính này

    // public $price;
    public $category_id;
    
    public $created_by; // Thêm thuộc tính này
    public $updated_at;


    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, detail, category_id, created_by, updated_at) VALUES (:name, :detail, :category_id, :created_by,NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":detail", $this->detail);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":created_by", $this->created_by);

        return $stmt->execute();
    }
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET name = :name, detail = :detail, category_id = :category_id, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $data["name"]);
        $stmt->bindParam(":detail", $data["detail"]);
        $stmt->bindParam(":category_id", $data["category_id"]);
        return $stmt->execute();
    }
    public function getAllProducts() {
        $query = "SELECT id, name, category_id, created_by,  created_at, 
                  IFNULL(updated_at, created_at) as last_updated FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProductByName($name) {
        $query = "SELECT id, name, detail, category_id, created_by, created_at, updated_at FROM " . $this->table_name . " WHERE name = :name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getProductById($id) {
        $query = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
?>
