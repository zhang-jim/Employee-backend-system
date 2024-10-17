<?php
include("mysqlconn.php");
session_start();
class PageCount extends SqlConn
{
    public function main($category, $date)
    {
        if ($category === 'all') {
            $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `time` WHERE `date` LIKE CONCAT(?, '%')");
            $stmt->bind_param("s", $date);
        } else {
            $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `memberdata` INNER JOIN (`time`) USING(`name`) WHERE `category` LIKE  CONCAT(?, '%') AND `date` LIKE CONCAT(?, '%')");
            $stmt->bind_param("ss", $category, $date);
        }
        $stmt->execute();
        $this->_result = $stmt->get_result();
        $rows = $this->_result->fetch_array();
        $COUNT = ceil($rows["COUNT(*)"] / 15);
        $this->_arr = array(
            'COUNT' => $COUNT
        );
    }
    public function leaveTimeListCount($category, $date)
    {
        if ($category === 'all') {
            $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `all_leave` WHERE `Date_of_filing` LIKE CONCAT( ?, '%')");
            $stmt->bind_param("s", $date);
        } else {
            $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `all_leave` INNER JOIN `memberdata` USING(`Name`) WHERE `category` LIKE  CONCAT(?, '%') AND `Date_of_filing` LIKE CONCAT(?, '%')");
            $stmt->bind_param("ss", $category, $date);
        }
        $stmt->execute();
        $this->_result = $stmt->get_result();
        $rows = $this->_result->fetch_array();
        $COUNT = ceil($rows["COUNT(*)"] / 15);
        $this->_arr = array(
            'COUNT' => $COUNT
        );
    }
    public function memberCount($category)
    {
        if ($category === 'all') {
            $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `memberdata`");
        } else {
            $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `memberdata` WHERE `category` LIKE CONCAT(?, '%')");
            $stmt->bind_param("s", $category);
        }
        $stmt->execute();
        $this->_result = $stmt->get_result();
        $rows = $this->_result->fetch_array();
        $COUNT = ceil($rows["COUNT(*)"] / 15);
        $this->_arr = array(
            'COUNT' => $COUNT
        );
    }

    public function leaveCount($class, $status = 'undetermined')
    {
        if ($class == 'approval') {
            if ($_SESSION["rule"] == 1) {
                $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `all_leave` INNER JOIN `memberdata` USING(`Name`) WHERE `rule` = ? AND `Status` = ?");
                $stmt->bind_param("is", 2, $status);
            } elseif ($_SESSION["rule"] == 2) {
                $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `all_leave` INNER JOIN `memberdata` USING(`Name`) WHERE `Name` != ? AND `category` LIKE CONCAT('%', ?, '%') AND `rule` != ? AND `Status` = ?");
                $stmt->bind_param("ssis", $_SESSION["user"], $_SESSION["category"], $_SESSION["rule"], $status);
            } else {
                $this->_arr['status'] = "false";
                return;
            }
        } else {
            $stmt = $this->_link->prepare("SELECT COUNT(*) FROM `all_leave` INNER JOIN `memberdata` USING (`Name`) WHERE `account` = ?");
            $stmt->bind_param('s', $_SESSION["account"]);
        }
        $stmt->execute();
        $this->_result = $stmt->get_result();
        $rows = $this->_result->fetch_array();
        $COUNT = ceil($rows["COUNT(*)"] / 7);
        $this->_arr = array(
            'COUNT' => $COUNT
        );
    }
}
if (isset($_GET['type'])) {
    $page_count = new PageCount;
    switch ($_GET["type"]) {
        case 'member_count':
            $page_count->memberCount($_GET['category']);
            break;
        case 'count':
            $page_count->main($_GET['category'], $_GET["date"]);
            break;
        case 'all_leave_count':
            $page_count->leaveTimeListCount($_GET['category'], $_GET["date"]);
            break;
        case 'leave_count':
            if (isset($_GET['review_status'])) {
                $page_count->leaveCount($_GET['class'], $_GET['review_status']);
            } else {
                $page_count->leaveCount($_GET['class']);
            }
            break;
    }
}
