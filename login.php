<?php
// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit();
}

// Include database configuration
require_once 'config.php';

// Connect to MySQL (XAMPP)
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Get form values
$username_email = trim($_POST['username_email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
$errors = [];

if (empty($username_email)) {
    $errors[] = "Username or email is required.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
}

// If there are validation errors, show them
if (!empty($errors)) {
    echo "<script>
        alert('" . implode('\\n', $errors) . "');
        window.history.back();
    </script>";
    exit();
}

// Admin hardcoded login
if ($username_email === 'admin' && $password === '123456') {
    session_start();
    $_SESSION['is_admin'] = true;
    $_SESSION['username'] = 'admin';
    echo "<script>
        window.location.href = 'admin-dashboard.php';
    </script>";
    exit();
}

// Check if user exists (by username or email)
$sql = "SELECT id, username, email, password FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $username_email, $username_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];

    echo "<script>
        window.location.href = 'dashboard.php';
    </script>";
} else {
    echo "<script>
        alert('Invalid username/email or password. Please try again.');
        window.history.back();
    </script>";
}

$stmt->close();
$conn->close();
?>
