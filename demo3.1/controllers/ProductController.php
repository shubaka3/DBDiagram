<?php
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    public function create($data) {
        $product = new Product($this->db);
        $product->name = $data["name"];
        $product->detail = $data["detail"];
        $product->category_id = $data["category_id"];
        $product->created_by = $data["created_by"];

        return $product->create() ? ["message" => "Product created successfully"] : ["error" => "Product creation failed"];
    }
    public function update($id, $data) {
        $query = "UPDATE products 
                  SET name = :name, detail = :detail, category_id = :category_id, updated_at = NOW()
                  WHERE id = :id AND created_by = :created_by";
    
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $data["name"]);
        $stmt->bindParam(":detail", $data["detail"]);
        $stmt->bindParam(":category_id", $data["category_id"]);
        $stmt->bindParam(":created_by", $data["created_by"]);
    
        if ($stmt->execute()) {
            return ["message" => "Product updated successfully"];
        } else {
            return ["error" => "Failed to update product"];
        }
    }
    
    public function getProducts() {
        $product = new Product($this->db);
    
        if (isset($_GET["name"])) {
            $result = $product->getProductByName($_GET["name"]);
            return json_encode($result ? $result : ["message" => "Product not found"], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($product->getAllProducts(), JSON_UNESCAPED_UNICODE);
        }
    }

    public function getProductById($id) {
        $product = new Product($this->db);
        $result = $product->getProductById($id);
        return $result ? $result : ["error" => "Product not found"];
    }
    
    
}
?>
