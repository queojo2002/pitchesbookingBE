<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once '../index.php';

$model = new ModelAuthenication($secret_Key, $method_encode);
if ($model->isLogin() == false) {
    echo $model->getMessage();
    exit();
}else if ($model->isAdmin() == false) {
    echo (new ModelReturn(0, "Bạn không có quyền truy cập", null))->toJson();
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $pitches = $conn->prepare("SELECT * FROM pitches WHERE id = ?");
        $pitches->bind_param("i", $id);
        $pitches->execute();
        $pitchesData = $pitches->get_result()->fetch_assoc();
        if ($pitchesData == null) {
            echo (new ModelReturn(0, "Không tìm thấy sân", null))->toJson();
            exit();
        }
        echo (new ModelReturn(1, "Lấy thông tin sân thành công", $pitchesData))->toJson();
        exit();
    }else {
        $pitches = $conn->query("SELECT * FROM pitches")->fetch_all(MYSQLI_ASSOC);
        echo (new ModelReturn(1, "Lấy thông tin sân thành công", $pitches))->toJson();
    }
    

}else if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    if ($data == null) {
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }else if (!isset($data['type']) && !isset($data['name']) && !isset($data['price']) && !isset($data['imageURL']) && !isset($data['status'])) {
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }
    $name = $data['name'];
    $type = $data['type'];
    $price = $data['price'];
    $imageURL = $data['imageURL'];
    $status = $data['status'];
    $timeUpdate = time();
    $timeMake = time();
    if ($name == "" || $type == "" || $price == "" || $imageURL == "" || $status == "") {
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }

    $insetPitches = $conn->prepare("INSERT INTO pitches (name, type, price, imageURL, status, timeUpdate, timeMake) VALUES (?,?,?,?,?,?,?)");
    if (!$insetPitches) {
        echo (new ModelReturn(0, "Lỗi truy vấn: ". $conn->error, null))->toJson();
        exit();
    }
    $insetPitches->bind_param("ssissii", $name, $type, $price, $imageURL, $status, $timeUpdate, $timeMake);
    $insetPitches->execute();
    if ($insetPitches->affected_rows > 0) {
        echo (new ModelReturn(1, "Thêm sân thành công", null))->toJson();
    }else {
        echo (new ModelReturn(0, "Thêm sân thất bại", null))->toJson();
    }

}else if ($_SERVER['REQUEST_METHOD'] == "PUT"){
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data == null) {
        echo (new ModelReturn(0, "Dữ liệu rỗng, không hợp lệ.", null))->toJson();
        exit();
    }else if (!isset($data['id'])){
        echo (new ModelReturn(0, "Vui lòng thêm ID Pitches.", null))->toJson();
        exit();
    }

    $pitches = $conn->prepare("SELECT * FROM pitches WHERE id = ?");
    $pitches->bind_param("i", $data['id']);
    $pitches->execute();
    $pitchesData = $pitches->get_result()->fetch_assoc();
    if ($pitchesData == null) {
        echo (new ModelReturn(0, "Không tìm thấy sân", null))->toJson();
        exit();
    }
    $pitches->close();


    $types = "";
    $params = [];
    $set_condition = "";
    if (isset($data['name'])) {
        $types .= "s";
        $set_condition .= "name = ?,";
        $params[] = $data['name'];
    }
    if (isset($data['type'])) {
        $types .= "s";
        $set_condition .= "type = ?,";
        $params[] = $data['type'];
    }
    if (isset($data['price'])) {
        $types .= "i";
        $set_condition .= "price = ?,";
        $params[] = $data['price'];
    }
    if (isset($data['imageURL'])) {
        $types .= "s";
        $set_condition .= "imageURL = ?,";
        $params[] = $data['imageURL'];
    }
    if (isset($data['status'])) {
        $types .= "s";
        $set_condition .= "status = ?,";
        $params[] = $data['status'];
    }
    $set_condition = rtrim($set_condition, ",");
    $set_condition .= ", timeUpdate = ". time();


    $updatePitches = $conn->prepare("UPDATE pitches SET $set_condition WHERE id = ?");
    if (!$updatePitches) {
        echo (new ModelReturn(0, "Lỗi truy vấn: ". $conn->error, null))->toJson();
        exit();
    }
    $params[] = $data['id'];
    $types .= "i";
    $updatePitches->bind_param($types, ...$params);
    $updatePitches->execute();
    if ($updatePitches->affected_rows > 0) {
        echo (new ModelReturn(1, "Cập nhật sân thành công", null))->toJson();
    }
    else {
        echo (new ModelReturn(0, "Cập nhật sân thất bại", null))->toJson();
    }
}



?>