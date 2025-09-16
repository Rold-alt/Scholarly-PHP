<?php
session_start();
if (empty($_SESSION['is_admin'])) {
  header('Location: login.html');
  exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  die('Database connection failed: ' . $conn->connect_error);
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT id, username, email, contact FROM users";
if ($q !== '') {
  $sql .= " WHERE username LIKE ? OR email LIKE ? OR contact LIKE ?";
  $stmt = $conn->prepare($sql);
  $like = "%$q%";
  $stmt->bind_param('sss', $like, $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly – Student Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .search-input { max-width: 280px; }
  </style>
  </head>
<body>
  <div class="dashboard d-flex">
    <aside class="sidebar d-flex flex-column align-items-center p-3">
      <img src="assets/images/Group 44.png" alt="Scholarly Logo" class="logo mb-4">
      <div class="profile text-center mb-4">
        <img src="assets/Images/Admin.png" alt="Profile" class="profile-img mb-2">
        <h2 class="h5 fw-bold">Admin</h2>
        <p class="small mb-2">ADMIN</p>
      </div>
      <hr class="w-100">
      <nav class="nav flex-column w-100">
        <a href="admin-dashboard.php" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/material-symbols_school.png" alt=""> Scholarships
        </a>
        <a href="add-scholarship.html" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/material-add_symbols_school.png" alt=""> Add Scholarship
        </a>
        <a href="studentmanagementboard.php" class="nav-link active d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/ph_student-bold.png" alt=""> Student Management
        </a>
      </nav>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <h4 class="fw-bold mb-3">Student Management</h4>

      <form class="input-group mb-4 search-input" method="GET" action="studentmanagementboard.php">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search student">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th scope="col"></th>
              <th scope="col">STUDENT ID</th>
              <th scope="col">STUDENT NAME</th>
              <th scope="col">EMAIL</th>
              <th scope="col">CONTACT NUMBER</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><input type="checkbox"></td>
                  <td><?php echo htmlspecialchars($row['id']); ?></td>
                  <td><?php echo htmlspecialchars($row['username']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['contact']); ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No students found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
if (isset($result) && $result instanceof mysqli_result) { $result->free(); }
$conn->close();
?>


