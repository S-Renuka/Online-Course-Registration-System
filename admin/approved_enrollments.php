<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch approved enrollments
$stmt = $conn->prepare("
    SELECT ce.*, c.course_name, c.image_url, c.duration, c.fee
    FROM course_enrollment ce
    JOIN courses c ON ce.course_id = c.id
    WHERE ce.status = 'Approved'
    ORDER BY ce.created_at DESC
");
$stmt->execute();
$enrollments = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Approved Enrollments</title>
<style>
body { font-family: Arial; margin:0; background:#f3f8fc; display:flex; }

/* Sidebar */
.sidebar { width:230px; background:#004d99; height:100vh; color:white; display:flex; flex-direction:column; position:fixed; top:0; left:0; }
.sidebar h2 { text-align:center; background:#003366; margin:0; padding:20px 0; font-size:22px; }
.sidebar a { display:block; padding:14px 20px; color:white; text-decoration:none; font-size:16px; border-bottom:1px solid rgba(255,255,255,0.1); transition:0.3s; }
.sidebar a:hover, .sidebar a.active { background:#ffcc00; color:#000; font-weight:bold; }

/* Main content */
.main-content { margin-left:230px; width:calc(100%-230px); display:flex; flex-direction:column; min-height:100vh; }

/* Navbar */
.navbar { background:#004d99; display:flex; justify-content:space-between; align-items:center; padding:15px 40px; color:white; }
.navbar img { height:40px; margin-right:1000px; border-radius:5px; }

/* Container */
.container { padding:40px; flex-grow:1; }
.container h2 { font-size:30px; text-align:center; margin-bottom:30px; }

/* Grid cards */
.course-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:25px; }
.course-card { background:white; border-radius:10px; padding:15px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.1); transition:transform 0.2s; }
.course-card:hover { transform:scale(1.03); }
.course-card img { width:100%; height:160px; object-fit:cover; border-radius:10px; }
.course-card h3 { color:#004d99; margin:10px 0; }
.course-card p { margin:5px 0; color:#555; }
.course-card a { display:inline-block; margin-top:10px; padding:10px 15px; background:#004d99; color:white; text-decoration:none; border-radius:6px; transition:0.3s; }
.course-card a:hover { background:#003366; }

footer { background:#003366; color:white; text-align:center; padding:15px; font-size:14px; margin-top:auto; }
</style>
</head>
<body>

<div class="sidebar">
  <h2>OCRS Admin</h2>
  <a href="admin_dashboard.php">📊 Dashboard</a>
  <a href="pending_enrollments.php">⏳ Pending</a>
  <a href="approved_enrollments.php" class="active">✅ Approved</a>
  <a href="rejected_enrollments.php">❌ Rejected</a>
  <a href="admin_logout.php">🚪 Logout</a>
</div>

<div class="main-content">
  <div class="navbar">
    <div class="logo"><img src="../images/login.jpg" alt="Logo"> Approved Enrollments</div>
    <span>Welcome, <?= htmlspecialchars($_SESSION['admin_username']); ?> 👋</span>
  </div>

  <div class="container">
    <h2>✅ Approved Enrollments</h2>

    <div class="course-grid">
      <?php if($enrollments->num_rows > 0): ?>
        <?php while($row = $enrollments->fetch_assoc()): ?>
          <div class="course-card">
            <img src="../<?= $row['image_url']; ?>" alt="Course Image">
            <h3><?= htmlspecialchars($row['course_name']); ?></h3>
            <p><strong>Student:</strong> <?= htmlspecialchars($row['student_name']); ?></p>
            <p><strong>Amount:</strong> ₹<?= $row['amount']; ?></p>
            <p><strong>Mode:</strong> <?= $row['mode']; ?></p>
            <p><strong>Enrolled On:</strong> <?= date("d-m-Y h:i A", strtotime($row['created_at'])); ?></p>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="text-align:center; font-size:18px; color:#444;">No approved enrollments.</p>
      <?php endif; ?>
    </div>
  </div>

  <footer>&copy; 2025 Online Course Registration System. All Rights Reserved.</footer>
</div>

</body>
</html>
