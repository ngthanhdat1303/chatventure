
<?php
require_once 'config.php'; //lấy thông tin từ config
$conn = mysqli_connect($DBHOST, $DBUSER, $DBPW, $DBNAME); // kết nối data
$ID = $_GET['ID'];
$traloi = $_GET['traloi'];
$traloi = "\"".$traloi."\"";

function requestText($userid, $jsondata)
{ // hàm gửi chát :)))
    global $errorChat;
    global $TOKEN;
    global $BOT_ID;
    global $BLOCK_NAME;
    $url = "https://api.chatfuel.com/bots/$BOT_ID/users/$userid/send?chatfuel_token=$TOKEN&chatfuel_block_name=$BLOCK_NAME";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, html_entity_decode($jsondata));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($ch);

    if (curl_errno($ch)) {
        echo $errorChat;
    } else {
        $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($resultStatus != 200) {
            echo $errorChat;
        }
    }
    curl_close($ch);

}

function isAnswerExist($userid , $cauhoi) { //hàm kiểm tra xem user đã tồn tại chưa
    global $conn;
    $result = mysqli_query($conn, "SELECT `ID` from `answers` WHERE `IDtraloi` = $userid AND `IDcauhoi` = $cauhoi LIMIT 1");
    $row = mysqli_num_rows($result);
    //var_dump(mysqli_error($conn));
    return $row;
}

function sendchat($userid, $noidung)
{
    global $JSON;
    $payload = '{"' . $JSON . '":' . json_encode($noidung) . '}';
    requestText($userid, $payload);
}

$sql = "SELECT `IDcauhoi` from `users` WHERE `ID` = $ID";
$info = mysqli_query($conn,$sql);
$infocauhoi = mysqli_fetch_assoc($info);
$cauhoi = $infocauhoi['IDcauhoi'];
if ($cauhoi == 0) {
    sendchat($ID , "Damn bro bạn kiểu làm bài không cần đọc đề");
} else {
    sendchat($ID , "Câu trả lời của bạn đã được ghi lại. Cảm ơn rất nhiều UwU");
    if (isAnswerExist($ID , $cauhoi)) {
        $sql = "UPDATE `answers` SET `traloi` = $traloi WHERE `IDtraloi` = $ID AND `IDcauhoi` = $cauhoi LIMIT 1";
    } else {
        $sql = "INSERT INTO `answers` (`IDtraloi` , `traloi` , `IDcauhoi`) VALUES ($ID,$traloi ,$cauhoi)";
    }
    $info = mysqli_query($conn,$sql);
    //var_dump($traloi);
    //var_dump($infocauhoi);
    //var_dump($info);
    //var_dump(mysqli_error($conn));
    //var_dump($sql);
}

?>