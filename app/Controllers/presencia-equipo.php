<?php
require_once __DIR__ . '/../Core/app.php';
header('Cache-Control: no-store, private');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}
$usuario = current_user();
http_response_code(204);

