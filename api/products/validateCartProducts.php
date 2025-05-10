<?php
require_once __DIR__ . '/../../includes/db.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$productIds = $data['product_ids'] ?? [];
if (!$productIds) { echo json_encode([]); exit; }
$pdo = DB::getConnection();
$in  = str_repeat('?,', count($productIds) - 1) . '?';
$stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($in) AND is_deleted = 0");
$stmt->execute($productIds);
echo json_encode($stmt->fetchAll()); 