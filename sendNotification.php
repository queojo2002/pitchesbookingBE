<?php 
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once("index.php");
$model = new ModelAuthenication($secret_Key, $method_encode);
if ($model->isLogin() == false) {
    echo $model->getMessage();
    exit();
}

$userFromAccessToken = json_decode($model->getMessage(),true)['data'];
$data = json_decode(file_get_contents('php://input'), true);
if ($data == null) {
    echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
    exit();
}else if (!isset($data["title"]) || !isset($data["body"]) || !isset($data["imageURL"]) || !isset($data["type"]) || !isset($data["reciverIDS"])){
    echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
    exit();

}



$title = $data["title"];
$body = $data["body"];
$imageURL = $data["imageURL"];
$type = $data["type"];
$reciverIDS = $data["reciverIDS"];

if ($title == "" || $body == "" || $imageURL == "" ||  $type == "" || $reciverIDS == "") {
  echo (new ModelReturn(0, "Dữ liệu không hợp lệ.", null))->toJson();
  exit();
}else if ($type != "userToAdmin" && $type != "adminToUser") {
  echo (new ModelReturn(0, "Loại thông báo không hợp lệ.", null))->toJson();
  exit();
}else if ($type == "userToAdmin" && $userFromAccessToken['role'] != "user") {
  echo (new ModelReturn(0, "Bạn không có quyền gửi thông báo này.1", null))->toJson();
  exit();
}else if ($type == "adminToUser" && $userFromAccessToken['role'] != "admin") {
  echo (new ModelReturn(0, "Bạn không có quyền gửi thông báo này.2", null))->toJson();
  exit();
}else if ($reciverIDS == $userFromAccessToken['id']) {
  echo (new ModelReturn(0, "Không thể gửi thông báo cho chính mình.", null))->toJson();
  exit();
}

if ($type == "userToAdmin") {
    $query = $conn->prepare("SELECT * FROM users WHERE role = 'admin'");  
    if (!$query) {
        die("Error preparing query: " . $conn->error);
    }
    $query->execute();
    $result_admin = $query->get_result()->fetch_all(MYSQLI_ASSOC);
    if ($result_admin == null) {
        echo (new ModelReturn(0, "Không có admin nào.", null))->toJson();
        exit();
    }
    foreach ($result_admin as $admin) {
        $tokenFCM = $admin['tokenFCM'];
        if ($tokenFCM == null) {
            continue;
        }
        sendNotification($title, $body, $imageURL, $admin['id'], $type, $tokenFCM);
    }
    echo (new ModelReturn(1, "Gửi thông báo thành công.", null))->toJson();
}else if ($type == "adminToUser") {
    $query = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$query) {
        die("Error preparing query: " . $conn->error);
    }
    $query->bind_param("i", $reciverIDS);
    $query->execute();
    $result_user = $query->get_result()->fetch_assoc();

    if ($result_user == null) {
        echo (new ModelReturn(0, "Người nhận không tồn tại.", null))->toJson();
        exit();
    }
    $tokenFCM = $result_user['tokenFCM'];
    if ($tokenFCM == null) {
        echo (new ModelReturn(0, "Người nhận chưa đăng nhập.", null))->toJson();
        exit();
    }   
    sendNotification($title, $body, $imageURL, $result_user['id'], $type, $tokenFCM);
    echo (new ModelReturn(1, "Gửi thông báo thành công.", null))->toJson();
}









function sendNotification($title, $body, $imageURL, $nguoinhan, $type, $tokenFCM){
  $credential = new ServiceAccountCredentials(
      "https://www.googleapis.com/auth/firebase.messaging",
      json_decode(file_get_contents("pvKey.json"), true)
  );

  $token = $credential->fetchAuthToken(HttpHandlerFactory::build());

  $ch = curl_init("https://fcm.googleapis.com/v1/projects/pitchbooking-b7a05/messages:send");

  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: Bearer '.$token['access_token']
  ]);

  $payload = [
      "message" => [
          "token" => $tokenFCM,
          "notification" => [
              "title" => $title,
              "body" => $body,
              "image" => $imageURL
          ],
          "webpush" => [
              "fcm_options" => [
                  "link" => "https://google.com"
              ]
          ],
          "data" => [
              "reciverIDS" => (string)$nguoinhan,
              "type" => (string)$type
          ]
      ]
  ];

  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

  $response = curl_exec($ch);

  if ($response === false) {
      $error = curl_error($ch);
      echo "Curl error: " . $error;
  } else {
      echo $response;
  }

  curl_close($ch);
}

?>