<?php
session_start();
include '../db/db_connect.php';

/* ---------- LOGIN CHECK ---------- */
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='../ui/login.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------- GET COURSE ID ---------- */
$course_id = $_GET['course_id'] ?? $_POST['course_id'] ?? 0;
$course_id = intval($course_id);

if ($course_id <= 0) {
    echo "<script>alert('Invalid course'); window.location.href='courses.php';</script>";
    exit();
}

/* ---------- FETCH COURSE ---------- */
$stmt = $conn->prepare("SELECT course_name, fee FROM courses WHERE id=?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    echo "<script>alert('Course not found'); window.location.href='courses.php';</script>";
    exit();
}

/* ---------- UPI QR DETAILS ---------- */
$upiId  = "7904982922@ybl";   // 🔴 Replace with your UPI ID
$payee  = "Course Academy";
$amount = $course['fee'];

$upiUrl = "upi://pay?pa=$upiId&pn=" . urlencode($payee) .
          "&am=$amount&cu=INR&tn=Course Payment";

/* ---------- FORM SUBMISSION ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {

    if (
        empty($_POST['name']) ||
        empty($_POST['email']) ||
        empty($_POST['phone']) ||
        empty($_POST['mode']) ||
        empty($_POST['transaction_id']) ||
        empty($_FILES['screenshot']['name'])
    ) {
        echo "<script>alert('All fields are required');</script>";
    } else {

        $name           = $_POST['name'];
        $email          = $_POST['email'];
        $phone          = $_POST['phone'];
        $mode           = $_POST['mode'];
        $transaction_id = $_POST['transaction_id'];

        /* ---------- UPLOAD SCREENSHOT ---------- */
        $folder = "../uploads/payment/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["screenshot"]["name"]);
        $target   = $folder . $fileName;

        if (!move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target)) {
            echo "<script>alert('Screenshot upload failed');</script>";
            exit();
        }

        $screenshotPath = "uploads/payment/" . $fileName;

        /* ---------- INSERT ENROLLMENT ---------- */
        $stmt = $conn->prepare("
            INSERT INTO course_enrollment
            (user_id, course_id, student_name, email, phone, mode, amount, transaction_id, payment_screenshot, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
        ");

        $stmt->bind_param(
            "iissssdss",
            $user_id,
            $course_id,
            $name,
            $email,
            $phone,
            $mode,
            $amount,
            $transaction_id,
            $screenshotPath
        );

        if ($stmt->execute()) {
            echo "<script>alert('Enrollment submitted successfully! Waiting for admin approval'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Database Error');</script>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Course Enrollment</title>
<style>
body {
    font-family: Poppins, sans-serif;
    background: linear-gradient(to right, #74ebd5, #acb6e5);
    display: flex;
    justify-content: center;
    padding: 40px;
}
.form-container {
    background: #fff;
    padding: 30px;
    width: 450px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}
label { margin-top: 15px; display: block; }
input, select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
button {
    margin-top: 20px;
    padding: 12px;
    width: 100%;
    border: none;
    border-radius: 10px;
    background: #2575fc;
    color: white;
    font-size: 16px;
    cursor: pointer;
}
button:hover { background: #6a11cb; }
#qr-section {
    display: none;
    text-align: center;
    margin-top: 20px;
}
#qr-section img {
    width: 220px;
    margin: 10px 0;
}
</style>
</head>

<body>

<div class="form-container">
<h2>Course Enrollment</h2>
<h3 style="text-align:center;"><?php echo htmlspecialchars($course['course_name']); ?></h3>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

<label>Full Name</label>
<input type="text" name="name" required>

<label>Email</label>
<input type="email" name="email" required>

<label>Phone</label>
<input type="tel" name="phone" pattern="[0-9]{10}" required>

<label>Mode</label>
<select name="mode" required>
    <option value="">--Select--</option>
    <option>Live</option>
    <option>Recorded</option>
    <option>Live + Recorded</option>
</select>

<label>Amount</label>
<input type="text" value="₹<?php echo $amount; ?>" readonly>

<button type="button" id="payBtn">Proceed to Payment</button>

<div id="qr-section">
    <p><b>Scan & Pay using UPI</b></p>

    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($upiUrl); ?>">

    <label>UPI Transaction ID</label>
    <input type="text" name="transaction_id" placeholder="Enter Transaction ID" required>

    <label>Upload Payment Screenshot</label>
    <input type="file" name="screenshot" accept="image/*" required>

    <button type="submit">Complete Enrollment</button>
</div>

</form>
</div>

<script>
document.getElementById("payBtn").onclick = function () {
    this.style.display = "none";
    document.getElementById("qr-section").style.display = "block";
};
</script>

</body>
</html>
