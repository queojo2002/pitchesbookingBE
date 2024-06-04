<?php 
require_once('index.php');


if (!isset($_GET['pitchesBookingID'])){
    echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
    exit();
}


$idPitchesBooking = $_GET['pitchesBookingID'];
if ($idPitchesBooking == null) {
    echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
    exit();
}

$pitchesBookingCheck = $conn->prepare("SELECT * FROM pitchesbooking WHERE id = ?");
$pitchesBookingCheck->bind_param('i', $idPitchesBooking);
$pitchesBookingCheck->execute();
$pitchesBookingCheck = $pitchesBookingCheck->get_result()->fetch_assoc();

if (count($pitchesBookingCheck) == 0){
    echo (new ModelReturn(0, "Không có dữ liệu về sân này.", null))->toJson();
    exit();
}

if ($pitchesBookingCheck['status'] == 1) {
    echo (new ModelReturn(1, "Sân của bạn đã được thanh toán.", null))->toJson();
    exit();
}else if ($pitchesBookingCheck['status'] == 2) {
    echo (new ModelReturn(2, "Sân mà bạn đang đặt đã hết hạn hoặc đã bị hủy.", null))->toJson();
    exit();
}



