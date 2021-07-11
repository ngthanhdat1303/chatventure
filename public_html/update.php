<?php
require_once 'config.php'; //lấy thông tin từ config
$conn = mysqli_connect($DBHOST, $DBUSER, $DBPW, $DBNAME) or die ('Không thể kết nối tới database');
$ID = $_GET['ID'];// lấy id từ chatfuel
$gioitinh = $_GET['gt']; // lấy giới tính
$requestgt = $_GET['requestgt'];
$firstname = $_GET['firstname'];
$lastname = $_GET['lastname'];
$name = "\"" . $lastname . " " . $firstname . "\"";

function isUserExist($userid) { //hàm kiểm tra xem user đã tồn tại chưa 
    global $conn;
    $result = mysqli_query($conn, "SELECT `ID` from `users` WHERE `ID` = $userid LIMIT 1");
    $row = mysqli_num_rows($result);
    //var_dump(mysqli_error($conn));
    return $row;
}

/// Xét giới tính
if ($gioitinh == 'male'){
    $gioitinh = 1;
} else if ($gioitinh == 'female'){
    $gioitinh = 2;
} else $gioitinh = 0;

if ($requestgt == 'male'){
    $requestgt = 1;
} else if ($requestgt == 'female'){
    $requestgt = 2;
} else $requestgt = 0;

if (!isUserExist($ID)) { // nếu chưa tồn tại thì update lên sever
    $sql = "INSERT INTO `users` (`ID`, `trangthai`, `hangcho` ,`gioitinh` , `yeucau`, `ten`) VALUES ($ID, 0, 0, $gioitinh, $requestgt, $name)";
    //var_dump($sql);
    $info = mysqli_query($conn,$sql);
    //var_dump($ID);
    //var_dump($gioitinh);
    //var_dump($requestgt);
    //var_dump($info);
    //var_dump(mysqli_error($conn));
}
else {
    $sqlyeucau = "UPDATE `users` SET `yeucau` = $requestgt , `ten` = $name WHERE `ID` = $ID LIMIT 1";
    $infoyeucau = mysqli_query($conn,$sqlyeucau);;
//    var_dump($infoyeucau);
//    var_dump(mysqli_error($conn));
}

mysqli_close($conn);
?>