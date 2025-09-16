<?php
session_start();

// Require admin session
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
$sql = "SELECT id, title, sponsor, category, start_date, end_date, image_path FROM scholarships";
if ($q !== '') {
  $sql .= " WHERE title LIKE ? OR sponsor LIKE ? OR category LIKE ?";
  $stmt = $conn->prepare($sql);
  $like = "%$q%";
  $stmt->bind_param('sss', $like, $like, $like);
  $stmt->execute();
  $scholarships = $stmt->get_result();
} else {
  $scholarships = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
  <div class="dashboard d-flex">
    <aside class="sidebar d-flex flex-column align-items-center p-3">
      <img src="assets/images/Group 44.png" alt="Scholarly Logo" class="logo mb-4">
      <div class="profile text-center mb-4">
        <img src="assets/Images/Admin.png" 
             alt="Profile" class="profile-img mb-2">
        <h2 class="h5 fw-bold">Admin</h2>
        <p class="small mb-2">ADMIN</p>
      </div>
      <hr class="w-100">

      <nav class="nav flex-column w-100">
        <a href="admin-dashboard.php" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/material-symbols_school.png"> Scholarships
        </a>
        <a href="add-scholarship.html" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/material-add_symbols_school.png">Add Scholarships
        </a>
        <a href="studentmanagementboard.php" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/ph_student-bold.png">Student Management
        </a>
        <a href="logout.php" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/weui_lock-outlined.png"> Logout
        </a>
      </nav>
    </aside>
    <main class="main-content flex-grow-1 p-4">
      <div class="controls d-flex justify-content-between align-items-center mb-4">
        <form class="w-50" method="GET" action="admin-dashboard.php">
          <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Search available scholarships">
        </form>
        <div class="filter-sort d-flex gap-2">
          <a class="btn btn-outline-secondary" href="add-scholarship.html">Add</a>
        </div>
      </div>

      <div class="d-flex flex-column gap-3">
        <?php if ($scholarships && $scholarships->num_rows > 0): ?>
          <?php while ($s = $scholarships->fetch_assoc()): ?>
            <div class="card p-3 d-flex flex-row justify-content-between align-items-center shadow-sm">
              <div class="card-left d-flex align-items-center gap-3">
                <?php $img = (!empty($s['image_path']) ? $s['image_path'] : 'assets/Images/image.png'); ?>
                <img src="<?php echo htmlspecialchars($img); ?>" alt="logo" width="65">
                <div>
                  <h3 class="h6 fw-bold mb-1"><?php echo htmlspecialchars($s['title']); ?></h3>
                  <p class="small mb-0"><?php echo htmlspecialchars($s['sponsor']); ?> • <?php echo htmlspecialchars($s['category']); ?></p>
                  <p class="small text-muted mb-0">
                    <?php echo htmlspecialchars($s['start_date'] ?? ''); ?>
                    <?php echo $s['start_date'] ? '–' : ''; ?>
                    <?php echo htmlspecialchars($s['end_date'] ?? ''); ?>
                  </p>
                </div>
              </div>
              <div class="d-flex gap-2">
                <a class="btn btn-sm btn-outline-primary" href="#">View</a>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="alert alert-info mb-0">No scholarships found.</div>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <?php if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); } ?>
  <?php if (isset($scholarships) && $scholarships instanceof mysqli_result) { $scholarships->free(); } ?>
  <?php $conn->close(); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
