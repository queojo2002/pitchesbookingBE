<?php 
date_default_timezone_set('Asia/Ho_Chi_Minh');


$time = date('Y-m-d H:i:s');
$conn       = mysqli_connect('45.252.251.31', 'jrpfrdmk', 'iz14tKY9f0', 'jrpfrdmk_pitchesbooking') or die ('Connect DB Failed');
mysqli_query($conn,"SET NAMES utf8");

$secret_Key  = '68V0zWFrS72GbpPreidkQFLfj4v9m3Ti+DXc8OB0gcM=_dth'; // secret key của jwt
$method_encode = 'HS512'; // thuật toán mã hóa
$domainName = "localhost"; // domain name của bạn



?>