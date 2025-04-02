<?php

require_once __DIR__ . '/migrations.php';
// Chạy migration (nếu cần)
$migration = new Migration();
$migration->run();
require __DIR__ . "/routes/api.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

?>

