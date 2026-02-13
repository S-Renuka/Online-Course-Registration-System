<?php
session_start();
include '../db/db_connect.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../ui/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate GET id
$course_id = $_GET['id'] ?? 0;
if ($course_id <= 0) {
    echo "<script>alert('Invalid course'); window.location.href='enrolled.php';</script>";
    exit();
}

// Fetch enrollment details
$stmt = $conn->prepare("
    SELECT ce.*, c.course_name, c.description, c.duration, c.fee, c.image_url
    FROM course_enrollment ce
    JOIN courses c ON ce.course_id = c.id
    WHERE ce.user_id = ? AND ce.course_id = ?
");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Course not found'); window.location.href='enrolled.php';</script>";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Course Details</title>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background: #eef3f8;
        display: flex;
    }

    /* Sidebar */
    .sidebar {
        width: 230px;
        height: 100vh;
        background-color: #004d99;
        color: #fff;
        position: fixed;
        display: flex;
        flex-direction: column;
    }

    .sidebar h2 {
        margin: 0;
        padding: 20px;
        background: #003366;
        text-align: center;
    }

    .sidebar a {
        padding: 15px 20px;
        display: block;
        color: white;
        text-decoration: none;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar a:hover {
        background: #ffcc00;
        color: #000;
    }

    /* Main content */
    .main-content {
        margin-left: 230px;
        padding: 40px;
        width: calc(100% - 230px);
    }

    .course-box {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        max-width: 900px;
        margin: auto;
    }

    .course-box img {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 10px;
    }

    h2 {
        color: #004d99;
        margin-bottom: 15px;
    }

    .details p {
        font-size: 17px;
        margin: 8px 0;
    }

    .section-title {
        margin-top: 25px;
        font-size: 22px;
        color: #003366;
        border-left: 5px solid #ffcc00;
        padding-left: 10px;
    }

    .qr-box img {
        width: 180px;
        height: 180px;
        margin-top: 10px;
    }

    /* Video Link Section */
    .video-box {
        margin-top: 15px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 10px;
        border: 1px solid #ddd;
    }

    .video-box iframe {
        width: 100%;
        height: 320px;
        border-radius: 10px;
    }

    a.btn-back {
        display: inline-block;
        margin-top: 20px;
        background: #004d99;
        color: #fff;
        padding: 12px 20px;
        text-decoration: none;
        border-radius: 6px;
    }

    a.btn-back:hover {
        background: #003366;
    }
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>OCRS</h2>
    <a href="dashboard.php">📚 Courses</a>
    <a href="pending_courses.php">⏳ Pending Courses</a>
    <a href="enrolled.php">🧾 Enrolled</a>
    <a href="profile.php">👤 Profile</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">

    <div class="course-box">

        <!-- Course Image -->
        <img src="../<?php echo $row['image_url']; ?>" alt="Course">

        <h2><?php echo $row['course_name']; ?></h2>

        <div class="details">
            <p><strong>Enrolled On:</strong> 
                <?php echo date("d-m-Y h:i A", strtotime($row['created_at'])); ?>
            </p>

            <p><strong>Fee Paid:</strong> ₹<?php echo $row['amount']; ?></p>

            <p><strong>Course Duration:</strong> <?php echo $row['duration']; ?></p>

            <p><strong>Course Description:</strong><br>
                <?php echo nl2br($row['description']); ?>
            </p>

            <p><strong>Mode:</strong> <?php echo $row['mode']; ?></p>
        </div>

        <!-- QR Code Section -->
        <h3 class="section-title">🎟 QR Code (E-Pass)</h3>
        <div class="qr-box">
            <p>Show this QR Code for course verification:</p>

            <?php
            $qr_data = "UserID: $user_id | CourseID: $course_id";
            $qr_url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($qr_data);
            ?>

            <img src="<?php echo $qr_url; ?>" alt="QR Code">
        </div>


        <!-- Course Material Section -->
        <h3 class="section-title">📂 Course Materials</h3>
        <p>No materials uploaded yet.</p>


        <!-- ⭐ NEW: VIDEO LECTURE SECTION -->
        <h3 class="section-title">🎥 Video Lesson</h3>
        <div class="video-box">
            <!-- Replace this YouTube link with your own -->
            <iframe 
                src="https://www.youtube.com/embed/dQw4w9WgXcQ" 
                allowfullscreen>
            </iframe>
        </div>


        <a class="btn-back" href="enrolled.php">⬅ Back to My Courses</a>

    </div>

</div>

</body>
</html>
