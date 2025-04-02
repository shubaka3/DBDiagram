<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    private static $secret_key = "sikibidi"; // Đổi secret key theo ý bạn

    public static function generateToken($user_id) {
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + (60 * 60), // Hết hạn sau 1 giờ
            "sub" => $user_id
        ];

        $token = JWT::encode($payload, self::$secret_key, 'HS256');
        error_log("🛠 Token được tạo: " . $token); // Log token ra để kiểm tra
        echo ini_get('error_log');
        return $token;
    }

    public static function validateToken($token) {
        try {
            error_log("🔍 Đang kiểm tra token: " . $token);
            $decoded = JWT::decode($token, new Key(self::$secret_key, 'HS256'));
            error_log("✅ Token hợp lệ: " . json_encode($decoded));
             // Kiểm tra xem `sub` có tồn tại không
            // if (!isset($decoded->sub)) {
            //     error_log("❌ Token không chứa user_id!");
            //     return null;
            // }
            return $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            error_log("❌ Token đã hết hạn! " . $e->getMessage());
            return null;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            error_log("❌ Chữ ký Token không hợp lệ! " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log("❌ Lỗi khi giải mã Token: " . $e->getMessage());
            return null;
        }
    }
    

   
}
?>
