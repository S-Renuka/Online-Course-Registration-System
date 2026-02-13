<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../ui/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

/* FETCH ONLY APPROVED COURSES */
$stmt = $conn->prepare("
    SELECT ce.*, c.course_name, c.image_url, c.description, c.duration, c.fee
    FROM course_enrollment ce
    JOIN courses c ON ce.course_id = c.id
    WHERE ce.user_id = ? AND ce.status = 'Approved'
    ORDER BY ce.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Enrolled Courses</title>

<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    background-color: #f3f8fc;
    display: flex;
  }

  /* Sidebar */
  .sidebar {
    width: 230px;
    background-color: #004d99;
    height: 100vh;
    color: white;
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
  }

  .sidebar h2 {
    text-align: center;
    background-color: #003366;
    margin: 0;
    padding: 20px 0;
    font-size: 22px;
  }

  .sidebar a {
    display: block;
    padding: 14px 20px;
    color: white;
    text-decoration: none;
    font-size: 16px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    transition: 0.3s;
  }

  .sidebar a:hover, .sidebar a.active {
    background-color: #ffcc00;
    color: #000;
    font-weight: bold;
  }

  /* Main content */
  .main-content {
    margin-left: 230px;
    width: calc(100% - 230px);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  /* Navbar */
  .navbar {
    background-color: #004d99;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 40px;
    color: white;
  }

  .navbar img {
    height: 40px;
    margin-right: 10px;
    border-radius: 5px;
  }

  /* Container */
  .container {
    padding: 40px;
    flex-grow: 1;
  }

  .container h2 {
    font-size: 30px;
    text-align: center;
    margin-bottom: 20px;
    color: #003366;
  }

  /* Course Grid */
  .course-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
    margin-top: 25px;
  }

  .course-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: 0.2s;
  }

  .course-card:hover {
    transform: scale(1.03);
  }

  .course-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 10px;
  }

  .course-card h3 {
    color: #004d99;
    margin: 10px 0;
  }

  .course-card a {
    display: inline-block;
    background-color: #004d99;
    color: white;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    transition: 0.3s;
    margin-top: 10px;
  }

  .course-card a:hover {
    background-color: #003366;
  }

  footer {
    background-color: #003366;
    color: white;
    text-align: center;
    padding: 15px;
    font-size: 14px;
    margin-top: auto;
  }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>OCRS</h2>
  <a href="dashboard.php">📚 Courses</a>
  <a href="pending_courses.php">⏳ Pending Courses</a>
  <a href="enrolled.php" class="active">🧾 Enrolled Courses</a>
  <a href="profile.php">👤 Profile</a>
  <a href="logout.php">🚪 Logout</a>
</div>

<!-- Main Area -->
<div class="main-content">

  <!-- Navbar -->
  <div class="navbar">
    <div class="logo">
      <img src="../images/login.jpg" alt="Logo"> My Enrolled Courses
    </div>
    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</span>
  </div>

  <!-- Courses List -->
  <div class="container">

    <h2>🧾 Courses You Have Enrolled</h2>

    <?php if ($result->num_rows > 0): ?>

      <div class="course-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="course-card">
            <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Course Image">

            <h3><?php echo htmlspecialchars($row['course_name']); ?></h3>
            <p><strong>Mode:</strong> <?php echo htmlspecialchars($row['mode']); ?></p>
            <p><strong>Amount Paid:</strong> ₹<?php echo htmlspecialchars($row['amount']); ?></p>
            <p><strong>Approved On:</strong>
              <?php echo date("d-m-Y h:i A", strtotime($row['created_at'])); ?>
            </p>

            <a href="view-enrolled.php?id=<?php echo $row['course_id']; ?>">▶ Course</a>
          </div>
        <?php endwhile; ?>
      </div>

    <?php else: ?>

      <p style="font-size:18px; margin-top:20px; text-align:center; color:#444;">
        No approved courses yet. Please wait for admin approval.
      </p>

    <?php endif; ?>

  </div>

  <footer>
    © 2025 Online Course Registration System. All Rights Reserved.
  </footer>

</div>

</body>
</html>
