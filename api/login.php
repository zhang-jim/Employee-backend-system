<?php
include("mysqlconn.php");
session_start();
class Authentication extends SqlConn
{
    public $account;
    public $password;
    protected $id;
    public function login()
    {
        $this->sql = "SELECT `name`,`account`,`rule`,`category`,`gender` FROM `memberdata` WHERE `account` = ? AND `password` = ?";
        $stmt = $this->_link->prepare($this->sql);
        $stmt->bind_param('ss', $this->account, $this->password);
        $stmt->execute();
        $this->_result = $stmt->get_result();
        if ($this->_result->num_rows === 1) {
            $row = $this->_result->fetch_assoc();
            $_SESSION["rule"] = $row["rule"];
            $_SESSION['user'] = $row["name"];
            $_SESSION['category'] = $row["category"];
            $_SESSION['account'] = $row["account"];
            $_SESSION['gender'] = $row["gender"];
            $this->_arr = array('status' => 'true');
        } else {
            $this->_arr = array('status' => 'false');
        }
    }

    public function loginStatus($status)
    {
        if (!isset($_SESSION['user']) || $status == 'loginout') {
            session_unset();
            $this->_arr = array('status' => 'false', 'message' => '已登出');
        } else {
            $this->_arr = array('status' => 'true');
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $authentication = new Authentication;
    $authentication->loginStatus($_GET['type']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input'); //取得前端資料 
    $array = json_decode($data, true); //對json格式的字串 進行解碼 回傳array格式
    $authentication = new Authentication;
    if ($array['type'] === 'login') {
        $authentication->account = $array['user'];
        $authentication->password = $array['password'];
        $authentication->login();
    }
}
