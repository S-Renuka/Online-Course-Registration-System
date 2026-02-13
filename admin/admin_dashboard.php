<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch counts for dashboard cards
$pending = $conn->query("SELECT COUNT(*) AS total FROM course_enrollment WHERE status='Pending'")->fetch_assoc();
$approved = $conn->query("SELECT COUNT(*) AS total FROM course_enrollment WHERE status='Approved'")->fetch_assoc();
$rejected = $conn->query("SELECT COUNT(*) AS total FROM course_enrollment WHERE status='Rejected'")->fetch_assoc();

// Fetch all enrollments for Enrollment Details tab
$enrollments = $conn->query("SELECT * FROM course_enrollment ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
  body { font-family: Arial, sans-serif; margin: 0; background-color: #f3f8fc; display: flex; }

  /* Sidebar */
  .sidebar { width: 230px; background-color: #004d99; height: 100vh; color: white; display: flex; flex-direction: column; position: fixed; left: 0; top: 0; }
  .sidebar h2 { text-align: center; background-color: #003366; margin: 0; padding: 20px 0; font-size: 22px; }
  .sidebar a { display: block; padding: 14px 20px; color: white; text-decoration: none; font-size: 16px; border-bottom: 1px solid rgba(255,255,255,0.1); transition: 0.3s; }
  .sidebar a:hover, .sidebar a.active { background-color: #ffcc00; color: #000; font-weight: bold; }

  /* Main content */
  .main-content { margin-left: 230px; width: calc(100% - 230px); display: flex; flex-direction: column; min-height: 100vh; }

  /* Navbar */
  .navbar { background-color: #004d99; display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; color: white; }
  .navbar img { height: 40px; margin-right: 10px; border-radius: 5px; }

  /* Container */
  .container { padding: 40px; flex-grow: 1; }
  .container h2 { font-size: 30px; text-align: center; margin-bottom: 30px; }

  /* Dashboard Cards */
  .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-top: 20px; }
  .dashboard-card { background: white; border-radius: 10px; padding: 30px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s; }
  .dashboard-card:hover { transform: scale(1.05); }
  .dashboard-card h3 { color: #004d99; margin: 10px 0; font-size: 28px; }
  .dashboard-card p { font-size: 16px; color: #555; margin: 5px 0 15px; }
  .dashboard-card a { display: inline-block; padding: 10px 20px; background-color: #004d99; color: white; text-decoration: none; border-radius: 6px; transition: 0.3s; }
  .dashboard-card a:hover { background-color: #003366; }

  /* Enrollment table */
  table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
  table th, table td { padding: 12px 15px; border: 1px solid #ddd; text-align: center; font-size: 14px; }
  table th { background-color: #004d99; color: white; }
  table tr:nth-child(even) { background-color: #f9f9f9; }
  .view-btn { padding: 6px 12px; background-color: #004d99; color: white; text-decoration: none; border-radius: 5px; }
  .view-btn:hover { background-color: #003366; }

  footer { background-color: #003366; color: white; text-align: center; padding: 15px; font-size: 14px; margin-top: auto; }

</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>OCRS Admin</h2>
  <a href="#" class="active">📊 Dashboard</a>
  <a href="pending_enrollments.php">⏳ Pending Enrollments</a>
  <a href="approved_enrollments.php">✅ Approved Enrollments</a>
  <a href="rejected_enrollments.php">❌ Rejected Enrollments</a>
  <a href="admin_logout.php">🚪 Logout</a>
</div>

<!-- Main -->
<div class="main-content">
  <div class="navbar">
    <div class="logo">
      <img src="../images/login.jpg" alt="Logo"> Admin Dashboard
    </div>
    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> 👋</span>
  </div>

  <div class="container">
    <h2>📊 Enrollment Summary</h2>

    <div class="dashboard-grid">
      <div class="dashboard-card">
        <h3><?= $pending['total'] ?></h3>
        <p>Pending Enrollments</p>
        <a href="pending_enrollments.php">View</a>
      </div>

      <div class="dashboard-card">
        <h3><?= $approved['total'] ?></h3>
        <p>Approved Enrollments</p>
        <a href="approved_enrollments.php">View</a>
      </div>

      <div class="dashboard-card">
        <h3><?= $rejected['total'] ?></h3>
        <p>Rejected Enrollments</p>
        <a href="rejected_enrollments.php">View</a>
      </div>
    </div>

    <!-- Enrollment Details -->
    <h2 id="enrollment-details">📋 Enrollment Details</h2>
    <table>
      <thead>
        <tr>
          <th>User ID</th>
          <th>Course ID</th>
          <th>Student Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Mode</th>
          <th>Amount</th>
          <th>Transaction ID</th>
          <th>Payment Screenshot</th>
          <th>Status</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($enrollments->num_rows > 0): ?>
          <?php while($row = $enrollments->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['user_id']) ?></td>
              <td><?= htmlspecialchars($row['course_id']) ?></td>
              <td><?= htmlspecialchars($row['student_name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= htmlspecialchars($row['mode']) ?></td>
              <td><?= htmlspecialchars($row['amount']) ?></td>
              <td><?= htmlspecialchars($row['transaction_id']) ?></td>
              <td>
                <?php if (!empty($row['payment_screenshot'])): ?>
                  <a class="view-btn" href="../uploads/<?= $row['payment_screenshot'] ?>" target="_blank">View</a>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="11">No enrollments found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

  </div>

  <footer>
    &copy; 2025 Online Course Registration System. All Rights Reserved.
  </footer>
</div>

</body>
</html>
