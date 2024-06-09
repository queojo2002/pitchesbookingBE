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
    
    $where_condition = "WHERE 1";
    $types = "";
    $params = [];

    if (isset($_GET['pitchesID'])) {
        $pitchesID = $_GET['pitchesID'];
        if ($pitchesID != 'null' && $pitchesID != null && $pitchesID != "undefined" && $pitchesID != "" && $pitchesID != 0 && $pitchesID != "0"){
            $where_condition .= " AND pitchesbooking.pitchesID = ?";
            $types .= "i";
            $params[] = $pitchesID;
        }
        
    }
    if (isset($_GET['userID'])) {
        $userID = $_GET['userID'];
        $where_condition .= " AND pitchesbooking.userID = ?";
        $types .= "i";
        $params[] = $userID;
    }
    if (isset($_GET['statusBooking'])) {
        $statusBooking = $_GET['statusBooking'];
        $where_condition .= " AND pitchesbooking.status = ?";
        $types .= "i";
        $params[] = $statusBooking;
    }
    if (isset($_GET['fromDate']) && isset($_GET['toDate'])) {
        $fromDate = $_GET['fromDate'];
        $toDate = $_GET['toDate'];
        $where_condition .= " AND pitchesbooking.timeStart >= ? AND pitchesbooking.timeStart <= ?";
        $types .= "ii";
        $params[] = $fromDate;
        $params[] = $toDate;
    }
    if (isset($_GET['searchText'])){
        $searchText = $_GET['searchText'];
        $where_condition .= " AND (pitches.name LIKE ? OR users.fullname LIKE ? OR pitchesbooking.BankTranNo LIKE ? OR pitchesbooking.TransactionNo LIKE ?)";
        $types .= "ssss";
        $params[] = '%' . $searchText . '%';
        $params[] = '%' . $searchText . '%';
        $params[] = '%' . $searchText . '%';
        $params[] = '%' . $searchText . '%';
    }

    $pitchesBookingFilter = $conn->prepare("SELECT pitchesbooking.*, users.id as 'userID', users.fullname, users.phone, users.email, pitches.name, pitches.type, pitches.imageURL, pitches.status FROM pitchesbooking
                                LEFT JOIN pitches ON pitchesbooking.pitchesID = pitches.id
                                LEFT JOIN users ON pitchesbooking.userID = users.id
                                $where_condition
                                Order by pitchesbooking.timeCreate DESC
                                "
                                );
    if (!$pitchesBookingFilter) {
        echo (new ModelReturn(0, "Lỗi truy vấn: " . $conn->error, null))->toJson();
        exit();
    }
    if ($types) {
        $pitchesBookingFilter->bind_param($types, ...$params);
    }
    $pitchesBookingFilter->execute();
    $pitchesData = $pitchesBookingFilter->get_result()->fetch_all(MYSQLI_ASSOC);
    if ($pitchesData == null){
        echo (new ModelReturn(0, "Không tìm thấy dữ liệu: " . $where_condition, null))->toJson();
        exit();
    }else {
        echo (new ModelReturn(1, "Lấy thông tin đặt sân thành công", $pitchesData))->toJson();
    }

}else if ($_SERVER['REQUEST_METHOD'] == 'PUT'){
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data == null) {
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }
    if (!isset($data['id']) || !isset($data['status'])){
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }

    $id = $data['id'];
    $status = $data['status'];

    $pitchesBooking = $conn->prepare("UPDATE pitchesbooking SET status = ? WHERE id = ?");
    if (!$pitchesBooking) {
        echo (new ModelReturn(0, "Lỗi truy vấn: " . $conn->error, null))->toJson();
        exit();
    }
    $pitchesBooking->bind_param("ii", $status, $id);
    $pitchesBooking->execute();
    if ($pitchesBooking->affected_rows > 0){
        echo (new ModelReturn(1, "Cập nhật trạng thái đặt sân thành công", null))->toJson();
    }else {
        echo (new ModelReturn(0, "Cập nhật trạng thái đặt sân thất bại: " . $conn->error, null))->toJson();
    }






}






?>