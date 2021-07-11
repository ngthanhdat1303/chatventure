
<?php
require_once 'config.php'; //lấy thông tin từ config
$conn = mysqli_connect($DBHOST, $DBUSER, $DBPW, $DBNAME); // kết nối data
$ID = $_GET['ID'];
$cauhoi = $_GET['cauhoi'];
$cauhoi = "\"".$cauhoi."\"";
$ID = "\"".$ID."\"";

$sql = "INSERT INTO `questions` (`cauhoi` , `IDdatcauhoi`) VALUES ($cauhoi , $ID)";
$info = mysqli_query($conn,$sql);
//var_dump($info);
//var_dump(mysqli_error($conn));
//var_dump($sql);

if ($info) {
    echo '{
 "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "elements":[
            {
              "title":"Aduvjp",
              "subtitle":"Ngon iem"
            }
          ]
        }
      }
    }
  ]
}';
} else {
    echo '{
 "messages": [
    {
      "attachment":{
        "type":"template",
        "payload":{
          "template_type":"generic",
          "elements":[
            {
              "title":"Non , quá non",
              "subtitle":""
            }
          ]
        }
      }
    }
  ]
}';
}

?>