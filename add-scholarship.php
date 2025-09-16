<?php
session_start();

// Require admin session
if (empty($_SESSION['is_admin'])) {
    header('Location: login.html');
    exit();
}

require_once 'config.php';

// Connect to MySQL and ensure database/table
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
$conn->select_db(DB_NAME);

// Create scholarships table if not exists
$createTableSql = "CREATE TABLE IF NOT EXISTS scholarships (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  sponsor VARCHAR(255) NOT NULL,
  category ENUM('Company','School','Organization') NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(100) NULL,
  image_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createTableSql);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-scholarship.html');
    exit();
}

// Collect inputs
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$sponsor = trim($_POST['sponsor'] ?? '');
$category = $_POST['category'] ?? '';
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

$errors = [];
if ($title === '') { $errors[] = 'Scholarship title is required.'; }
if ($description === '') { $errors[] = 'Description is required.'; }
if ($sponsor === '') { $errors[] = 'Sponsor is required.'; }
if (!in_array($category, ['Company','School','Organization'], true)) { $errors[] = 'Category is required.'; }
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Invalid email format.'; }
if ($start_date && $end_date && strtotime($end_date) < strtotime($start_date)) { $errors[] = 'End date cannot be before start date.'; }

// Upload handling
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $mime = mime_content_type($_FILES['image']['tmp_name']);
    if (!isset($allowed[$mime])) {
        $errors[] = 'Only JPG and PNG images are allowed.';
    } else {
        $ext = $allowed[$mime];
        $uploadsDir = __DIR__ . '/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        $safeName = preg_replace('/[^A-Za-z0-9-_]/', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
        $fileName = $safeName . '_' . time() . '.' . $ext;
        $dest = $uploadsDir . '/' . $fileName;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $errors[] = 'Failed to upload image.';
        } else {
            $imagePath = 'uploads/' . $fileName;
        }
    }
}

if (!empty($errors)) {
    echo "<script>alert('" . implode("\\n", array_map('addslashes', $errors)) . "'); window.history.back();</script>";
    exit();
}

$sql = "INSERT INTO scholarships (title, description, sponsor, category, start_date, end_date, phone, email, image_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'sssssssss',
    $title,
    $description,
    $sponsor,
    $category,
    $start_date,
    $end_date,
    $phone,
    $email,
    $imagePath
);

if ($stmt->execute()) {
    echo "<script>alert('Scholarship added successfully.'); window.location.href='admin-dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to add scholarship: " . addslashes($stmt->error) . "'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>


