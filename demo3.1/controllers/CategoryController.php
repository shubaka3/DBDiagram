<?php
require_once __DIR__ . '/../models/Category.php';

class CategoryController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        if (!isset($data["created_by"])) {
            return json_encode(["error" => "Unauthorized"], JSON_UNESCAPED_UNICODE);
        }
    
        $category = new Category($this->db);
        $category->name = $data["name"];
        $category->created_by = $data["created_by"];
    
        return json_encode(
            $category->create() ? ["message" => "Category created successfully"] : ["error" => "Category creation failed"],
            JSON_UNESCAPED_UNICODE
        );
    }

    public function getCategories() {
        $category = new Category($this->db);
    
        if (isset($_GET["name"])) {
            $result = $category->getCategoryByName($_GET["name"]);
            return json_encode($result ? $result : ["message" => "Category not found"], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($category->getAllCategories(), JSON_UNESCAPED_UNICODE);
        }
    }
}
?>
