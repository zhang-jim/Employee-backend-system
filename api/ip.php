<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header("content-type:application/json;charset=utf-8");
    $allowedIPs = ['35.94.93.42'];
    $userIP = $_SERVER['REMOTE_ADDR'];
    if (in_array($userIP, $allowedIPs)) {
        $response_value = array('status' => 'false', 'message' => 'Access denied.');
    } else {
        $response_value = array('status' => 'true', 'message' => 'Welcome to the system!');
    }
    echo json_encode($response_value);
}
