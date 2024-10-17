<?php
class SqlConn
{
    protected $_host = 'localhost';
    protected $_username = 'backend_system';
    protected $_password = 'KzxYR6aGbPGp';
    protected $_database = 'backend_system';
    protected $_link;
    protected $_result;
    protected $_arr;
    public $sql;
    function __construct()
    {
        $this->_link = mysqli_connect($this->_host, $this->_username, $this->_password, $this->_database) or die("mysql connect error!");
        mysqli_set_charset($this->_link,'UTF8');
        header("content-type:application/json;charset=utf-8");
        date_default_timezone_set("Asia/Taipei");
    }

    function __destruct()
    {
        echo json_encode($this->_arr);
    }
}
