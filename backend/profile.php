<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../ui/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];


// ============================================================
//  PROCESS UPDATE PROFILE
// ============================================================
if (isset($_POST['update_profile'])) {

    $name       = $_POST['name'];
    $email      = $_POST['email'];
    $phone      = $_POST['phone'];
    $dob        = $_POST['dob'];
    $gender     = $_POST['gender'];
    $address    = $_POST['address'];
    $city       = $_POST['city'];
    $student_id = $_POST['student_id'];
    $department = $_POST['department'];

    // Handle profile picture upload
    $new_pic = "";

    if (!empty($_FILES['profile_pic']['name'])) {

        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $targetPath = "../uploads/" . $fileName;

        // Create folder if not exists
        if (!is_dir("../uploads")) {
            mkdir("../uploads", 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
            $new_pic = "uploads/" . $fileName;

            // Update DB with image
            $pic_sql = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
            $pic_sql->bind_param("si", $new_pic, $user_id);
            $pic_sql->execute();
            $pic_sql->close();

            $_SESSION['profile_pic'] = $new_pic;
        }
    }

    // Update all other fields
    $sql = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, dob=?, gender=?, address=?, city=?, student_id=?, department=? WHERE id=?");
    $sql->bind_param("sssssssssi", 
        $name, $email, $phone, $dob, $gender, $address, $city, $student_id, $department, $user_id
    );

    if ($sql->execute()) {
        // Update session username (for navbar)
        $_SESSION['username'] = $name;

        echo "<script>alert('Profile Updated Successfully'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Update Failed');</script>";
    }

    $sql->close();
}


// ============================================================
//  PROCESS CHANGE PASSWORD
// ============================================================
if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $newp    = $_POST['new_password'];
    $conf    = $_POST['confirm_password'];

    // Fetch old password
    $check = $conn->prepare("SELECT password FROM users WHERE id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();
    $check->close();

    if (!password_verify($current, $row['password'])) {
        echo "<script>alert('Current Password Wrong');</script>";
    } elseif ($newp != $conf) {
        echo "<script>alert('Passwords Do Not Match');</script>";
    } else {
        $hashed = password_hash($newp, PASSWORD_DEFAULT);

        $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si", $hashed, $user_id);
        $upd->execute();
        $upd->close();

        echo "<script>alert('Password Changed Successfully'); window.location.href='profile.php';</script>";
    }
}



// ============================================================
//  FETCH USER DETAILS
// ============================================================
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Profile - OCRS</title>

<style>
/* (YOUR STYLES — unchanged) */
body { 
    font-family: Arial, sans-serif; 
    background:#f3f8fc; 
    margin:0; 
    display:flex; 
}
.sidebar { width:230px; background:#004d99; color:white; height:100vh; position:fixed; padding-top:20px; }
.sidebar h2 { text-align:center; background:#003366; margin:0; padding:20px 0; font-size:22px; }
.sidebar a { display:block; padding:14px 20px; color:white; text-decoration:none; font-size:16px; border-bottom:1px solid rgba(255,255,255,0.1); }
.sidebar a:hover, .sidebar a.active { background:#ffcc00; color:#000; font-weight:bold; }
.main-content { margin-left:230px; width: calc(100% - 230px); display:flex; flex-direction:column; min-height:100vh; }
.navbar { background:#004d99; display:flex; justify-content:space-between; align-items:center; padding:15px 40px; color:white; }
.navbar img { height:40px; margin-right:10px; border-radius:5px; }
.container { padding:40px; flex-grow:1; }
h2 { font-size:28px; text-align:center; margin-bottom:20px; color:#004d99; }
.profile-wrapper { display: flex; gap: 25px; justify-content: center; margin-top: 30px; }
.left-card { width: 280px; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
.left-card img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd; }
.student-name { font-size: 22px; margin-top: 15px; font-weight: bold; color: #333; }
.course-title { font-size: 16px; color: #444; margin-top: 10px; }
.batch-year { font-size: 18px; font-weight: bold; color: #ff9933; margin-top: 15px; }
.right-card { width: 550px; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.details-table { width: 100%; border-collapse: collapse; }
.details-table th { width: 40%; background: #f1f7ff; padding: 10px; text-align: left; color: #004d99; font-weight: bold; }
.details-table td { padding: 10px; background: #fafafa; }
.btn-area { margin-top: 20px; display:flex; gap:10px; }
.btn-edit, .btn-pass { padding: 10px 18px; border: none; border-radius: 6px; cursor:pointer; font-weight:bold; color:white; }
.btn-edit { background:#2575fc; }
.btn-pass { background:#6a11cb; }
form { background:white; padding:25px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); max-width:700px; margin:auto; display:none; }
form label { margin-top:10px; font-weight:bold; display:block; }
form input, form select { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; margin-top:5px; }
form button { margin-top:15px; padding:12px; border:none; border-radius:8px; background:#2575fc; color:white; font-weight:bold; cursor:pointer; }
form button:hover { background:#6a11cb; }
footer { background:#003366; color:white; text-align:center; padding:15px; font-size:14px; margin-top:auto; }
</style>
</head>


<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>OCRS</h2>
    <a href="dashboard.php">📚 Courses</a>
    <a href="pending_courses.php">⏳ Pending Courses</a>
    <a href="enrolled.php">🧾 Enrolled</a>
    <a class="active">👤 Profile</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- Main -->
<div class="main-content">

    <div class="navbar">
        <div class="logo">
            <img src="../images/login.jpg" alt=""> Student Dashboard
        </div>
        <span>Welcome, <?php echo $_SESSION['username']; ?> 👋</span>
    </div>

    <div class="container">

        <h2>Student Profile</h2>

        <div class="profile-wrapper" id="profile-view">

            <div class="left-card">
                <img src="../<?php echo $user['profile_pic'] ?: 'images/default-profile.png'; ?>">
                <h3 class="student-name"><?php echo $user['name']; ?></h3>
                <p class="course-title">Bachelor of Engineering (BE)<br><?php echo $user['department']; ?></p>
                <p class="batch-year">2023 - 2027</p>
            </div>

            <div class="right-card">
                <table class="details-table">
                    <tr><th>Full Name</th><td><?= $user['name'] ?></td></tr>
                    <tr><th>Register Number</th><td><?= $user['student_id'] ?></td></tr>
                    <tr><th>Gender</th><td><?= $user['gender'] ?></td></tr>
                    <tr><th>Date of Birth</th><td><?= $user['dob'] ?></td></tr>
                    <tr><th>Blood Group</th><td>B+VE</td></tr>
                    <tr><th>Mobile</th><td><?= $user['phone'] ?></td></tr>
                    <tr><th>Email</th><td><?= $user['email'] ?></td></tr>
                    <tr><th>Address</th><td><?= $user['address'] ?></td></tr>
                    <tr><th>City/State</th><td><?= $user['city'] ?></td></tr>
                </table>

                <div class="btn-area">
                    <button class="btn-edit" onclick="showForm('edit')">✏️ Edit Profile</button>
                    <button class="btn-pass" onclick="showForm('password')">🔑 Change Password</button>
                </div>
            </div>
        </div>


        <!-- EDIT PROFILE FORM -->
        <form method="POST" enctype="multipart/form-data" id="edit-form">
            <h3>Edit Profile</h3>

            <label>Profile Picture</label>
            <input type="file" name="profile_pic">

            <label>Name</label>
            <input type="text" name="name" value="<?= $user['name'] ?>">

            <label>Email</label>
            <input type="email" name="email" value="<?= $user['email'] ?>">

            <label>Phone</label>
            <input type="text" name="phone" value="<?= $user['phone'] ?>">

            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= $user['dob'] ?>">

            <label>Gender</label>
            <select name="gender">
                <option <?= $user['gender']=='Male'?'selected':'' ?>>Male</option>
                <option <?= $user['gender']=='Female'?'selected':'' ?>>Female</option>
            </select>

            <label>Address</label>
            <input type="text" name="address" value="<?= $user['address'] ?>">

            <label>City/State</label>
            <input type="text" name="city" value="<?= $user['city'] ?>">

            <label>Student ID</label>
            <input type="text" name="student_id" value="<?= $user['student_id'] ?>">

            <label>Department</label>
            <input type="text" name="department" value="<?= $user['department'] ?>">

            <button type="submit" name="update_profile">Save Changes</button>
            <button type="button" onclick="goBack()" style="background:gray;">Back</button>
        </form>


        <!-- CHANGE PASSWORD FORM -->
        <form method="POST" id="password-form">
            <h3>Change Password</h3>

            <label>Current Password</label>
            <input type="password" name="current_password">

            <label>New Password</label>
            <input type="password" name="new_password">

            <label>Confirm Password</label>
            <input type="password" name="confirm_password">

            <button type="submit" name="change_password">Change Password</button>
            <button type="button" onclick="goBack()" style="background:gray;">Back</button>
        </form>

    </div>

    <footer>&copy; 2025 Online Course Registration System</footer>

</div>

<script>
function showForm(type) {
    document.getElementById('profile-view').style.display = 'none';
    if(type === 'edit') document.getElementById('edit-form').style.display = 'block';
    if(type === 'password') document.getElementById('password-form').style.display = 'block';
}

function goBack() {
    document.getElementById('edit-form').style.display = 'none';
    document.getElementById('password-form').style.display = 'none';
    document.getElementById('profile-view').style.display = 'flex';
}
</script>

</body>
</html>
