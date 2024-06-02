<?php 
require_once("index.php");
$getInformation = $conn->query("SELECT * FROM information");
$result = $getInformation->fetch_assoc();
$result = json_encode($result);
echo $result;
?>