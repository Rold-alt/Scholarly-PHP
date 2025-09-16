<?php
session_start();
header('Content-Type: application/javascript');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (!isset($_SESSION['user_id'])) {
  echo "// not logged in";
  exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  echo "// db error";
  exit();
}

$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT username, email, contact FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc() ?: ['username' => '', 'email' => '', 'contact' => ''];
$stmt->close();
$conn->close();

$u = json_encode($user['username'] ?? '');
$e = json_encode($user['email'] ?? '');
$c = json_encode($user['contact'] ?? '');

echo "(function(){\n";
echo "var u = $u, e = $e, c = $c;\n";
echo "var elUser = document.getElementById('setUsername');\n";
echo "var elEmail = document.getElementById('setEmail');\n";
echo "var elContact = document.getElementById('setContact');\n";
echo "var elSidebar = document.getElementById('sidebarName');\n";
echo "var elFullName = document.getElementById('fullName');\n";
echo "var elEmailEdit = document.getElementById('email');\n";
echo "var elContactEdit = document.getElementById('contact');\n";
echo "if (elUser && u) elUser.value = u;\n";
echo "if (elEmail && e) elEmail.value = e;\n";
echo "if (elContact) elContact.value = c || '';\n";
echo "if (elSidebar && u) elSidebar.textContent = u;\n";
echo "if (elFullName && u) elFullName.value = u;\n";
echo "if (elEmailEdit && e) elEmailEdit.value = e;\n";
echo "if (elContactEdit) elContactEdit.value = c || '';\n";
echo "})();";
?>


