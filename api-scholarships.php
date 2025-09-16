<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && empty($_SESSION['is_admin'])) {
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

$q = trim($_GET['q'] ?? '');
$sql = "SELECT id, title, sponsor, image_path FROM scholarships";
if ($q !== '') {
  $sql .= " WHERE title LIKE ? OR sponsor LIKE ?";
  $stmt = $conn->prepare($sql);
  $like = "%$q%";
  $stmt->bind_param('ss', $like, $like);
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  $res = $conn->query($sql);
}

$items = [];
if ($res) {
  while ($row = $res->fetch_assoc()) { $items[] = $row; }
}
if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
if (isset($res) && $res instanceof mysqli_result) { $res->free(); }
$conn->close();

echo json_encode($items);
?>


