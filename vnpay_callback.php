<?php 
require_once('index.php');


$pitchesBooking = $conn->prepare("SELECT * FROM pitchesbooking WHERE status = 0");
$pitchesBooking->execute();
$pitchesBooking = $pitchesBooking->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($pitchesBooking as $key => $value) {
    $timeCreate = date('Y-m-d H:i:s', $value['timeCreate']);
    $timeExpire = date('Y-m-d H:i:s', strtotime('+5 minutes 10 seconds', strtotime($timeCreate)));

    $now = date('Y-m-d H:i:s');
    if ($now > $timeExpire) {
        $updatePitchesBooking = $conn->prepare("UPDATE pitchesbooking SET status = 2 WHERE id = ?");
        $updatePitchesBooking->bind_param('i', $value['id']);
        $updatePitchesBooking->execute();
    }
}


?>