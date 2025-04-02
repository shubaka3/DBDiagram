<?php
require_once __DIR__ . '/../config/database.php';

class Messenger {
    private $conn;
    private $table_name = "messages";

    public function __construct($db) {
        $this->conn = $db;
        $this->migrate(); // Tự động tạo bảng nếu chưa có
    }

    private function migrate() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($query);
    }

    public function saveMessage($sender_id, $receiver_id, $message) {
        $query = "INSERT INTO " . $this->table_name . " (sender_id, receiver_id, message) 
                  VALUES (:sender_id, :receiver_id, :message)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sender_id", $sender_id);
        $stmt->bindParam(":receiver_id", $receiver_id);
        $stmt->bindParam(":message", $message);
        return $stmt->execute();
    }

    public function getChatHistory($user1, $user2) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (sender_id = :user1 AND receiver_id = :user2) 
                     OR (sender_id = :user2 AND receiver_id = :user1)
                  ORDER BY timestamp ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user1", $user1);
        $stmt->bindParam(":user2", $user2);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
