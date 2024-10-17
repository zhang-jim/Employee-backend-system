<?php
include("mysqlconn.php");
session_start();
class SearchData extends SqlConn
{
    // startAndFinishWorkTimeList (上下班時間列表)
    public function startAndFinishWorkTimeList($num, $category, $date, $finish = 15)
    {
        $punch_list = null;
        if ($_SESSION['rule'] > 1 && $_SESSION['rule'] !== 'admin') {
            $this->_arr = array('status' => 'false');
            return;
        }
        $start = ($num - 1) * 15;
        if ($category === 'all') {
            $stmt = $this->_link->prepare("SELECT * FROM `time` WHERE `date` LIKE CONCAT( ?, '%') ORDER BY `date` DESC LIMIT ?, ?");
            $stmt->bind_param('sii', $date, $start, $finish);
        } else {
            $stmt = $this->_link->prepare("SELECT * FROM `memberdata` INNER JOIN (`time`) USING(`name`) WHERE `category` LIKE CONCAT( ?, '%') AND `date` LIKE CONCAT( ?, '%') ORDER BY `date` DESC LIMIT ?, ?");
            $stmt->bind_param('ssii', $category, $date, $start, $finish);
        }
        $stmt->execute();
        $this->_result = $stmt->get_result();
        while ($row = $this->_result->fetch_assoc()) {
            $punch_list[] = array(
                'name' => $row["name"],
                'work' => $row["onlineTime"],
                'back' => $row["offlineTime"],
                'calendar' => $row["workstatus"],
                'category' => $row["statusCategory"],
                'date' => $row["date"]
            );
        }
        $this->_arr['punch_list'] = $punch_list;
    }
    // absenteeismData (出缺勤資料)
    public function absenteeismData($member, $date)
    {
        $time_list = null;
        $annual_day = 0;
        $sick_day = 0;
        $personal_day = 0;
        $this->sql = "SELECT `Leave_category`,`Start_date`,`Finish_date`,`Days` FROM `all_leave` WHERE `Name` = ? AND `Start_date` LIKE CONCAT(?, '%') AND `Status` = 'approved'";
        $stmt = $this->_link->prepare($this->sql);
        $stmt->bind_param('ss', $member, $date);
        $stmt->execute();
        $this->_result = $stmt->get_result();

        while ($row = $this->_result->fetch_assoc()) {
            $time_list[] = array(
                'start_date' => $row["Start_date"],
                'finish_date' => $row["Finish_date"],
                'leave_category' => $row["Leave_category"]
            );
            switch ($row['Leave_category']) {
                case '特休':
                    $annual_day += $row['Days'];
                    break;
                case '病假':
                    $sick_day += $row['Days'];
                    break;
                case '事假':
                    $personal_day += $row['Days'];
                    break;
            }
        }
        $this->_arr['timelist'] = $time_list;
        $this->_arr['annual_leave'] = $annual_day;
        $this->_arr['sick_leave'] = $sick_day;
        $this->_arr['personal_leave'] = $personal_day;

        $date_array = explode("-", $date);
        $year = $date_array[0];
        $month = $date_array[1];
        $this->sql = "SELECT `latetime` FROM `latetimetable` WHERE `year` = ? AND `month` = ? AND `name` = ?";
        $stmt = $this->_link->prepare($this->sql);
        $stmt->bind_param('sss', $year, $month, $member);
        $stmt->execute();
        $this->_result = $stmt->get_result();
        $row = $this->_result->fetch_assoc();
        if ($row) {
            $this->_arr['latetime'] = $row['latetime'];
        } else {
            $this->_arr['latetime'] = null;
        }

        $this->sql = "SELECT * FROM `time` WHERE `name` = ? AND `workstatus` LIKE CONCAT('%未打卡%') AND `date` LIKE CONCAT(?, '%')";
        $stmt = $this->_link->prepare($this->sql);
        $stmt->bind_param('ss', $member, $date);
        $stmt->execute();
        $this->_result = $stmt->get_result();
        $this->_arr['unCheckIn'] = $this->_result->num_rows;

        $this->sql = "SELECT `Annual_leave`,`Remaining_annual_leave` FROM `memberdata` WHERE `name` = ?";
        $stmt = $this->_link->prepare($this->sql);
        $stmt->bind_param('s', $member);
        $stmt->execute();
        $this->_result = $stmt->get_result();
        $row = $this->_result->fetch_assoc();
        $annual_total = $row['Annual_leave'] - $row['Remaining_annual_leave'];
        $this->_arr['annual_total'] = $annual_total;

        mysqli_free_result($this->_result);
    }
    // promptMessage (提示訊息)
    public function promptMessage()
    {
        $this->sql = "SELECT `Name`,`Date_of_filing`,`Text` FROM `annual_leave_message`";
        $this->_result = $this->_link->query($this->sql);
        while ($row = mysqli_fetch_assoc($this->_result)) {
            $this->_arr[] = array(
                'name' => $row["Name"],
                'date' => $row["Date_of_filing"],
                'text' => $row["Text"]
            );
        }
    }
    // leaveRecord (檢視假單紀錄)
    public function leaveRecord($num, $finish = 7)
    {
        $start = ($num - 1) * 7;
        $this->sql = "SELECT `Name`,`Start_date`,`Finish_date`,`Date_of_filing`,`Status`,`Leave_category`,`illustrate`,`account` FROM `all_leave` INNER JOIN `memberdata` USING(`Name`) WHERE `account` = ? ORDER BY `Date_of_filing` DESC LIMIT ?, ?";
        $stmt = $this->_link->prepare($this->sql);
        $stmt->bind_param('sii', $_SESSION['account'], $start, $finish);
        $stmt->execute();
        $this->_result = $stmt->get_result();
        while ($row = $this->_result->fetch_assoc()) {
            $this->_arr[] = array(
                'name' => $row["Name"],
                'start_date' => $row["Start_date"],
                'finish_date' => $row["Finish_date"],
                'date_of_filing' => $row["Date_of_filing"],
                'status' => $row["Status"],
                'leave_category' => $row["Leave_category"],
                'illustrate' => $row["illustrate"]
            );
        }
    }
    // Pending Leave Requests (待審核假單)
    public function pendingLeaveRequests($status, $num, $finish = 7)
    {
        $leave_list = null;
        $start = ($num - 1) * 7;
        if ($_SESSION["rule"] == 1) {
            $sql_rule = 1;
            $this->sql = "SELECT `Leave_id`,`Name`,`category`,`Start_date`,`Finish_date`,`Status`,`Leave_category`,`rule` FROM `all_leave` INNER JOIN `memberdata` USING(`Name`) WHERE `rule` != ?  AND `Status` = ? ORDER BY `start_date` ASC LIMIT ?, ?";
            $stmt = $this->_link->prepare($this->sql); //準備SQL指令
            $stmt->bind_param("isii", $sql_rule, $status, $start, $finish);
        } elseif ($_SESSION["rule"] == 2) {
            $this->sql = "SELECT `Leave_id`,`Name`,`category`,`Start_date`,`Finish_date`,`Status`,`Leave_category`,`rule` FROM `all_leave` INNER JOIN `memberdata` USING(`Name`) WHERE `Name` != ? AND `Category` LIKE CONCAT('%', ?, '%') AND `rule` != ?  AND `Status` = ? ORDER BY `start_date` ASC LIMIT ?, ?";
            $stmt = $this->_link->prepare($this->sql); //準備SQL指令
            $stmt->bind_param("ssisii", $_SESSION["user"], $_SESSION["category"], $_SESSION["rule"], $status, $start, $finish); // s 代表傳入的是字串 設定參數
        } else {
            $this->_arr['status'] = "false";
            return;
        }

        $stmt->execute(); //執行查詢
        $this->_result = $stmt->get_result(); //取得結果
        while ($row = $this->_result->fetch_assoc()) {
            $leave_list[] = array(
                'leave_id' => $row["Leave_id"],
                'name' => $row["Name"],
                'category' => $row["category"],
                'start_date' => $row["Start_date"],
                'finish_date' => $row["Finish_date"],
                'status' => $row["Status"],
                'leave_category' => $row["Leave_category"]
            );
        }
        $this->_arr["leave_list"] = $leave_list;
    }
    public function approveLeaveApplication($leave_id)
    {
        $this->sql = "SELECT `Leave_id`,`Name`,`Start_date`,`Finish_date`,`Date_of_filing`,`Status`,`Leave_category`,`Reason`,`Sick_img` FROM `all_leave`  WHERE `Leave_id` = ?";
        $stmt = $this->_link->prepare($this->sql); //準備SQL指令
        $stmt->bind_param("i", $leave_id); // s 代表傳入的是字串 設定參數

        $stmt->execute(); //執行查詢
        $this->_result = $stmt->get_result(); //取得結果
        while ($row = $this->_result->fetch_assoc()) {
            $this->_arr = array(
                'leave_id' => $row["Leave_id"],
                'name' => $row["Name"],
                'date_of_filing' => $row["Date_of_filing"],
                'start_date' => $row["Start_date"],
                'finish_date' => $row["Finish_date"],
                'status' => $row["Status"],
                'reason' => $row["Reason"],
                'leave_category' => $row["Leave_category"],
                'sick_img' => $row["Sick_img"]
            );
        }
    }
    public function leaveTimeList($num, $category, $date, $finish = 15)
    {
        $leave_list = null;
        if ($_SESSION['rule'] > 1 && $_SESSION['rule'] !== 'admin') {
            $this->_arr = array('status' => 'false');
            return;
        }
        $start = ($num - 1) * 15;
        if ($category === 'all') {
            $stmt = $this->_link->prepare("SELECT * FROM `all_leave` WHERE `Date_of_filing` LIKE CONCAT( ?, '%') ORDER BY `Date_of_filing` DESC LIMIT ?, ?");
            $stmt->bind_param('sii', $date, $start, $finish);
        } else {
            $stmt = $this->_link->prepare("SELECT * FROM `all_leave` INNER JOIN `memberdata` USING(`Name`) WHERE `category` LIKE CONCAT( ?, '%') AND `Date_of_filing` LIKE CONCAT( ?, '%') ORDER BY `Date_of_filing` DESC LIMIT ?, ?");
            $stmt->bind_param('ssii', $category, $date, $start, $finish);
        }
        $stmt->execute();
        $this->_result = $stmt->get_result();
        while ($row = $this->_result->fetch_assoc()) {
            $leave_list[] = array(
                'name' => $row["Name"],
                'start_date' => $row["Start_date"],
                'finish_date' => $row["Finish_date"],
                'date_of_filing' => $row["Date_of_filing"],
                'status' => $row["Status"],
                'leave_category' => $row["Leave_category"]
            );
        }
        $this->_arr['leave_list'] = $leave_list;
    }
}

if (isset($_GET["type"])) {
    $data = new SearchData;
    switch ($_GET["type"]) {
        case 'show_data':
            $data->startAndFinishWorkTimeList($_GET["page"], $_GET["category"], $_GET["date"]);
            break;
        case 'show_leave_list':
            $data->leaveTimeList($_GET["page"], $_GET["category"], $_GET["date"]);
            break;
        case 'absence':
            $data->absenteeismData($_GET["member"], $_GET["date"]);
            break;
        case 'prompt_message':
            $data->promptMessage();
            break;
        case 'leave_record':
            $data->leaveRecord($_GET['num']);
            break;
        case 'pending_leave_requests':
            $data->pendingLeaveRequests($_GET["review_status"], $_GET['num']);
            break;
        case 'approve_leave_application':
            $data->approveLeaveApplication($_GET["leave_id"]);
            break;
    }
}
