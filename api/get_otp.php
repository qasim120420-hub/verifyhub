<?php
require_once '../config/session.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); exit; }

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT om.*, o.id as order_id, n.number FROM otp_messages om 
                      JOIN orders o ON om.order_id = o.id 
                      JOIN numbers n ON om.number_id = n.id 
                      WHERE o.user_id = ? 
                      ORDER BY om.received_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$otps = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $otps]);
?>