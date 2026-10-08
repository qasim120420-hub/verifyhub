<?php
require_once '../config/session.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); exit; }
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT o.*, n.number FROM orders o JOIN numbers n ON o.number_id = n.id WHERE o.user_id = ? AND o.status IN ('pending','waiting_otp','otp_received') ORDER BY o.created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

echo json_encode(['success' => true, 'orders' => $orders]);
?>