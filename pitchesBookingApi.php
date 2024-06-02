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
    }else if (!isset($data["timeStart"]) || !isset($data["timeEnd"]) || !isset($data["pitchesID"])) {
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }

    $timeStart = $data["timeStart"];
    $timeEnd = $data["timeEnd"];
    $pitchesID = $data["pitchesID"];
    $userID = $userFromAccessToken['id'];
    $timeCreate = time();

    if ($timeStart == null || $timeEnd == null || $pitchesID == null) {
        echo (new ModelReturn(0, "Vui lòng nhập đầy đủ thông tin.", null))->toJson();
        exit();
    }
    if ($timeStart < time() || $timeEnd < time()) {
        echo (new ModelReturn(0, "Thời gian không hợp lệ.", null))->toJson();
        exit();
    }else if ($timeEnd - $timeStart < 3600) {
        echo (new ModelReturn(0, "Thời gian đặt sân phải lớn hơn 1 tiếng.", null))->toJson();
        exit();
    }


    $getPitches = $conn->prepare("SELECT * FROM pitches WHERE id = ? AND status = '0'");
    $getPitches->bind_param('i', $pitchesID);
    $getPitches->execute();
    $getPitches = $getPitches->get_result()->fetch_assoc();
    if (count($getPitches) == 0) {
        echo (new ModelReturn(0, "Sân không tồn tại hoặc đã bị khóa.", null))->toJson();
        exit();
    }
    $amount = ($timeEnd - $timeStart) / 3600 * $getPitches['price'];


    $timeStartNew = date('Y-m-d H:i:s', $timeStart);
    $timeEndNew = date('Y-m-d H:i:s', $timeEnd);

    $getPitchesBooking = $conn->prepare("SELECT * FROM pitchesbooking WHERE pitchesID = ?");
    $getPitchesBooking->bind_param('s', $pitchesID);
    $getPitchesBooking->execute();
    $getPitchesBooking = $getPitchesBooking->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($getPitchesBooking as $key => $value) {
        $timeStartDB = date('Y-m-d H:i:s', $value['timeStart']);
        $timeEndDB = date('Y-m-d H:i:s', $value['timeEnd']);


        
        /*

            
        
        timeStartNew = 6h30
        timeEndNew = 7h30

        timeStartDB = 7h30
        timeEndDB = 8h30


        timeStartNew = 8h29
        timeEndNew = 9h30


        
        * Kiểm tra xem khung giờ đặt sân mới có trùng với khung giờ đặt sân cũ không
        * 1. timeStartDB < timeEndNew
        * 2. timeEndDB > timeStartNew



        */



        if ($timeStartDB < $timeEndNew && $timeEndDB > $timeStartNew) {
            echo (new ModelReturn(0, "Khung giờ này của sân này đã được đặt." , null))->toJson();
            exit();
        }
    }

    $insertPitchesBooking = $conn->prepare("INSERT INTO pitchesbooking (userID, pitchesID, amount, status, timeStart, timeEnd, timeCreate) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $status = 0;
    $insertPitchesBooking->bind_param('iiiiiii', $userID, $pitchesID, $amount, $status, $timeStart, $timeEnd, $timeCreate);
    $insertPitchesBooking->execute();
    echo (new ModelReturn(1, "Đặt sân thành công.", null))->toJson();

    







}else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['cmd'] == "getPitchesBookingByUserID") {
        $getPitchesBooking = $conn->prepare("SELECT pitchesbooking.*, users.fullname, users.email, pitches.name, pitches.imageURL FROM pitchesbooking 
                                                    INNER JOIN users ON users.id = userID 
                                                    INNER JOIN pitches ON pitches.id = pitchesID 
                                                    WHERE userID = ?");
        $getPitchesBooking->bind_param('i', $userFromAccessToken['id']);
        $getPitchesBooking->execute();
        $getPitchesBooking = $getPitchesBooking->get_result()->fetch_all(MYSQLI_ASSOC);
        echo (new ModelReturn(1, "Lấy dữ liệu thành công.", $getPitchesBooking))->toJson();
    }else if ($_GET['cmd'] == "getPitchesBookingByID") {
        if (!isset($_GET['id'])) {
            echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
            exit();
        }
        $id = $_GET['id'];
        $getPitchesBooking = $conn->prepare("SELECT pitchesbooking.*, users.fullname, users.email FROM pitchesbooking 
                                                    INNER JOIN users ON users.id = userID 
                                                    INNER JOIN pitches ON pitches.id = pitchesID 
                                                    WHERE pitchesID = ?");
        $getPitchesBooking->bind_param('i', $id);
        $getPitchesBooking->execute();
        $getPitchesBooking = $getPitchesBooking->get_result()->fetch_all(MYSQLI_ASSOC);
        if (count($getPitchesBooking) == 0) {
            echo (new ModelReturn(0, "Không có dữ liệu.", null))->toJson();
            exit();
        }
        echo (new ModelReturn(1, "Lấy dữ liệu thành công.", $getPitchesBooking))->toJson();
    }
    
}


?>