<?php

$ID = $_GET['ID'];// lấy id từ chatfuel
require_once 'config.php'; //lấy thông tin từ config

$conn = mysqli_connect($DBHOST, $DBUSER, $DBPW, $DBNAME); // kết nối data
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

// Số người trong hàng chờ

$conn = mysqli_connect($DBHOST, $DBUSER, $DBPW, $DBNAME); // kết nối data

$getusernumber = mysqli_query($conn, "SELECT COUNT(*) from `users` WHERE hangcho = 1");

$usernumber = mysqli_fetch_assoc($getusernumber);
$usernumber = $usernumber['COUNT(*)'];

//////// LẤY ID NGƯỜI CHÁT CÙNG ////////////
function getRelationship($userid) {
  global $conn;

  $result = mysqli_query($conn, "SELECT ketnoi from users WHERE ID = $userid");
  $row = mysqli_fetch_assoc($result);
  $relationship = $row['ketnoi'];
  return $relationship;
}

////// Hàm Gửi JSON //////////
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

function endchat($userid,$noidung){
global $JSON;
$payload = '{"'.$JSON.'":"'.$noidung.'","chat":"off"}';
request($userid,$payload);		
}

function outchat($userid) {
  global $conn;
  global $usernumber;
  $partner = getRelationship($userid);
  mysqli_query($conn, "UPDATE `users` SET `trangthai` = 0, `ketnoi` = NULL, `hangcho` = 0 WHERE `ID` = $userid");
  mysqli_query($conn, "UPDATE `users` SET `trangthai` = 0, `ketnoi` = NULL, `hangcho` = 0 WHERE `ID` = $partner");
  if ($usernumber != "0") {
      sendchat($userid, "Bạn đã thoát! Hiện có $usernumber người hiện đang chờ để tham gia cuộc trò chuyện. Để tiếp tục hãy gõ Start");
      endchat($partner, "Người bạn kia đã chuồn! Hiện có $usernumber người hiện đang chờ để tham gia cuộc trò chuyện. Để tiếp tục hãy gõ Start");
  } else {
      sendchat($userid, "Bạn đã thoát! Tiếp tục cuộc trò chuyện bằng cách gõ Batdau");
      endchat($partner, "Người bạn kia đã chuồn! Tiếp tục cuộc trò chuyện bằng cách gõ Batdau");
  }
}


function hangcho($userid) {
  global $conn;

  $result = mysqli_query($conn, "SELECT `hangcho` from `users` WHERE `ID` = $userid");
  $row = mysqli_fetch_assoc($result);

  return intval($row['hangcho']) !== 0;
}

function trangthai($userid) {
  global $conn;

  $result = mysqli_query($conn, "SELECT `trangthai` from `users` WHERE `ID` = $userid");
  $row = mysqli_fetch_assoc($result);

  return intval($row['trangthai']) !== 0;
}


if (!trangthai($ID)){ // nếu chưa chát
if (!hangcho($ID)) { // nếu không ở trong hàng chờ

echo'{
 "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "elements":[
            {
              "title":"Tâm bất biện giữa dòng đời vạn biến",
              "subtitle":"Gõ Start để trải nghiệm cuộc sống"
            }
          ]
        }
      }
    }
  ]
}';

}else{ // nếu đang ở trong hàng chờ
echo'{
 "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "elements":[
            {
              "title":"Cya",
              "subtitle":"Bạn đã thoát! Hẹn gặp bạn lần sau"
            }
          ]
        }
      }
    }
  ]
}';
mysqli_query($conn, "UPDATE `users` SET `hangcho` = 0 WHERE `ID` = $ID");
}
}else{
// nếu đang chát
//giải quyết sau
outchat($ID);
}
mysqli_close($conn);
?>