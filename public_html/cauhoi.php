<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<style type="text/css">
    .question {
        font-size: 2.5vmax;
    }

    .panel {
        cursor: pointer;
    }

    .answer {
        font-size: 2vmax;
    }
</style>
<?php
require_once 'config.php'; //lấy thông tin từ config
$conn = mysqli_connect($DBHOST, $DBUSER, $DBPW, $DBNAME) or die ('Không thể kết nối tới database');
$ID = $_GET['ID'];
$questions = array();
$sql = "SELECT `cauhoi` , `ID` from `questions` WHERE `IDdatcauhoi` = $ID";
$info = mysqli_query($conn,$sql);
while ($get = mysqli_fetch_array($info)) {
    array_push($questions , $get);
}

for ($i = 0; $i < sizeof($questions); $i++) {
    $question = $questions[$i][0];
    echo "
<div class=\"panel-group\">
    <div class=\"panel panel-default\" data-toggle=\"collapse\" href=\"#collapse$i\">
        <div class=\"panel-heading\">
            <h4 class=\"panel-title\">
                  <div class=\"question text-center row justify-content-center align-self-center\">
                  $question
                  </div>
            </h4>
        </div>
        <div id=\"collapse$i\" class=\"panel-collapse collapse\">
";
    $answers = array();
    $IDquestions = $questions[$i][1];
    $sql = "SELECT `traloi` from `answers` WHERE `IDcauhoi` = $IDquestions";
    $info = mysqli_query($conn,$sql);
    while ($get = mysqli_fetch_array($info)) {
        array_push($answers , $get);
    }


    for ($j = 0; $j < sizeof($answers); $j++) {
        $answer = $answers[$j][0];
        echo "
            <div class=\"answer panel-body\"> $answer </div>
        ";
    }
    echo "
        </div>
    </div>
</div>
";
}
?>
