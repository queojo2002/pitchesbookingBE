<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once('index.php');

$model = new ModelAuthenication($secret_Key, $method_encode);
if ($model->isLogin() == false) {
    echo $model->getMessage();
    exit();
}

$userFromAccessToken = json_decode($model->getMessage(),true)['data'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    if ($data == null) {
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }

    $where_condition = "WHERE status = '0'";
    $types = "";
    $params = [];

    
    if (isset($data['type'])) {
        $types .= "s";
        $params[] = $data['type'];
        $where_condition .= " AND type = ?";
    }

    if (isset($data['name'])) {
        $types .= "s";
        $name = '%' . $data['name'] . '%';
        $where_condition .= " AND name LIKE ?";
        $params[] = $name;
    }
    
    if (isset($data['price'])){
        $price = $data['price'];
        $types .= "ii";
        $params[] = $price[0];
        $params[] = $price[1];
        $where_condition .= " AND price BETWEEN ? AND ?";
    }


    $pitches = $conn->prepare("SELECT * FROM pitches $where_condition");
    if (!$pitches) {
        echo (new ModelReturn(0, "Lỗi truy vấn", null))->toJson();
        exit();
    }
    if ($types) {
        $pitches->bind_param($types, ...$params);
    }

    $pitches->execute();

    $result = $pitches->get_result();
    $pitchesData = $result->fetch_all(MYSQLI_ASSOC);

    $pitches->close();
    $conn->close();
    
    echo (new ModelReturn(1, "Lấy thông tin sân thành công", $pitchesData))->toJson();



}else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $pitches = $conn->query("SELECT * FROM pitches WHERE status = '0'")->fetch_all(MYSQLI_ASSOC);
    echo (new ModelReturn(1, "Lấy thông tin sân thành công", $pitches))->toJson();


}else {
    echo (new ModelReturn(0, "Phương thức không đúng.", null))->toJson();
}





?>