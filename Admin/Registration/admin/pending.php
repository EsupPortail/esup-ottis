<?php
include("../config.php");

$query = "SELECT * FROM `users` ORDER BY id DESC";
$result = mysqli_query($conn,$query) or die(mysql_error());

$nbpending=0;
while ($user=mysqli_fetch_assoc($result)) {
    if ($user["pending"]!="NO" && $user["active"]!="YES") $nbpending++;

}

$query = "update users set pending='NO';";
$result = mysqli_query($conn,$query) or die(mysql_error());

echo $nbpending;


?>