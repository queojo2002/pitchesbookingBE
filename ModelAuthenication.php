<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once('ModelReturn.php');
class ModelAuthenication {
    private $isAdmin = false;
    private $isLogin = false;
    private $message = null;
    private $userID = null;

    public function __construct($secret_Key, $method_encode) {
        try {
            if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $this->message = (new ModelReturn(0, "Yêu cầu không hợp lệ.", null))->toJson();
            } else if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                $this->message =  (new ModelReturn(0, "Access Token không hợp lệ", null))->toJson();
            } else {
                $accessToken = $matches[1];
                if (!$accessToken) {
                    $this->message =  (new ModelReturn(0, "Access Token không hợp lệ", null))->toJson();
                } else {
                    $accessTokenDecode = JWT::decode($accessToken, new Key($secret_Key, $method_encode));
                    $userFromAccessToken = json_decode(json_encode($accessTokenDecode), true); 
                    if ($userFromAccessToken['type'] != 'access'){
                        $this->message = (new ModelReturn(0, "Access Token không hợp lệ", null))->toJson();
                    }else {
                        $this->message = (new ModelReturn(1, "Access Token hợp lệ", $userFromAccessToken))->toJson();
                        $this->isLogin = true;
                        $this->isAdmin = ($userFromAccessToken['role'] == "admin");
                        $this->userID = $userFromAccessToken['id'];
                    }
                }
            }
        } catch (LogicException $e) {
            $this->message = (new ModelReturn(0, "LogicException: ". $e->getMessage(), null))->toJson();
        } catch (UnexpectedValueException $e) {
            if ($e->getMessage() == "Expired token") {
                $this->message = (new ModelReturn(2, "Access Token hết hạn", null))->toJson();
            } else {
                $this->message = (new ModelReturn(0, "UnexpectedValueException: ". $e->getMessage(), null))->toJson();
            }
        } catch (Exception $e) {
            $this->message = (new ModelReturn(0, "Exception: ". $e->getMessage(), null))->toJson();
        }
    }

    public function getMessage() {
        return $this->message;
    }

    public function isAdmin() {
        return $this->isAdmin;
    }

    public function isLogin() {
        return $this->isLogin;
    }

    public function getUserID() {
        return $this->userID;
    }
}

?>
