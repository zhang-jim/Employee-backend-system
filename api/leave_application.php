<?php
include("mysqlconn.php");
session_start();
class LeaveApplication extends SqlConn
{
    protected $_start_date;
    protected $_finish_date;
    protected $_leave_category;
    protected $_reason;
    public function applyLeave($data)
    {
        if (empty($data["reason"]) || empty($data['start_date_time']) || empty($data['finish_date_time'])) {
            $this->_arr = array('status' => 'false', 'message' => '資料未填寫完畢');
            return;
        }
        $now_date = date("Y-m-d");
        $this->_start_date = new DateTime($data['start_date_time']);
        $this->_finish_date = new DateTime($data['finish_date_time']);
        $interval = $this->_finish_date->diff($this->_start_date); // time
        $this->_leave_category = $data["category"];
        $this->_reason = preg_replace('/\s+/', ' ', $data["reason"]);

        if ($this->_finish_date <= $this->_start_date || $interval->h <= 0) {
            $this->_arr = array('status' => 'false', 'message' => '時間有誤!請正常選擇');
            return;
        }
        $start_date = $this->_start_date->format("Y-m-d H:i:s");
        $finish_date = $this->_finish_date->format("Y-m-d H:i:s");

        if ($interval->h > 8) {
            $hours = 8;
        } else if ($interval->h > 4) {
            $hours = $interval->h - 1;
        } else {
            $hours = $interval->h;
        }
        $hours = $hours / 8 + $interval->d;

        $user = $_SESSION['user'];
        $gender = $_SESSION['gender'];
        $stmt = $this->_link->prepare("SELECT `Annual_leave`,`Remaining_annual_leave` FROM `memberdata` WHERE `name` = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $this->_result = $stmt->get_result();
        $row = $this->_result->fetch_assoc();
        if ($row) {
            $annual = (int)$row["Annual_leave"] - ((int)$row["Remaining_annual_leave"] + $hours);
        }

        switch ($this->_leave_category) {
            case '事假':
                if ($hours >= 0.5) {
                    $stmt = $this->_link->prepare("INSERT INTO `all_leave` (`Name`,`Start_date`,`Finish_date`,`Date_of_filing`,`Leave_category`,`Reason`,`Days`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('ssssssd', $user, $start_date, $finish_date, $now_date, $this->_leave_category, $this->_reason, $hours);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) {
                        $this->_arr = array('status' => 'true', 'message' => '提交成功!');
                    }
                } else {
                    $this->_arr = array('status' => 'false', 'message' => '請假時間過短!');
                }
                break;
            case '生理假':
                $this_month = substr($finish_date, 0, 7);
                $stmt = $this->_link->prepare("SELECT `Menstruation_days` FROM `menstruation_leave` WHERE `name` = ? AND `date` LIKE CONCAT(?)");
                $stmt->bind_param("ss", $user, $this_month);
                $stmt->execute();
                $this->_result = $stmt->get_result();
                $num_rows = $this->_result->num_rows;

                if ($num_rows > 0) {
                    $row = $this->_result->fetch_assoc();
                    $days = $row['Menstruation_days'];
                } else {
                    $stmt = $this->_link->prepare("INSERT INTO `menstruation_leave` (`name`,`date`,`Menstruation_days`) VALUES (?, ?, 0)");
                    $stmt->bind_param('ss', $user, $this_month);
                    $stmt->execute();
                    $days = 0;
                }

                if ($gender === "female") {
                    if ($days < 1) {
                        if ($hours == 0.5 || $hours == 1) {
                            $stmt = $this->_link->prepare("INSERT INTO `all_leave` (`Name`,`Start_date`,`Finish_date`,`Date_of_filing`,`Leave_category`,`Reason`,`Days`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param('ssssssd', $user, $start_date, $finish_date, $now_date, $this->_leave_category, $this->_reason, $hours);
                            $stmt->execute();
                            if ($stmt->affected_rows > 0) {
                                $this->_arr = array('status' => 'true', 'message' => '提交成功!');
                            }
                        } else {
                            $this->_arr = array('status' => 'false', 'message' => '時間有誤!請正常選擇');
                            return;
                        }
                    } else {
                        $this->_arr = array('status' => 'false', 'message' => '本月生理假已用完');
                        return;
                    }
                } else {
                    $this->_arr = array('status' => 'false', 'message' => '男生請生理假??');
                    return;
                }
                break;
            case '病假':
                $sick_img = $data["img"];
                if ($hours >= 0.5) {
                    $stmt = $this->_link->prepare("INSERT INTO `all_leave` (`Name`,`Start_date`,`Finish_date`,`Date_of_filing`,`Leave_category`,`Reason`,`Sick_img`,`Days`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('sssssssi', $user, $start_date, $finish_date, $now_date, $this->_leave_category, $this->_reason, $sick_img, $hours);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) {
                        $this->_arr = array('status' => 'true', 'message' => '提交成功!');
                    }
                } else {
                    $this->_arr = array('status' => 'false', 'message' => '請假時間過短!');
                }
                break;
            case '特休':
                if ($annual > 0) {
                    $two_weeks_later = new DateTime('+2 weeks');
                    if ($this->_start_date->format('Y-m-d') >= $two_weeks_later->format('Y-m-d')) {
                        if ($interval->h == 9) {
                            $stmt = $this->_link->prepare("INSERT INTO `all_leave` (`Name`,`Start_date`,`Finish_date`,`Date_of_filing`,`Leave_category`,`Reason`,`Days`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param('ssssssi', $user, $start_date, $finish_date, $now_date, $this->_leave_category, $this->_reason, $hours);
                            $stmt->execute();
                            if ($stmt->affected_rows > 0) {
                                $this->_arr = array('status' => 'true', 'message' => '提交成功!');
                            }
                        } else {
                            $this->_arr = array('status' => 'false', 'message' => '請假時間過短!');
                        }
                    } else {
                        $this->_arr = array('status' => 'false', 'message' => '提交失敗!特休必須提前兩周申請!');
                    }
                } else {
                    $this->_arr = array('status' => 'false', 'message' => '無法申請，請確認可用特休天數!');
                }
                break;
        }
    }
    public function reviewingLeave($array)
    {
        $stmt = $this->_link->prepare("SELECT `Name`,`Start_date`,`Leave_category`,`Annual_leave`,`Remaining_annual_leave`,`Days` FROM `all_leave` INNER JOIN `memberdata` USING(`Name`) WHERE `Leave_id` = ?");
        $stmt->bind_param("i", $array["reviewing_id"]);
        $stmt->execute();
        $this->_result = $stmt->get_result(); //取得結果
        if ($this->_result->num_rows === 0) {
            $this->_arr = array('status' => 'false', 'message' => '失敗');
            return;
        }
        $row = $this->_result->fetch_assoc();
        $name = $row["Name"];
        $this_month = substr($row["Start_date"], 0, 7);
        $annual_leave = $row["Annual_leave"];
        $remaining_annual_leave = $row["Remaining_annual_leave"];
        $leave_category = $row["Leave_category"];
        $leave_days = $row["Days"];

        if ($array['approval'] === 'approved') {
            if ($leave_category === "特休") {
                $remaining_annual_leave += $leave_days;
                if ($annual_leave - $remaining_annual_leave < 0) {
                    $this->_arr = array('status' => 'false', 'message' => '特休已使用完畢，無法通過');
                    return;
                } else {
                    $stmt = $this->_link->prepare("UPDATE `memberdata` SET `Remaining_annual_leave` = ? WHERE `Name` = ?");
                    $stmt->bind_param("is", $remaining_annual_leave, $name);
                    $stmt->execute();
                }
            }
            if ($leave_category === "生理假") {
                $stmt = $this->_link->prepare("SELECT `Menstruation_days` FROM `menstruation_leave` WHERE `name` = ? AND `date` LIKE CONCAT(?, '%')");
                $stmt->bind_param("ss", $name, $this_month);
                $stmt->execute();
                $this->_result = $stmt->get_result();
                $row = $this->_result->fetch_assoc();
                $total_days = floatval($row["Menstruation_days"] + $leave_days);
                if ($total_days > 1) {
                    $this->_arr = array('status' => 'false', 'message' => '請假時間總和大於一天，無法通過');
                    return;
                } else {
                    $stmt = $this->_link->prepare("UPDATE `menstruation_leave` SET `Menstruation_days` = ? WHERE `name` = ? AND `date` LIKE CONCAT(?, '%')");
                    $stmt->bind_param("dss", $total_days, $name, $this_month);
                    $stmt->execute();
                }
            }
            $illustrate = NULL;
        } else {
            $illustrate = preg_replace('/\s+/', ' ', $array['illustrate']);
            if (empty($illustrate)) {
                $this->_arr = array('status' => 'false', 'message' => '未通過原因未填寫');
                return;
            }
        }

        $stmt = $this->_link->prepare("UPDATE `all_leave` SET `Status` = ?, `illustrate` = ? WHERE `Leave_id` = ?");
        $stmt->bind_param("ssi", $array['approval'], $illustrate, $array["reviewing_id"]);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $this->_arr = array('status' => 'true', 'message' => '編輯成功!');
        } else {
            $this->_arr = array('status' => 'false', 'message' => '失敗');
        }
    }
    public function sickImgUpload()
    {
        if (!empty($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $fileName = uniqid() . '.' . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            $destination = "upload/sick/" . date("Y-m-d-") . $fileName;
            if (!file_exists($destination)) {
                move_uploaded_file($_FILES["file"]["tmp_name"], "../" . $destination);
                $this->_arr = array('img_path' => $destination);
            }
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // GET 請求，取得資料
    $array = $_GET;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_form = new LeaveApplication;
    $array = json_decode(file_get_contents('php://input'), true);
    if (isset($_FILES)) {
        $leave_form->sickImgUpload();
    }
    if (isset($array['type'])) {
        switch ($array['type']) {
            case 'apply':
                $leave_form->applyLeave($array);
                break;
            case 'reviewing':
                $leave_form->reviewingLeave($array);
                break;
        }
    }
}
