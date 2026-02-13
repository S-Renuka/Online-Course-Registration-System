<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../ui/login.html");
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid access!'); window.location.href='dashboard.php';</script>";
    exit();
}

$course_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch prerequisites
$preq = $conn->query("
    SELECT * 
    FROM prerequisites 
    WHERE course_id = $course_id
");

// Completed prerequisites by user
$completed = $conn->query("
    SELECT prereq_id 
    FROM prereq_completed 
    WHERE user_id = $user_id
");

$completed_list = [];
while ($c = $completed->fetch_assoc()) {
    $completed_list[] = $c['prereq_id'];
}

$total_preq = $preq->num_rows;
$completed_preq = count($completed_list);

// -------------------------------------------
// 🔍 CHECK IF USER ALREADY ENROLLED THIS COURSE
// -------------------------------------------
$checkEnroll = $conn->prepare("
    SELECT id 
    FROM course_enrollment 
    WHERE user_id = ? AND course_id = ?
");
$checkEnroll->bind_param("ii", $user_id, $course_id);
$checkEnroll->execute();
$already_enrolled = $checkEnroll->get_result()->num_rows > 0;

// CAN ENROLL IF no prerequisites or all prerequisites completed
$can_enroll = ($total_preq == 0) || ($completed_preq == $total_preq);
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $course['course_name']; ?> - Details</title>

<style>
    body { font-family: Arial; background:#f3f8fc; margin:0; display:flex; }
    .sidebar { width:230px; background:#004d99; color:white; height:100vh; position:fixed; padding-top:20px; }
    .sidebar a { padding:14px; display:block; color:white; text-decoration:none; font-size:16px; }
    .sidebar a:hover { background:#003366; }

    .card { 
        background:white; padding:25px; max-width:850px; 
        margin:40px auto; border-radius:12px;
        box-shadow:0 4px 12px rgba(0,0,0,0.1); 
    }

    .prereq-box { 
        width:250px; padding:15px; background:white; border-radius:10px; 
        box-shadow:0 2px 8px rgba(0,0,0,0.1); text-align:center; 
    }

    button { cursor:pointer; }
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2 style="text-align:center;">OCRS</h2>
    <a href="dashboard.php">📚 Courses</a>
    <a href="pending_courses.php">⏳ Pending Courses</a>
    <a href="enrolled.php">🧾 Enrolled Courses</a>
    <a href="profile.php">👤 Profile</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- MAIN CONTENT SECTION -->
<div class="card">

    <img src="../<?php echo $course['image_url']; ?>" 
         style="width:100%; border-radius:8px; height:300px; object-fit:cover;">

    <h2><?php echo $course['course_name']; ?></h2>
    <p><?php echo nl2br($course['description']); ?></p>

    <p><strong>Duration:</strong> <?php echo $course['duration']; ?></p>
    <p><strong>Fee:</strong> ₹<?php echo $course['fee']; ?></p>

    <hr><br>

    <!-- PREREQUISITES -->
    <h3 style="color:#004d99;">Prerequisite Courses</h3>

    <?php if ($total_preq == 0): ?>
        <p>✔ No prerequisites. You can enroll immediately.</p>
    <?php else: ?>
        <p>Completed: <?php echo $completed_preq; ?> / <?php echo $total_preq; ?></p>

        <div style="display:flex; flex-wrap:wrap; gap:20px;">
            <?php
            $preq->data_seek(0);
            while ($p = $preq->fetch_assoc()):
                $done = in_array($p['prereq_id'], $completed_list);
            ?>
            <div class="prereq-box">

                <img src="../<?php echo $p['prereq_image']; ?>" 
                     style="width:100%; height:150px; object-fit:cover; border-radius:8px;">

                <h4><?php echo $p['prereq_name']; ?></h4>

                <?php if ($done): ?>
                    <p style="color:green;">✔ Completed</p>
                <?php else: ?>
                    <form action="prereq-details.php" method="GET">
                        <input type="hidden" name="pid" value="<?php echo $p['prereq_id']; ?>">
                        <button style="background:#004d99; color:white; padding:8px 12px; border:none; border-radius:6px;">
                            View Details
                        </button>
                    </form>
                <?php endif; ?>

            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <br><hr><br>

    <!-- ENROLL BUTTON / ENROLLED BUTTON -->
    <?php if ($already_enrolled): ?>

        <!-- 🔥 Already Enrolled Button -->
        <button disabled style="background:gray; padding:12px 16px; border:none; border-radius:6px; color:white;">
            ✔ Already Enrolled
        </button>

    <?php elseif ($can_enroll): ?>

        <!-- 🟢 Enroll Now -->
        <form action="enroll.php" method="POST">
            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
            <button style="background:green; padding:12px 16px; color:white; border:none; border-radius:6px;">
                ✔ Enroll Now
            </button>
        </form>

    <?php else: ?>

        <!-- 🔴 Can't Enroll -->
        <button disabled style="background:gray; padding:12px 16px; border:none; border-radius:6px; color:white;">
            Complete all prerequisites to enroll
        </button>

    <?php endif; ?>

</div>
</body>
</html>
