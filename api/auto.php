<?php
include("mysqlconn.php");
class CronTabPHP extends SqlConn
{
    protected $Timeid = [];
    public function main()
    {
        $this->sql = "SELECT * FROM `time` WHERE `offlineTime` is NULL AND `workstatus` NOT LIKE '%未打卡%' AND `date` LIKE '%" . date("Y-m") . "%' ";
        $this->_result = $this->_link->query($this->sql);
        if (mysqli_num_rows($this->_result) !== 0) {
            while ($row = mysqli_fetch_assoc($this->_result)) {
                $this->Timeid[] = $row["id"];
            }
        }
        if (!empty($this->Timeid)) {
            foreach ($this->Timeid as $key => $value) {
                $workStatus = [];
                $statusCategoryarr = [];
                $this->sql = "SELECT * FROM `time` WHERE `id` = '$value'";
                $this->_result = $this->_link->query($this->sql);
                while ($row = mysqli_fetch_assoc($this->_result)) {
                    if (!empty($row["workstatus"])) {
                        $workStatus = explode(",", $row["workstatus"]);
                    }
                    if (!empty($row["statusCategory"])) {
                        $statusCategoryarr = explode(",", $row["statusCategory"]);
                    }
                }

                array_push($workStatus, "未打卡");
                array_push($statusCategoryarr, "unCheckIn");
                $workStatus = array_unique($workStatus);
                $statusCategoryarr = array_unique($statusCategoryarr);
                $workStatus = implode(",", $workStatus);
                $statusCategoryarr = implode(",", $statusCategoryarr);

                $this->sql = "UPDATE `time` SET `workstatus` = '$workStatus',`statusCategory` = '$statusCategoryarr' WHERE `id` = '$value'";
                $this->_result = $this->_link->query($this->sql);
            }
        }
    }
    public function 
    
    annualLeave()
    {
        $this->sql = "SELECT `name`,`Prompting_date`,`EntryDate` FROM `memberdata` ORDER BY `EntryDate` ASC";
        $this->_result = $this->_link->query($this->sql);
        while ($row = mysqli_fetch_assoc($this->_result)) {
            $this->annualLeaveRange($row["name"], $row["EntryDate"]);
            if ($row["Prompting_date"] != NULL) {
                $this->promptMessage($row["name"], $row["Prompting_date"]);
            }
        }
    }
    public function annualLeaveRange($name, $date)
    {
        $entry_date = new DateTime($date);
        $now_date = new DateTime(date("Y-m-d"));
        $date_diff = $entry_date->diff($now_date); //計算相差天數 
        $year = $date_diff->y;
        switch ($year) {
            case 0:
                $month = $date_diff->m;
                if ($month >= 6) {
                    $annual_leave_days = 3;
                } else {
                    $annual_leave_days = 0;
                }
                break;
            case $year == 1 || $year == 2:
                $annual_leave_days = 10;
                break;
            case $year >= 3 && $year < 5:
                $annual_leave_days = 14;
                break;
            case $year >= 5 && $year < 10:
                $annual_leave_days = 15;
                break;
            default:
                $annual_leave_days = 15;
                if ($year >= 24) {
                    $annual_leave_days = 30;
                } else {
                    $year = $year - 9;
                    for ($i = 0; $i < $year; $i++) {
                        $annual_leave_days++;
                    }
                }
                break;
        }
        // 計算並寫入提醒日期
        if ($year >= 1) {
            $add_year = $year + 1;
            $add_days = (365 * $add_year) - 10 + (1 * floor($add_year / 4)); //閏年加回天數
            date_add($entry_date, date_interval_create_from_date_string($add_days . " days"));
            $prompting_date = date_format($entry_date, "Y-m-d");
            if ($year > 1) {
                $this->sql = "UPDATE `memberdata` SET `Annual_leave` = '$annual_leave_days',`Prompting_date` = '$prompting_date',`Remaining_annual_leave` = 0 WHERE `name` = '$name'";
            } else {
                $this->sql = "UPDATE `memberdata` SET `Annual_leave` = '$annual_leave_days',`Prompting_date` = '$prompting_date' WHERE `name` = '$name'";
            }
        } else {
            $this->sql = "UPDATE `memberdata` SET `Annual_leave` = '$annual_leave_days',`Prompting_date` = NULL WHERE `name` = '$name'";
        }
        $this->_link->query($this->sql);
    }
    public function promptMessage($name, $prompting_date)
    {
        $now_date = date("Y-m-d");
        $update_date = strtotime("+10 days", strtotime("$prompting_date"));
        if ($now_date == $prompting_date) {
            $text = "特休將於" . date("Y-m-d", $update_date) . "號刷新！";
            $this->sql = "INSERT INTO `annual_leave_message` (`Name`,`Date_of_filing`,`Text`) VALUES ('$name','" . date("Y-m-d") . "','$text')";
            $this->_link->query($this->sql);
        }
    }
    public function deleteUnapproved()
    {
        $now_date = date("Y-m-d");
        $stmt = $this->_link->prepare("SELECT `Leave_id`,`Sick_img`,`Date_of_filing` FROM `all_leave` WHERE `Status` = 'unapproved'");
        $stmt->execute();
        $this->_result = $stmt->get_result();

        while ($row = $this->_result->fetch_assoc()) {
            $leave_id = $row["Leave_id"];
            $sick_img = $row["Sick_img"];

            $expirationDate  = new DateTime($row['Date_of_filing']);
            $expirationDate->add(new DateInterval('P10D'));

            if ($now_date == $expirationDate->format('Y-m-d')) {
                if (file_exists($sick_img)) {
                    unlink($sick_img);
                }
                $stmt2 = $this->_link->prepare("DELETE FROM `all_leave` WHERE `Leave_id` = ?");
                $stmt2->bind_param("i", $leave_id);
                $stmt2->execute();
            };
        }
    }
    public function __destruct()
    {
        mysqli_free_result($this->_result);
        echo date("Y-m-d H:i:s")."\r";
    }
}
$cron_tab_php = new CronTabPHP;
$cron_tab_php->main();
$cron_tab_php->annualLeave();
$cron_tab_php->deleteUnapproved();