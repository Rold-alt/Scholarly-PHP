<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  die('Database connection failed: ' . $conn->connect_error);
}

$userId = (int) $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $contact = trim($_POST['contact'] ?? '');

  $errors = [];
  if ($username === '') { $errors[] = 'Username is required.'; }
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
  if ($contact === '') { $errors[] = 'Contact is required.'; }

  if (empty($errors)) {
    $sql = "UPDATE users SET username = ?, email = ?, contact = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $username, $email, $contact, $userId);
    if ($stmt->execute()) {
      $_SESSION['username'] = $username;
      $_SESSION['email'] = $email;
      $message = 'Profile updated successfully';
    } else {
      $message = 'Failed to update profile: ' . $stmt->error;
    }
    $stmt->close();
  } else {
    $message = implode("\n", $errors);
  }
}

// Load current user
$stmt = $conn->prepare("SELECT username, email, contact FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
  <div class="container py-5" style="max-width: 640px;">
    <h3 class="mb-4">Edit Profile</h3>
    <?php if ($message): ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Contact</label>
        <input type="text" class="form-control" name="contact" value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>" required>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="dashboard.php" class="btn btn-secondary">Back</a>
      </div>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>


