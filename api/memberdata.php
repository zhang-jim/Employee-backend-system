<?php
include("mysqlconn.php");
session_start();
class UserData extends SqlConn
{
    public function main()
    {
        $this->sql = "SELECT `name`,`account`,`password`,`category`,`class`,`email`,`Annual_leave`,`Remaining_annual_leave` FROM `memberdata` WHERE `account` = ?";
        $stmt = $this->_link->prepare($this->sql);
        $stmt->bind_param('s', $_SESSION["account"]);
        $stmt->execute();
        $this->_result = $stmt->get_result();
        while ($row = $this->_result->fetch_assoc()) {
            $annual = $row["Annual_leave"] - $row["Remaining_annual_leave"];
            $this->_arr = array(
                'name' => $row["name"],
                'category' => $row["category"],
                'period' => $row["class"],
                'account' => $row["account"],
                'email' => $row["email"],
                'annualLeave' => $annual
            );
        }
    }
    public function member($num, $category, $finish = 15)
    {
        if ($_SESSION['rule'] > 1 && $_SESSION['rule'] !== 'admin') {
            $this->_arr = array('status' => 'false');
            return;
        }
        $start = ($num - 1) * 15;
        if ($category === 'all') {
            $this->sql = "SELECT `name`, `category`, `EntryDate` FROM `memberdata` ORDER BY `EntryDate` ASC LIMIT ?, ?";
            $stmt = $this->_link->prepare($this->sql);
            $stmt->bind_param('ii', $start, $finish);
        } else {
            $this->sql = "SELECT `name`, `category`, `EntryDate` FROM `memberdata` WHERE `category` LIKE CONCAT( ?, '%') ORDER BY `EntryDate` ASC LIMIT ?, ?";
            $stmt = $this->_link->prepare($this->sql);
            $stmt->bind_param('sii', $category, $start, $finish);
        }
        $stmt->execute();
        $this->_result = $stmt->get_result();
        while ($row = $this->_result->fetch_assoc()) {
            $this->_arr[] = array(
                'category' => $row["category"],
                'name' => $row["name"],
                'EntryDate' => $row["EntryDate"]
            );
        }
    }

    protected function createContinuationNum()
    {
        $i = 1;
        $id = 0;
        $this->sql = "SELECT * FROM `memberdata` ORDER BY `id` ASC";
        $this->_result = $this->_link->query($this->sql);
        while ($row = mysqli_fetch_assoc($this->_result)) {
            $lastarray[] = $row["id"];
        }
        if (count($lastarray) == 0) {
            $id += 1;
        }
        while ($i <= count($lastarray)) {
            if ($i != $lastarray[$i - 1]) {
                $id = $i;
                break;
            } else {
                $i += 1;
                $id = $i;
            }
        }
        return $id;
    }
    public function register($registerArray)
    {
        $registerArray["gender"] = "male";
        foreach ($registerArray as $key => $val) {
            if (empty($val)) {
                if ($key == 'email') {
                    continue;
                } else {
                    return $this->_arr = array('status' => 'false', 'message' => '尚有資料未填寫!');
                }
            }
        }
        $insert_id = $this->createContinuationNum();
        $time_array = ["09:00 ~ 18:00" => "morning", "13:00 ~ 22:00" => "afternoon", "17:00 ~ 02:00" => "night"];
        foreach ($time_array as $key => $val) {
            if ($registerArray["period"] === $key) {
                $period = $val;
                break;
            }
        }
        // 判斷帳號是否重複
        $stmt = $this->_link->prepare("SELECT * FROM `memberdata` WHERE `name` = ? AND `account` = ?");
        $stmt->bind_param('ss', $registerArray["realName"], $registerArray["account"]);
        $stmt->execute();
        $this->_result = $stmt->get_result();
        if ($this->_result->num_rows === 0) {
            $this->sql = "INSERT INTO `memberdata` (`id`, `name`, `account`, `password`, `gender`, `category`, `class`, `email`, `EntryDate`)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->_link->prepare($this->sql);

            $stmt->bind_param('isssssss', $insert_id, $registerArray["realName"], $registerArray["account"], $registerArray["password"],$registerArray["gender"], $registerArray["category"], $period, $registerArray["email"]);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $this->_arr = array('status' => 'true', 'message' => '註冊成功!');
            } else {
                $this->_arr = array('status' => 'false', 'message' => '註冊失敗!');
            }
        } else {
            $this->_arr = array('status' => 'false', 'message' => '帳號已存在!');
        }
    }
    public function rule()
    {
        $this->_arr = array(
            'rule' => $_SESSION["rule"],
            'name' => $_SESSION["user"],
            'category' => $_SESSION["category"]
        );
    }
    public function editMemberData($array)
    {
        if (!empty($array["realName"])) {
            $time_array = ["09:00 ~ 18:00" => "morning", "13:00 ~ 22:00" => "afternoon", "17:00 ~ 02:00" => "night"];
            foreach ($time_array as $key => $value) {
                if ($array["period"] === $key) {
                    $period = $value;
                    break;
                }
            }
            $this->sql = "UPDATE `memberdata` SET `name` = ?, `class` = ?, `email` = ? WHERE `account` = ?";
            $stmt = $this->_link->prepare($this->sql);
            $stmt->bind_param('ssss', $array["realName"], $period, $array["email"], $array["account"]);
            $stmt->execute();
        }
        if ($stmt->affected_rows === 0) {
            $this->_arr = array('status' => 'false');
        } else {
            $this->_arr = array('status' => 'true');
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_data = new UserData;
    switch ($_GET['type']) {
        case 'member_data':
            $user_data->main();
            break;
        case 'rule':
            $user_data->rule();
            break;
        case 'member_data_show':
            $user_data->member($_GET['page'], $_GET["category"]);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input'); //取得前端資料 
    $array = json_decode($data, true); //對json格式的字串 進行解碼 回傳array格式
    $user_data = new UserData;
    switch ($array['type']) {
        case 'register':
            $user_data->register($array);
            break;
        case 'edit':
            $user_data->editMemberData($array);
            break;
    }
}
