<?php
session_start();
include '../db/db_connect.php';
require '../vendor/autoload.php';

use Smalot\PdfParser\Parser;

/* Auth check */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../ui/login.html");
    exit();
}

/* PID check */
if (!isset($_GET['pid'])) {
    header("Location: dashboard.php");
    exit();
}

$pid = intval($_GET['pid']);
$uid = $_SESSION['user_id'];

/* Fetch prerequisite */
$stmt = $conn->prepare("SELECT * FROM prerequisites WHERE prereq_id=?");
$stmt->bind_param("i", $pid);
$stmt->execute();
$prereq = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$prereq) {
    header("Location: dashboard.php");
    exit();
}

$cid = $prereq['course_id'];

/* Check completion */
$isCompleted = false;
$chk = $conn->prepare("SELECT 1 FROM prereq_completed WHERE user_id=? AND prereq_id=?");
$chk->bind_param("ii", $uid, $pid);
$chk->execute();
if ($chk->get_result()->num_rows > 0) $isCompleted = true;
$chk->close();

/* Generate MCQs */
$mcqs = [];
if (!$isCompleted && $prereq['pdf1']) {

    $parser = new Parser();
    $pdf = $parser->parseFile('../' . $prereq['pdf1']);
    $text = preg_replace('/\s+/', ' ', $pdf->getText());

    $sentences = array_values(array_filter(
        preg_split('/[.?!]/', $text),
        fn($s) => strlen(trim($s)) > 40
    ));

    shuffle($sentences);

    for ($i = 0; $i < 10 && count($sentences) >= 4; $i++) {
        $options = array_slice($sentences, 0, 4);
        shuffle($options);

        $mcqs[] = [
            'q' => 'Which statement is correct?',
            'opts' => $options,
            'ans' => rand(1, 4)
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?= htmlspecialchars($prereq['prereq_name']) ?></title>
<style>
body{font-family:Arial;background:#f5f8ff;margin:0;display:flex}
.sidebar{width:230px;background:#003b7a;color:white;height:100vh;position:fixed}
.sidebar h2{text-align:center;margin:25px 0}
.sidebar a{display:block;padding:14px 20px;color:white;text-decoration:none}
.sidebar a:hover{background:#ffcc00;color:black}
.main{margin-left:230px;width:calc(100% - 230px)}
.nav{background:#004d99;color:white;padding:15px 40px;display:flex;justify-content:space-between}
.card{background:white;width:900px;margin:30px auto;padding:25px;border-radius:12px}
.hidden{display:none}
.btn{padding:12px 16px;border:none;border-radius:6px;font-size:16px;cursor:pointer}
.pass{background:green;color:white}
.back{background:#004d99;color:white}
</style>
</head>

<body>

<div class="sidebar">
<h2>OCRS</h2>
<a href="dashboard.php">📚 Courses</a>
<a href="enrolled.php">🧾 Enrolled</a>
<a href="profile.php">👤 Profile</a>
<a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
<div class="nav">
<b><?= htmlspecialchars($prereq['prereq_name']) ?></b>
<span>Welcome <?= htmlspecialchars($_SESSION['username']) ?></span>
</div>

<div class="card">

<img src="../<?= $prereq['prereq_image'] ?>" style="width:100%;height:300px;object-fit:cover;border-radius:10px">

<h2><?= htmlspecialchars($prereq['prereq_name']) ?></h2>
<p><?= nl2br(htmlspecialchars($prereq['agenda'])) ?></p>
<hr>

<h3>📘 Study Material</h3>
<a href="../<?= $prereq['pdf1'] ?>" target="_blank" onclick="enableTest()">📄 Open PDF</a>

<?php if(!$isCompleted): ?>
<div id="mcq-test" class="hidden">
<hr>
<h3>📝 MCQ Test</h3>

<form id="mcqForm" method="POST" action="mark_prereq_complete.php">

<?php $i=1; foreach($mcqs as $m): ?>
<p><b><?= $i ?>. <?= $m['q'] ?></b></p>

<?php for($j=1;$j<=4;$j++): ?>
<label>
<input type="radio" name="q<?= $i ?>" value="<?= $j ?>" required>
<?= htmlspecialchars($m['opts'][$j-1]) ?>
</label><br>
<?php endfor; ?>

<input type="hidden" name="ans<?= $i ?>" value="<?= $m['ans'] ?>">
<hr>
<?php $i++; endforeach; ?>

<input type="hidden" name="pid" value="<?= $pid ?>">
<input type="hidden" name="cid" value="<?= $cid ?>">

<button type="button" class="btn pass" onclick="submitMCQ()">Submit Test</button>
</form>
</div>
<?php endif; ?>

<br>
<button class="btn back" onclick="history.back()">⬅ Back</button>

</div>
</div>

<script>
function enableTest(){
    document.getElementById('mcq-test').classList.remove('hidden');
}

function submitMCQ(){
    let score = 0;

    for(let i=1;i<=10;i++){
        let radios = document.getElementsByName('q'+i);
        let ans = document.querySelector('input[name="ans'+i+'"]').value;
        radios.forEach(r=>{
            if(r.checked && r.value == ans) score++;
        });
    }

    if(score >= 5){
        alert("🎉 Passed!");
        document.getElementById('mcqForm').submit();
    } else {
        alert("❌ Failed " + score + "/10. Try again.");
    }
}
</script>

</body>
</html>
