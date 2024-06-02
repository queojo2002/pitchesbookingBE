<?php 
use Firebase\JWT\JWT;
require_once('index.php');
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents('php://input'), true);
if ($data == null) {
    echo (new ModelReturn(0, "Dữ liệu không hợp lệ", null))->toJson();
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo (new ModelReturn(0, "Phương thức không đúng.", null))->toJson();
    exit();
}else if ((isset($data['email']) && isset($data['password'])) == false) {
    echo (new ModelReturn(0, "Vui lòng nhập đầy đủ thông tin.", null))->toJson();
    exit();
}

$email = $data["email"];
$password = $data["password"];
if ($email == null || $password == null) {
    echo (new ModelReturn(0, "Vui lòng nhập đầy đủ thông tin.", null))->toJson();
    exit();
}

$checkLogin = $conn->prepare("SELECT * FROM users WHERE email = ?");
$checkLogin->bind_param("s", $email);
$checkLogin->execute();
$result = $checkLogin->get_result();

if ($result->num_rows == 0) {
    echo (new ModelReturn(0, "Email của bạn không tồn tại trong hệ thống.", null))->toJson();
    exit();
}

$user = $result->fetch_assoc();
if (!password_verify($password, $user["password"])) {
    echo (new ModelReturn(0, "Mật khẩu không chính xác.", null))->toJson();
    exit();
}   



// Tạo access token
$date = new DateTimeImmutable();
$expire_at = $date->modify('+60 minutes')->getTimestamp(); // thời gian tồn tại của token là 1 phút
$request_data = [
    'iat'  => $date->getTimestamp(),         // chỉ định thời gian JWT được tạo ra
    'iss'  => $domainName,                       // xác định thực thể đã phát hành JWT
    'nbf'  => $date->getTimestamp(),         // Token chỉ được chấp nhận sau thời gian này
    'exp'  => $expire_at,                           // thời gian hết hạn của token
    'email' => $user["email"],                     // thông tin user
    'id' => $user["id"],
    'role' => $user["role"],             // thông tin user
    'type' => 'access'  // Thêm loại token
];
$accessToken = JWT::encode($request_data, $secret_Key, $method_encode);

// Tạo refresh token
$refresh_expire_at = $date->modify('+7 days')->getTimestamp(); // thời gian tồn tại của refresh token là 7 ngày
$refresh_data = [
    'iat'  => $date->getTimestamp(),
    'iss'  => $domainName,
    'nbf'  => $date->getTimestamp(),
    'exp'  => $refresh_expire_at,
    'email' => $user["email"],
    'id' => $user["id"],
    'role' => $user["role"],            
    'type' => 'refresh'  // Thêm loại token
];
$refreshToken = JWT::encode($refresh_data, $secret_Key, $method_encode);



// Trả về access token và refresh token
$modelReturn = new ModelReturn(1, "Đăng nhập thành công", array(
    "accessToken" => $accessToken,
    "refreshToken" => $refreshToken,
    "expire_at" => $expire_at,
    "refresh_expire_at" => $refresh_expire_at
));

echo $modelReturn->toJson();
?>