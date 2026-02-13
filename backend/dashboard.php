<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../ui/login.html");
    exit();
}

// Search handling
$search = $_GET['search'] ?? "";

// If searching
if ($search != "") {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_name LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $courses = $stmt->get_result();
} 
// Default: show all courses
else {
    $courses = $conn->query("SELECT * FROM courses");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Online Course Registration System</title>
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
  }

  /* Grid */
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
    transition: transform 0.2s;
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

  .course-card button {
    background-color: #004d99;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 10px;
  }

  .course-card button:hover {
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

  #course-details {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    display: none;
  }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>OCRS</h2>
  <a href="#" class="active">📚 Courses</a>
  <a href="enrolled.php">🧾 Enrolled Courses</a>
  <a href="pending_courses.php">⏳ Pending Courses</a>
  <a href="profile.php">👤 Profile</a>
  <a href="logout.php">🚪 Logout</a>
</div>

<!-- Main -->
<div class="main-content">
  <div class="navbar">
    <div class="logo">
      <img src="../images/login.jpg" alt="Logo"> Student Dashboard
    </div>

    <!-- Search + Username -->
    <div class="search-bar" style="display:flex; align-items:center; gap:15px;">
        <form method="GET" action="dashboard.php" style="display:flex; gap:10px;">
          <input type="text" name="search" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
          <button type="submit">Search</button>
        </form>

        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</span>
    </div>
  </div>

  <!-- Courses -->
  <div class="container">

    <h2>📚 Available Courses</h2>

    <div class="course-grid" id="course-list">
      <?php while ($row = $courses->fetch_assoc()) { ?>
        <div class="course-card">
          <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="">
          <h3><?php echo htmlspecialchars($row['course_name']); ?></h3>
          <p><?php echo htmlspecialchars(substr($row['description'], 0, 80)); ?>...</p>
          <p><strong>₹<?php echo $row['fee']; ?></strong> | <?php echo $row['duration']; ?></p>
          <button onclick="loadCourseDetails(<?php echo $row['id']; ?>)">View Details</button>
        </div>
      <?php } ?>
    </div>

    <div id="course-details"></div>
  </div>

  <footer>
    &copy; 2025 Online Course Registration System. All Rights Reserved.
  </footer>
</div>

<script>
function loadCourseDetails(courseId) {
  fetch('course-details.php?id=' + courseId)
    .then(res => res.text())
    .then(data => {
      document.getElementById('course-list').style.display = 'none';
      const detailsDiv = document.getElementById('course-details');
      detailsDiv.innerHTML = data;
      detailsDiv.style.display = 'block';
    })
    .catch(err => alert('Error loading details!'));
}

function goBack() {
  document.getElementById('course-details').style.display = 'none';
  document.getElementById('course-list').style.display = 'grid';
}
</script>

</body>
</html>
