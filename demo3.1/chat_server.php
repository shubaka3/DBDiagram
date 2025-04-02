<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/database.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    private $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Received message: $msg\n";
        $data = json_decode($msg, true);

        if (!$data || !isset($data['type'])) {
            echo "❌ Lỗi: Tin nhắn không hợp lệ!\n";
            return;
        }

        try {
            if ($data['type'] === 'load_history') {
                $this->loadChatHistory($from, $data['user1'], $data['user2']);
            } elseif ($data['type'] === 'message') {
                $this->saveAndBroadcastMessage($from, $data);
            }
        } catch (\Exception $e) {
            echo "⚠ Lỗi khi xử lý tin nhắn: " . $e->getMessage() . "\n";
        }
    }

    private function loadChatHistory(ConnectionInterface $conn, $user1, $user2) {
        $query = "SELECT sender, message, timestamp FROM messages 
                  WHERE (user1 = :user1 AND user2 = :user2) 
                     OR (user1 = :user2 AND user2 = :user1)
                  ORDER BY timestamp ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["user1" => $user1, "user2" => $user2]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conn->send(json_encode(["type" => "chat_history", "messages" => $messages]));
    }

    private function saveAndBroadcastMessage(ConnectionInterface $from, $data) {
        $query = "INSERT INTO messages (user1, user2, sender, message) 
                  VALUES (:user1, :user2, :sender, :message)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            "user1" => $data['user1'],
            "user2" => $data['user2'],
            "sender" => $data['sender'],
            "message" => $data['message']
        ]);

        $messageData = [
            "sender" => $data["sender"],
            "message" => $data["message"],
            "timestamp" => date("Y-m-d H:i:s")
        ];

        foreach ($this->clients as $client) {
            $client->send(json_encode(["type" => "new_message", "message" => $messageData]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "📌 Error occurred at line " . $e->getLine() . " in " . $e->getFile() . "\n";
    }
}

$server = IoServer::factory(
    new HttpServer(new WsServer(new ChatServer())),
    8080
);

echo "WebSocket server running on port 8080...\n";
$server->run();
