<?php
session_start();
include '../db/db_connect.php';
require '../vendor/autoload.php';

use Smalot\PdfParser\Parser;

$pid = intval($_GET['pid']);
$cid = intval($_GET['cid']);

$stmt = $conn->prepare("SELECT pdf1 FROM prerequisites WHERE prereq_id=?");
$stmt->bind_param("i", $pid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$parser = new Parser();
$pdf = $parser->parseFile('../'.$row['pdf1']);
$text = $pdf->getText();

$sentences = array_values(array_filter(
    preg_split('/(?<=[.?!])\s+/', $text),
    fn($s) => strlen($s) > 30
));

$mcqs = [];
for ($i=1; $i<=10 && count($sentences)>=4; $i++){
    shuffle($sentences);
    $mcqs[] = [
        'q'=>$sentences[0],
        'o'=>[$sentences[1],$sentences[2],$sentences[3],$sentences[0]],
        'c'=>4
    ];
}
?>

<form id="mcqForm" method="POST" action="mark_prereq_complete.php">
<?php $i=1; foreach($mcqs as $m): ?>
<p><b><?php echo $i.". ".$m['q']; ?></b></p>
<?php foreach($m['o'] as $k=>$opt): ?>
<label>
<input type="radio" name="q<?php echo $i;?>" value="<?php echo $k+1;?>" required>
<?php echo htmlspecialchars($opt); ?>
</label><br>
<?php endforeach; ?>
<input type="hidden" name="correct<?php echo $i;?>" value="<?php echo $m['c']; ?>">
<?php $i++; endforeach; ?>

<input type="hidden" name="pid" value="<?php echo $pid; ?>">
<input type="hidden" name="cid" value="<?php echo $cid; ?>">
<button type="button" onclick="submitMCQ()">Submit</button>
</form>

<script>
function submitMCQ(){
    let total=10, correct=0, f=document.getElementById('mcqForm');
    for(let i=1;i<=total;i++){
        let r=f['q'+i], c=f['correct'+i].value;
        for(let x of r){ if(x.checked && x.value==c) correct++; }
    }
    if(correct>=5){
        alert("Passed!");
        f.submit();
        setTimeout(()=>location.href="course-details.php?id=<?php echo $cid;?>",500);
    } else {
        alert("Failed: "+correct+"/10");
    }
}
</script>
