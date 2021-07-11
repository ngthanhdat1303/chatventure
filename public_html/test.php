<?php
$ID = $_GET['ID']; // lấy id từ chatfuel
$requestgt = $_GET['requestgt'];// lấy giới tính yêu cầu
$gioitinh = $_GET['gt'];// lấy giới tính

require_once 'config.php'; //lấy thông tin từ config
$conn = mysqli_connect($DBHOST, $DBUSER, $DBPW, $DBNAME); // kết nối data
////// Hàm Gửi JSON //////////

/// Xét giới tính
if ($gioitinh == 'male'){
    $gioitinh = 1;
} else if ($gioitinh == 'female'){
    $gioitinh = 2;
} else $gioitinh = 0;

function request($userid,$jsondata) {
    global $TOKEN;
    global $BOT_ID;
    global $BLOCK_NAME;
    $url = "https://api.chatfuel.com/bots/$BOT_ID/users/$userid/send?chatfuel_token=$TOKEN&chatfuel_block_name=$BLOCK_NAME";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($ch);
    $errorChat = '{
     "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "elements":[
            {
              "title":"Lỗi :<",
              "subtitle":"Đã xảy ra lỗi gửi tin. Bạn gửi lại thử nhé."
            }
          ]
        }
      }
    }
  ]
} ';
    if (curl_errno($ch)) {
        echo errorChat;
    } else {
        $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($resultStatus == 200) {
            // send ok
        } else {
            echo errorChat;
        }
    }
    curl_close($ch);


}
///// Hàm gửi tin nhắn //////////

function sendchat($userid,$noidung){
    global $JSON;
    $payload = '{"'.$JSON.'":"'.$noidung.'"}';
    request($userid,$payload);
}


///// hàm kiểm tra hàng chờ ///////
function hangcho($userid) {
    global $conn;

    $result = mysqli_query($conn, "SELECT `hangcho` from `users` WHERE `ID` = $userid");
    $row = mysqli_fetch_assoc($result);

    return intval($row['hangcho']) !== 0;
}

//// Kết nối hai người /////
function addketnoi($user1, $user2) {
    global $conn;

    $getuser1 = mysqli_query($conn, "SELECT `ten` from `users` WHERE `ID` = $user1");
    $rowuser1 = mysqli_fetch_assoc($getuser1);
    $getuser2 = mysqli_query($conn, "SELECT `ten` from `users` WHERE `ID` = $user2");
    $rowuser2 = mysqli_fetch_assoc($getuser2);
    $user1name = "\"" . $rowuser1['ten'] . "\"";
    $user2name = "\"" . $rowuser2['ten'] . "\"";
    sendchat($user1, mysqli_error($conn));
    sendchat($user2, mysqli_error($conn));
    mysqli_query($conn, "UPDATE `users` SET `trangthai` = 1, `ketnoi` = $user2, `hangcho` = 0, `IDketnoicuoi` = $user2, `tenketnoicuoi` = $user2name WHERE `ID`= $user1");
    sendchat($user1, mysqli_error($conn));
    sendchat($user2, mysqli_error($conn));
    mysqli_query($conn, "UPDATE `users` SET `trangthai` = 1, `ketnoi` = $user1, `hangcho` = 0, `IDketnoicuoi` = $user1, `tenketnoicuoi` = $user1name WHERE `ID`= $user2");
    sendchat($user1, mysqli_error($conn));
    sendchat($user2, mysqli_error($conn));
}
/////Tìm kiếm kết nối /////

function ketnoi($userid,$requestgt, $gioitinh) { //tìm người
    global $conn;

    $getuserlastconnect = mysqli_query($conn, "SELECT `IDketnoicuoi` from `users` WHERE `ID` = $userid");
    $userlastconnectarr = mysqli_fetch_assoc($getuserlastconnect);
    $userlastconnect = $userlastconnectarr['IDketnoicuoi'];
    if (is_null($userlastconnect)) $userlastconnect = 0;

    if($requestgt == "female"){// giới tính là nữ
        $result = mysqli_query($conn, "SELECT `ID` FROM `users` WHERE `ID` != $userid AND `hangcho` = 1 AND `gioitinh` = 2 AND (`yeucau` = $gioitinh OR `yeucau` = 0) AND `ID` != $userlastconnect AND `ID` NOT IN (SELECT `idBlocked` FROM `block` WHERE `idBlock` = $userid) LIMIT 1");
        //echo "result : " . $result."<br>";
    }else if($requestgt == "male"){// giới tính là nam
        $result = mysqli_query($conn, "SELECT `ID` FROM `users` WHERE `ID` != $userid AND `hangcho` = 1 AND `gioitinh` = 1 AND (`yeucau` = $gioitinh OR `yeucau` = 0) AND `ID` != $userlastconnect AND `ID` NOT IN (SELECT `idBlocked` FROM `block` WHERE `idBlock` = $userid) LIMIT 1");
    }else{ // Tìm kiếm người không xác định
        $result = mysqli_query($conn, "SELECT `ID` FROM `users` WHERE `ID` != $userid AND `hangcho` = 1 AND (`yeucau` = $gioitinh OR `yeucau` = 0) AND `ID` != $userlastconnect AND `ID` NOT IN (SELECT `idBlocked` FROM `block` WHERE `idBlock` = $userid) LIMIT 1");
    }
    //echo $result;
    $row = mysqli_fetch_assoc($result);
    $partner = $row['ID'];
    // xử lý kiểm tra
    if ($partner == 0) { // nếu người không có ai trong hàng chờ
        mysqli_query($conn, "UPDATE `users` SET `hangcho` = 1 WHERE `ID` = $userid");
        if($requestgt == 'female'){
            echo'{
     "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "elements":[
            {
              "title":"Cứ bình tĩnh",
              "subtitle":"Một bạn nữ sẽ xuất hiện nhanh thôi"
            }
          ]
        }
      }
    }
  ]
} ';

        }else if($requestgt == 'male'){
            echo'{
 "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "elements":[
            {
              "title":"Cứ bình tĩnh",
              "subtitle":"Một bạn nam sẽ xuất hiện nhanh thôi"
            }
          ]
        }
      }
    }
  ]
}  ';

        }else{
            echo'{
 "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "elements":[
            {
              "title":"Cứ bình tĩnh",
              "subtitle":"Rồi một người bạn cũng sẽ đến"
            }
          ]
        }
      }
    }
  ]
}';
        }
    } else {  // neu co nguoi trong hàng chờ
        addketnoi($userid, $partner);
        sendchat($partner,"Bạn đã được kết nối. Chúc bạn may mắn!");
        sendchat($userid,"Bạn đã được kết nối. Chúc bạn may mắn!");
    }
}

//////// LẤY ID NGƯỜI CHÁT CÙNG ////////////
function getRelationship($userid) {
    global $conn;

    $result = mysqli_query($conn, "SELECT `ketnoi` from `users` WHERE `ID` = $userid");
    $row = mysqli_fetch_assoc($result);
    $relationship = $row['ketnoi'];
    return $relationship;
}

//// hàm kiểm tra trạng thái
function trangthai($userid) {
    global $conn;

    $result = mysqli_query($conn, "SELECT `trangthai` from `users` WHERE `ID` = $userid");
    $row = mysqli_fetch_assoc($result);

    return intval($row['trangthai']) !== 0;
}

//// Xử lý //////
addketnoi(5676236405727141 , 4007404939324313);
mysqli_close($conn);
?>