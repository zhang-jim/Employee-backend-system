<?php
include("mysqlconn.php");
session_start();
class work extends SqlConn
{
    protected $_user;
    protected $_category;
    protected $_class;
    protected $_date;
    protected $_time;
    protected $_startWork;
    protected $_ontime;
    protected $_getOffWork;
    protected $_lastclock;
    protected $workStatus = [];
    protected $statusCategoryarr = [];

    function __construct()
    {
        $this->_link = mysqli_connect($this->_host, $this->_username, $this->_password, $this->_database) or die("mysql connect error!");
        mysqli_set_charset($this->_link, 'UTF8');
        header("content-type:application/json;charset=utf-8");
        date_default_timezone_set("Asia/Taipei");

        // 取得當日日期與用戶名
        $this->_date = date("Y-m-d");
        $this->_time = date("H:i:s");
        $this->_user = $_SESSION["user"];
        $this->_category = $_SESSION["category"];
        // account -> name
        $this->sql = "SELECT `account`,`name`,`category`,`class` FROM `memberdata` WHERE `account` = '" . $_SESSION["account"] . "'";
        $this->_result = $this->_link->query($this->sql);
        while ($row = mysqli_fetch_assoc($this->_result)) {
            $this->_class = $row["class"];
        }
        // 抓取各班別時間 
        $this->sql = "SELECT * FROM `memberdata` INNER JOIN `classdata` USING(class) WHERE `name` = '$this->_user'";
        $this->_result = $this->_link->query($this->sql);
        while ($row = mysqli_fetch_assoc($this->_result)) {
            $this->_ontime = $row["ontime"];
            $this->_getOffWork = $row["getOffWork"];
            $this->_lastclock = $row["lastclock"];
            $this->_startWork = $row["startWork"];
        }
    }
    // 抓取每日上下班時間 顯示到個人行事曆
    function main()
    {
        $this->sql = "SELECT * FROM `time` WHERE `name` = '$this->_user'";

        $this->_result = $this->_link->query($this->sql);
        while ($row = mysqli_fetch_assoc($this->_result)) {
            $this->_arr[] = array(
                'work' => $row["onlineTime"],
                'back' => $row["offlineTime"],
                'calendar' => $row["workstatus"],
                'statusCategory' => $row["statusCategory"],
                'date' => $row["date"]
            );
        }
    }
    // 每日狀態判斷
    function status()
    {
        // 抓取員工上下班時間，當日狀態
        $this->sql = "SELECT * FROM `time` WHERE `name` = '$this->_user' AND `date` = '$this->_date'";
        $this->_result = $this->_link->query($this->sql);
        while ($row = mysqli_fetch_assoc($this->_result)) {
            $onlineTime = substr($row["onlineTime"], 11);
            $offlineTime = substr($row["offlineTime"], 11);
            if (!empty($row["workstatus"])) {
                array_push($this->workStatus, $row["workstatus"]);
            }
            if (!empty($row["statusCategory"])) {
                array_push($this->statusCategoryarr, $row["statusCategory"]);
            }
        }

        // 判斷當日狀態
        if ($onlineTime > $this->_ontime) {
            array_push($this->workStatus, "遲到");
            array_push($this->statusCategoryarr, "late");
        }
        if (!empty($offlineTime)) {
            //客服-晚因跨日 無法利用時間比較 通過計算工時
            if ($this->_class == "night") {
                $timeLag = intval(abs((strtotime($offlineTime) - strtotime($onlineTime)) / (60 * 60)));
                if (($offlineTime < $this->_getOffWork) || $timeLag < 9) {
                    array_push($this->workStatus, "早退");
                    array_push($this->statusCategoryarr, "excused");
                }
            } else {
                if ($offlineTime < $this->_getOffWork) {
                    array_push($this->workStatus, "早退");
                    array_push($this->statusCategoryarr, "excused");
                }
            }
            if (empty($this->workStatus)) {
                array_push($this->workStatus, "正常");
                array_push($this->statusCategoryarr, "ontime");
            }
        }

        //更新當日狀態
        $this->workStatus = array_unique($this->workStatus);
        $this->statusCategoryarr = array_unique($this->statusCategoryarr);
        $this->workStatus = implode(",", $this->workStatus);
        $this->statusCategoryarr = implode(",", $this->statusCategoryarr);
        $this->sql = "UPDATE `time` SET `workstatus` = '$this->workStatus' , `statusCategory` = '$this->statusCategoryarr' WHERE `name` = '$this->_user' AND `date` = '$this->_date'";
        $this->_result = $this->_link->query($this->sql);
    }
    // 計算遲到時間
    function delay()
    {
        $year = date('Y');
        $month = date('m');

        // 新增 每日遲到時間Table
        $this->sql = "SELECT `name`,`year`,`month`,`latetime` FROM `latetimetable` WHERE `year` = '$year' AND `month` = '$month' AND `name` = '$this->_user'";
        $this->_result = $this->_link->query($this->sql);
        if (mysqli_num_rows($this->_result) == 0) {
            $this->sql = "INSERT INTO `latetimetable` (`name`,`year`,`month`,`latetime`) VALUES ('$this->_user','$year','$month','00:00:00')";
            $this->_result = $this->_link->query($this->sql);
        }

        $this->sql = "SELECT * FROM `time` WHERE `workstatus` LIKE '遲到%' AND `date` = '$this->_date' AND `name` = '$this->_user'";
        $this->_result = $this->_link->query($this->sql);
        if (mysqli_num_rows($this->_result) > 0) {
            // 抓取員工上班時間
            $this->sql = "SELECT * FROM `time` WHERE `name` = '$this->_user' AND `date` = '$this->_date'";
            $this->_result = $this->_link->query($this->sql);
            while ($row = mysqli_fetch_assoc($this->_result)) {
                $onlineTime = substr($row["onlineTime"], 11);
            }

            //抓取當月遲到時間 累積天數
            $this->sql = "SELECT `name`,`year`,`month`,`latetime` FROM `latetimetable` WHERE `year` = '$year' AND `month` = '$month' AND `name` = '$this->_user'";
            $this->_result = $this->_link->query($this->sql);
            while ($row = mysqli_fetch_assoc($this->_result)) {
                $latetime = $row["latetime"];
            }

            // 計算遲到時間
            $h = "00";
            $m = abs(strtotime($onlineTime) - strtotime($this->_startWork)) / 60; //取分鐘

            if ($m >= 60) {
                $h = intval($m / 60);
                $m = $m % 60;
            }

            $h = sprintf("%02d", $h);
            $m = sprintf("%02d", $m);

            $timeLag = $h . ":" . $m . ":00";
            if ($timeLag > "08:00:00") {
                $timeLag = "08:00:00";
            }
            $this->sql = "UPDATE `time` SET `latetime` = '$timeLag' WHERE `name` = '$this->_user' && `date` = '$this->_date'"; //更新每日遲到時間
            $this->_result = $this->_link->query($this->sql);

            //更新總遲到時間 
            $h = date("H", strtotime($latetime)) + date("H", strtotime($timeLag)); //取小時
            $m = date("i", strtotime($latetime)) + date("i", strtotime($timeLag)); //取分鐘

            if ($m >= 60) {
                $h += intval($m / 60);
                $m = $m % 60;
            }

            $h = sprintf("%02d", $h);
            $m = sprintf("%02d", $m);

            $timeLag = $h . ":" . $m . ":00";
            $this->sql = "UPDATE `latetimetable` SET `latetime` = '$timeLag' WHERE `year` = '$year' AND `month` = '$month' AND `name` = '$this->_user' ";
            $this->_result = $this->_link->query($this->sql);
        }
    }
    // 上班
    function online()
    {
        $this->sql = "SELECT * FROM `time` WHERE `name` = '$this->_user' AND `date` = '$this->_date'";
        $this->_result = $this->_link->query($this->sql);

        if (mysqli_num_rows($this->_result) === 0) {
            $this->sql = "INSERT INTO `time` (`name`,`date`,`onlineTime`) VALUES ('$this->_user','$this->_date','" . date('Y-m-d H:i:s') . "')";
            $this->_result = $this->_link->query($this->sql);
            $this->_arr = array('message' => 'True', 'content' => '上班打卡成功!');
            $this->status(); 
            $this->delay();
        } else {
            $this->_arr = array('message' => 'False', 'content' => '已打卡上班!');
        }
    }
    // 下班
    function offline() 
    {
        if ($this->_time < $this->_lastclock) {
            $this->sql = "SELECT * FROM `time` WHERE `name` = '$this->_user' AND `date` = '$this->_date'"; //搜尋當日資料
            $this->_result = $this->_link->query($this->sql);
            if (mysqli_num_rows($this->_result) === 0) {
                if ($this->_class == "night") {
                    $this->_date = date("Y-m-d", strtotime("-1 days", strtotime("$this->_date")));
                    $this->sql = "SELECT * FROM `time` WHERE `name` = '$this->_user' AND `date` = '$this->_date' AND `offlineTime` is NULL";
                    $this->_result = $this->_link->query($this->sql);
                    if (mysqli_num_rows($this->_result) === 0) {
                        $this->_arr = array('message' => 'False', 'content' => '無本日打卡資訊!');
                    } else {
                        $this->sql = "UPDATE `time` SET `offlineTime` = '" . date('Y-m-d H:i:s') . "' WHERE `name` = '$this->_user' AND `date` = '$this->_date'";
                        $this->_result = $this->_link->query($this->sql);
                        $this->_arr = array('message' => 'True', 'content' => '下班! GO HOME~');
                        $this->status();
                    }
                } else {
                    $this->_arr = array('message' => 'False', 'content' => '無本日打卡資訊!');
                }
            } else {
                while ($row = mysqli_fetch_assoc($this->_result)) {
                    $offlineTime = $row["offlineTime"];
                }
                if ($offlineTime == NULL) {
                    $this->sql = "UPDATE `time` SET `offlineTime` = '" . date('Y-m-d H:i:s') . "'  WHERE `name` = '$this->_user' AND `date` = '$this->_date'";
                    $this->_result = $this->_link->query($this->sql);
                    $this->_arr = array('message' => 'True', 'content' => '下班! GO HOME~');
                    $this->status();
                } else {
                    $this->_arr = array('message' => 'False', 'content' => '已打過下班卡!!!');
                }
            }
        } else {
            $this->_arr = array('message' => 'False', 'content' => '打卡時間已過，無法打卡!');
        }
    }
    //忘打卡
    function forgotcheck() //解決上班未打卡 無資料 新增一筆並把上班時間計為NULL
    {
        if ($this->_time >= $this->_getOffWork && $this->_time < $this->_lastclock) {
            if ($this->_class == "night") {
                $this->_date = date("Y-m-d", strtotime("-1 days", strtotime("$this->_date")));
            }
            $this->sql = "SELECT * FROM `time` WHERE `name` = '$this->_user' AND `date` = '$this->_date'";
            $this->_result = $this->_link->query($this->sql);
            if (mysqli_num_rows($this->_result) === 0) {
                $status = True;
            } else {
                $status = False;
            }
            if ($status == True) {
                $this->sql = "INSERT INTO `time` (`name`,`date`,`workstatus`,`statusCategory`) 
            VALUES ('$this->_user','$this->_date','未打卡','unCheckIn')";
                $this->_result = $this->_link->query($this->sql);
                $this->_arr = array('message' => 'True', 'content' => '上班卡已補上');
            } else {
                $this->_arr = array('message' => 'False', 'content' => '無須補打上班卡');
            }
        } else {
            $this->_arr = array('message' => 'False', 'content' => '未到此功能開放時間，請於下班時間過後進行操作!');
        }
    }
}

if (isset($_GET["type"])) {
    $work = new work;
    switch ($_GET["type"]) {
        case 'online':
            $work->online();
            break;

        case 'forgot':
            $work->forgotcheck();
            break;

        case 'off':
            $work->offline();
            break;

        case 'showstatus':
            $work->main();
            break;
    }
}