<?php
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../middleware/AuthMiddleware.php";
require __DIR__ . "/../controllers/AuthController.php";
require __DIR__ . "/../controllers/ProductController.php";
require __DIR__ . "/../controllers/CategoryController.php";


$db = (new Database())->getConnection();
$authMiddleware = new AuthMiddleware();
$authController = new AuthController($db);
$productController = new ProductController($db);
$categoryController = new CategoryController($db);



$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

$json = file_get_contents("php://input");
$data = json_decode($json, true) ?? [];

// Thiết lập CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Xử lý đăng ký và đăng nhập (không cần token)
if ($request_uri === "/register" && $method === "POST") {
    echo json_encode($authController->register($data));
    exit();
} elseif ($request_uri === "/login" && $method === "POST") {
    echo json_encode($authController->login($data));
    exit();
}

// Lấy token từ header
$headers = getallheaders();
// error_log("🔍 Headers nhận được: " . json_encode($headers));

$authHeader = $headers["Authorization"] ?? $headers["authorization"] ?? null;
if (!$authHeader || !str_starts_with($authHeader, "Bearer ")) {
    error_log("❌ Token không tồn tại hoặc sai định dạng!");
    http_response_code(401);
    echo json_encode(["error" => "Token is missing"]);
    exit();
}

// Lấy token
$token = str_replace("Bearer ", "", $authHeader);
error_log("📌 Token nhận được: " . $token);

// Kiểm tra token
$user = $authMiddleware->validateToken($token);
error_log("🛠 Token giải mã: " . json_encode($user));
error_log("❌ Token không tồn tại hoặc sai định dạng! $user->id");

if (!$user || !isset($user->id)) {
    error_log("❌ Token không hợp lệ!");
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

// Gán user_id vào $data
$data["created_by"] = $user->id;
$GLOBALS['currentUser'] = $user;



// Các API yêu cầu đăng nhập
switch ($request_uri) {
    case "/product":
        if ($method === "POST") {
            echo json_encode($productController->create($data));
        }
        break;
    case "/category":
        if ($method === "POST") {
            echo json_encode($categoryController->create($data));
        } elseif ($method === "GET") {
            echo json_encode($categoryController->getCategories());
        }
        break;
    case "/users":
        if ($method === "GET") {
            echo json_encode($authController->getUsers());
        }
        break;
    case "/products":
        if ($method === "GET") {
            echo json_encode($productController->getProducts());
        }
        break;
    case preg_match("#^/product/(\d+)$#", $request_uri, $matches) ? true : false:
        $product_id = $matches[1];
        if ($method === "GET") {
            echo json_encode($productController->getProductById($product_id));
        } elseif ($method === "PUT") {
            echo json_encode($productController->update($product_id, $data));
        }
        break; 
    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
}
