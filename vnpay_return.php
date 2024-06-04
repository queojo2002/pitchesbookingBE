<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>VNPAY RESPONSE</title>
    </head>
    <body>
        <?php
        require_once('index.php');
        if (!isset($_GET['vnp_SecureHash'])) {
            echo "No response from vnpay";
            exit();
        }else if (!isset($_GET['vnp_TxnRef'])){
            echo "No response from vnpay";
            exit();
        }
        $idPitchesBooking = $_GET['vnp_TxnRef'];
        $pitchesBookingCheck = $conn->prepare("SELECT * FROM pitchesbooking WHERE id = ? and status = 0");
        $pitchesBookingCheck->bind_param('i', $idPitchesBooking);
        $pitchesBookingCheck->execute();
        $pitchesBookingCheck = $pitchesBookingCheck->get_result()->fetch_assoc();
        if ($pitchesBookingCheck == null) {
            echo "No response from vnpay";
            exit();
        }





        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        





        ?>
        <!--Begin display -->
        <div class="container">
            <div class="header clearfix">
                <h3 class="text-muted">VNPAY RESPONSE</h3>
            </div>
            <div class="table-responsive">
                <div class="form-group">
                    <label >Mã đơn hàng:</label>

                    <label><?php echo $_GET['vnp_TxnRef'] ?></label>
                </div>    
                <div class="form-group">

                    <label >Số tiền:</label>
                    <label><?php echo $_GET['vnp_Amount'] ?></label>
                </div>  
                <div class="form-group">
                    <label >Nội dung thanh toán:</label>
                    <label><?php echo $_GET['vnp_OrderInfo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã phản hồi (vnp_ResponseCode):</label>
                    <label><?php echo $_GET['vnp_ResponseCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã GD Tại VNPAY:</label>
                    <label><?php echo $_GET['vnp_TransactionNo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã Ngân hàng:</label>
                    <label><?php echo $_GET['vnp_BankCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Thời gian thanh toán:</label>
                    <label><?php echo $_GET['vnp_PayDate'] ?></label>
                </div> 

                <?php 
                    
                ?>

                <div class="form-group">
                    <label >Kết quả:</label>
                    <label>
                        <?php
                        if ($secureHash == $vnp_SecureHash) {
                            if ($_GET['vnp_ResponseCode'] == '00') {

                                
                                

                                $updatePitchesBooking = $conn->prepare("UPDATE pitchesbooking SET status = 1, BankTranNo = ?, TransactionNo = ? WHERE id = ?");
                                $TransactionNo = $_GET['vnp_TransactionNo'];
                                $BankTranNo = null;
                                if (isset($_GET['vnp_BankTranNo'])){
                                    $BankTranNo = $_GET['vnp_BankTranNo'];
                                }
                                $updatePitchesBooking->bind_param('ssi', $BankTranNo, $TransactionNo, $idPitchesBooking);
                                $updatePitchesBooking->execute();
                                if ($updatePitchesBooking->affected_rows == 0) {
                                    echo "<span style='color:red'>Co loi trong luc thanh toan, vui long lien he admin. Chờ 5s - 10s để hệ thống đưa bạn trở về.</span>";
                                } else {
                                    echo "<span style='color:blue'>GD Thanh cong, vui long cho giay lat...... Chờ 5s - 10s để hệ thống đưa bạn trở về.</span>";
                                }
                            } else {
                                $updatePitchesBooking = $conn->prepare("UPDATE pitchesbooking SET status = 2 WHERE id = ?");
                                $updatePitchesBooking->bind_param('i', $idPitchesBooking);
                                $updatePitchesBooking->execute();
                                if ($updatePitchesBooking->affected_rows == 0) {
                                    echo "<span style='color:red'>GD Khong thanh cong. Chờ 5s - 10s để hệ thống đưa bạn trở về.</span>";
                                } else {
                                    echo "<span style='color:red'>Co loi trong luc thanh toan. Chờ 5s - 10s để hệ thống đưa bạn trở về.</span>";
                                }
                            }
                        } else {
                            echo "<span style='color:red'>Chu ky khong hop le. Chờ 5s - 10s để hệ thống đưa bạn trở về.</span>";
                        }
                        ?>

                    </label>
                </div> 
            </div>
            <p>
                &nbsp;
            </p>
            <footer class="footer">
                   <p>&copy; VNPAY <?php echo date('Y')?></p>
            </footer>
        </div>  
    </body>
</html>
