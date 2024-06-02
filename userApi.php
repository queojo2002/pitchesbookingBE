<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once('index.php');


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data == null) {
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }else if (!isset($data["email"]) || !isset($data["password"]) || !isset($data["fullname"])) {
        echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
        exit();
    }
    $email = $data["email"];
    $password = $data["password"];
    $fullname = $data["fullname"];
    $role = "user";
    if ($email == null || $password == null || $fullname == null) {
        echo (new ModelReturn(0, "Vui lòng nhập đầy đủ thông tin.", null))->toJson();
        exit();
    }
    $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$checkUser) {
        die("Error preparing query: " . $conn->error);
    }
    $checkUser->bind_param("s", $email);
    $checkUser->execute();
    $result = $checkUser->get_result();
    if ($result->num_rows > 0) {
        echo (new ModelReturn(0, "Email đã tồn tại.", null))->toJson();
        exit();
    }
    $hashPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertUser = $conn->prepare("INSERT INTO users (email, password, fullname, role) VALUES (?, ?, ?, ?)");
    if (!$insertUser) {
        die("Error preparing query: " . $conn->error);
    }
    $insertUser->bind_param("ssss", $email, $hashPassword, $fullname, $role);
    $insertUser->execute();
    echo (new ModelReturn(1, "Đăng ký thành công", null))->toJson();
}else {

    $model = new ModelAuthenication($secret_Key, $method_encode);
    if ($model->isLogin() == false) {
        echo $model->getMessage();
        exit();
    }


    
    $userFromAccessToken = json_decode($model->getMessage(),true)['data'];
    
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['cmd'])) {
            if ($_GET['cmd'] == 'getAdmin') {
                $admin = $conn->query("SELECT * FROM users WHERE role = 'admin'")->fetch_all(MYSQLI_ASSOC);
                echo (new ModelReturn(1, "Lấy thông tin Admin thành công", $admin))->toJson();
                exit();
            }else if ($_GET['cmd'] == 'getUser') {
                if ($userFromAccessToken['role'] != 'admin') {
                    echo (new ModelReturn(0, "Bạn không có quyền truy cập.", null))->toJson();
                    exit();
                }
                $user = $conn->query("SELECT * FROM users WHERE role = 'user'")->fetch_all(MYSQLI_ASSOC);
                echo (new ModelReturn(1, "Lấy thông tin User thành công", $user))->toJson();
                exit();
            }
        }else {
            $user = $conn->query("SELECT * FROM users WHERE email = '".$userFromAccessToken['email']."'")->fetch_assoc();
            echo (new ModelReturn(1, "Lấy thông tin User thành công", $user))->toJson();
        }

       

    }else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data == null) {
            echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
            exit();
        }else if (!isset($data["fullname"]) || !isset($data["phone"]) || !isset($data["address"]) || !isset($data["imageURL"])) {
            echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
            exit();
        }
        $fullname = $data["fullname"];
        $phone = $data["phone"];
        $address = $data["address"];
        $imageURL = $data['imageURL'];
        if ($fullname == null || $phone == null || $address == null || $imageURL == null) {
            echo (new ModelReturn(0, "Vui lòng nhập đầy đủ thông tin.", null))->toJson();
            exit();
        }
        $updateUser = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, address = ?, imageURL = ? WHERE email = ?");
        if (!$updateUser) {
            die("Error preparing query: " . $conn->error);
        }
        $updateUser->bind_param("sssss", $fullname, $phone, $address, $imageURL, $userFromAccessToken['email']);
        $updateUser->execute();
        echo (new ModelReturn(1, "Cập nhật thông tin User thành công", null))->toJson();
    }else {
        echo (new ModelReturn(0, "Phương thức không đúng.", null))->toJson();
    }
    
    
}









?>