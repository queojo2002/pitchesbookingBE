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



if ($_SERVER['REQUEST_METHOD'] == "GET"){
    if (isset($_GET['cmd'])){
        $cmd = $_GET['cmd'];
        if ($cmd == "getUsersAndCountBooking"){
            $users = $conn->prepare("SELECT users.*, COUNT(pitchesbooking.id) as countBooking 
                                    FROM users 
                                    LEFT JOIN pitchesbooking ON users.id = pitchesbooking.userID 
                                    WHERE pitchesbooking.status = 1 and users.id <> ".$model->getUserID()."
                                    GROUP BY users.id");
            if (!$users){
                echo (new ModelReturn(0, "Lỗi truy vấn: " . $conn->error, null))->toJson();
                exit();
            }
            $users->execute();
            $users = $users->get_result();
            $users = $users->fetch_all(MYSQLI_ASSOC);
            echo (new ModelReturn(1, "Thành công", $users))->toJson();
        }
    }
}

?>