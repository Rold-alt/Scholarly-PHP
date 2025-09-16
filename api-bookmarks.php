<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode([]);
  exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode([]);
  exit();
}

// Ensure bookmarks table exists
$conn->query("CREATE TABLE IF NOT EXISTS bookmarks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  scholarship_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_scholarship (user_id, scholarship_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$userId = (int) $_SESSION['user_id'];

$sql = "SELECT s.id, s.title, s.sponsor, s.image_path
        FROM bookmarks b
        JOIN scholarships s ON s.id = b.scholarship_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) { $items[] = $row; }

$stmt->close();
$conn->close();

echo json_encode($items);
?>


