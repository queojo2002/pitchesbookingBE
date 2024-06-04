<?php 
date_default_timezone_set('Asia/Ho_Chi_Minh');


// Config API VNPAY 
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$vnp_TmnCode = "6LY4D08E"; //Mã định danh merchant kết nối (Terminal Id)
$vnp_HashSecret = "L32MNN2JMUFDPMQZXE1IQB3QFKV75MI3"; //Secret key
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = "http://192.168.1.56/pitchesbookingBE/vnpay_return.php";
$vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
$apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";
//Config input format
//Expire
$startTime = date("YmdHis");
$expire = date('YmdHis',strtotime('+5 minutes',strtotime($startTime)));
// END 



$time = date('Y-m-d H:i:s');
$conn       = mysqli_connect('45.252.251.31', 'jrpfrdmk', 'iz14tKY9f0', 'jrpfrdmk_pitchesbooking') or die ('Connect DB Failed');
mysqli_query($conn,"SET NAMES utf8");

$secret_Key  = '68V0zWFrS72GbpPreidkQFLfj4v9m3Ti+DXc8OB0gcM=_dth'; // secret key của jwt
$method_encode = 'HS512'; // thuật toán mã hóa
$domainName = "localhost"; // domain name của bạn



?>