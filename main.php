<?php
ini_set('display_errors', 1);
require 'mysqly.php'; # include library (single file)

header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");

mysqly::auth('root', 'root', 'task', '127.0.0.1');

function getViewCount($ip_address, $user_agent, $image_id): int
{
    return checkLogExists($ip_address, $user_agent, $image_id) ? mysqly::fetch('logs', ['ip_address' => $ip_address, 'user_agent' => $user_agent, 'image_id' => $image_id])[0]['view_count'] : 0;
}

function increaseViewCount($ip_address, $user_agent, $image_id)
{
    if (checkLogExists($ip_address, $user_agent, $image_id)) {
        updateViewCount($ip_address, $user_agent, $image_id, getViewCount($ip_address, $user_agent, $image_id) + 1);
    } else {
        createLog($ip_address, $user_agent, $image_id, 1);
    }
}

function checkLogExists($ip_address, $user_agent, $image_id): bool
{
    return mysqly::count('logs', ['ip_address' => $ip_address, 'user_agent' => $user_agent, 'image_id' => $image_id]) > 0;
}

function createLog($ip_address, $user_agent, $image_id, $view_count = 0)
{
    mysqly::insert('logs', ['ip_address' => $ip_address, 'user_agent' => $user_agent, 'image_id' => $image_id, 'view_count' => $view_count]);
}

function updateViewCount($ip_address, $user_agent, $image_id, $count)
{
    mysqly::update('logs', ['ip_address' => $ip_address, 'user_agent' => $user_agent, 'image_id' => $image_id], ['view_count' => $count]);
}

function getIPAddress()
{
    //whether ip_address is from the share internet  
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    }
    //whether ip_address is from the proxy  
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    //whether ip_address is from the remote address  
    else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    return $ip_address;
}

function returnResponse($data)
{
    echo json_encode($data);
}

if ($_GET["request_type"] == 'getRandomNumber') {
    returnResponse(rand(1, 4));
} elseif ($_GET["request_type"] == 'increaseCount' && $_GET["image_id"] && in_array($_GET["image_id"], [1, 2, 3, 4, 5])) {
    increaseViewCount(getIPAddress(), $_SERVER['HTTP_USER_AGENT'], $_GET["image_id"]);
} elseif ($_GET["request_type"] == 'getCount' && $_GET["image_id"] && in_array($_GET["image_id"], [1, 2, 3, 4, 5])) {
    returnResponse(getViewCount(getIPAddress(), $_SERVER['HTTP_USER_AGENT'], $_GET["image_id"]));
}
