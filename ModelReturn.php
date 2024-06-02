<?php 
    class ModelReturn {
        private $status;
        /*
            * 0: error
            * 1: success
            * 2: access token hết hạn
        */
        private $message;
        private $data;

        public function __construct($status, $message, $data) {
            $this->status = $status;
            $this->message = $message;
            $this->data = $data;
        }


        public function getStatus() {
            return $this->status;
        }

        public function getMessage() {
            return $this->message;
        }

        public function getData() {
            return $this->data;
        }

        public function setStatus($status) {
            $this->status = $status;
        }

        public function setMessage($message) {
            $this->message = $message;
        }

        public function setData($data) {
            $this->data = $data;
        }

        public function toArray() {
            return array(
                "status" => $this->status,
                "message" => $this->message,
                "data" => $this->data
            );
        }

        public function toJson() {
            return json_encode($this->toArray());
        }
        
    }
?>