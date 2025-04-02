<?php
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $db;
    private $secret_key = "sikibidi"; // Thay bằng khóa bí mật thực tế
    var $currenUser = "null";

    public function __construct($db) {
        $this->db = $db;
    }

    public function register() {
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
    
        // Lấy dữ liệu từ request body
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
    
        if (!isset($data['username'], $data['email'], $data['password'])) {
            return json_encode(["message" => "missing something"], JSON_UNESCAPED_UNICODE);
        }
    
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = $data['password'];
    
        if ($user->register()) {
            return json_encode(["message" => "success"], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(["message" => "fail"], JSON_UNESCAPED_UNICODE);
        }
    }        

    public function login() {
        session_start(); 
    
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
    
        if (!isset($data['email'], $data['password'])) {
            echo json_encode(["error" => "missing something"], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        $user = new User($this->db);
        $user->email = $data["email"];
        $user->password = $data["password"];
    
        $user_data = $user->login();
        if ($user_data) {
            $_SESSION['user_id'] = $user_data["id"];
            $_SESSION['email'] = $user_data["email"];
    
            $token = JWT::encode(["id" => $user_data["id"], "email" => $user_data["email"]], $this->secret_key, 'HS256');
    
            // 🚀 Kiểm tra JSON trước khi gửi
            $response = [
                "message" => "Login successful",
                "token" => $token,
                "session" => $_SESSION
            ];
            
            header('Content-Type: application/json'); // ✅ Đảm bảo đúng header JSON
            echo json_encode($response, JSON_UNESCAPED_UNICODE);

            exit;
        }
    
        echo json_encode(["error" => "wrong email or pass"], JSON_UNESCAPED_UNICODE);
        exit;
    }
     
    public function getUsers() {
        $user = new User($this->db);
    
        if (isset($_GET["username"])) {
            $result = $user->getUserByUsername($_GET["username"]);
            return json_encode($result ? $result : ["message" => "User not found"], JSON_UNESCAPED_UNICODE);
        } elseif (isset($_GET["current"])) {
            // Lấy user hiện tại từ $GLOBALS
            if (!isset($GLOBALS['currentUser'])) {
                return json_encode(["error" => "User not authenticated"], JSON_UNESCAPED_UNICODE);
            }
            return json_encode($user->getUserById($GLOBALS['currentUser']->id), JSON_UNESCAPED_UNICODE);
        }
        else {
            return json_encode($user->getAllUsers(), JSON_UNESCAPED_UNICODE);
        }
    }
    
}
?>
