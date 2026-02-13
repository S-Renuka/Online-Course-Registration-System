<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../ui/login.html");
    exit();
}

if (!isset($_POST['pid'], $_POST['cid'])) {
    header("Location: dashboard.php");
    exit();
}

$uid = $_SESSION['user_id'];
$pid = intval($_POST['pid']);
$cid = intval($_POST['cid']);

$stmt = $conn->prepare(
    "INSERT IGNORE INTO prereq_completed (user_id, prereq_id)
     VALUES (?, ?)"
);
$stmt->bind_param("ii", $uid, $pid);
$stmt->execute();
$stmt->close();

/* ✅ PERFECT REDIRECT */
header("Location: course-details.php?id=".$cid);
exit();
