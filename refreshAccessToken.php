<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once('index.php');



$data = json_decode(file_get_contents('php://input'), true);    
if ($data == null) {
    echo (new ModelReturn(0, "Dữ liệu không hợp lệ", null))->toJson();
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo (new ModelReturn(0, "Phương thức không đúng.", null))->toJson();
    exit();
} else if (!isset($data['refreshToken'])) {
    echo (new ModelReturn(0, "Vui lòng cung cấp refresh token.", null))->toJson();
    exit();
}
$refreshToken = $data['refreshToken'];

try {
    // Giải mã refresh token
    $decoded = JWT::decode($refreshToken, new Key($secret_Key, $method_encode));

    // Kiểm tra loại token
    if ($decoded->type !== 'refresh') {
        echo (new ModelReturn(0, "Token không hợp lệ.", null))->toJson();
        exit();
    }

    $email = $decoded->email;

    $checkToken = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $checkToken->bind_param("s", $email);
    $checkToken->execute();
    $result = $checkToken->get_result();

    if ($result->num_rows == 0) {
        echo (new ModelReturn(0, "Refresh token không hợp lệ.", null))->toJson();
        exit();
    }

    $user = $result->fetch_assoc();

    // Tạo access token mới
    $date = new DateTimeImmutable();
    $expire_at = $date->modify('+60 minutes')->getTimestamp(); // thời gian tồn tại của token là 1 phút
    $request_data = [
        'iat'  => $date->getTimestamp(),
        'iss'  => $domainName,
        'nbf'  => $date->getTimestamp(),
        'exp'  => $expire_at,
        'email' => $user["email"],                     
        'id' => $user["id"],
        'role' => $user["role"],            
        'type' => 'access'  // Thêm loại token
    ];
    $accessToken = JWT::encode($request_data, $secret_Key, $method_encode);

    // Tạo refresh token mới
    $refresh_expire_at = $date->modify('+7 days')->getTimestamp(); // thời gian tồn tại của refresh token là 7 ngày
    $refresh_data = [
        'iat'  => $date->getTimestamp(),
        'iss'  => $domainName,
        'nbf'  => $date->getTimestamp(),
        'exp'  => $refresh_expire_at,
        'email' => $user["email"],                    
        'id' => $user["id"],
        'role' => $user["role"],             
        'type' => 'refresh'
    ];
    $newRefreshToken = JWT::encode($refresh_data, $secret_Key, $method_encode);


    // Trả về access token và refresh token mới
    $modelReturn = new ModelReturn(1, "Access token mới đã được tạo.", array(
        "accessToken" => $accessToken,
        "refreshToken" => $newRefreshToken,
        "expire_at" => $expire_at,
        "refresh_expire_at" => $refresh_expire_at
    ));

    echo $modelReturn->toJson();

} catch (Exception $e) {
    echo (new ModelReturn(0, "Refresh token không hợp lệ hoặc đã hết hạn.", null))->toJson();
    exit();
}
?>