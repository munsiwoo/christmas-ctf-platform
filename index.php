<?php
error_reporting(0);
# made by munsiwoo

ini_set("session.cache_expire", 3600*24);
ini_set("session.gc_maxlifetime", 3600*24);
session_name('session_id');
session_start();

$ban_browser = "/Edge\/18|Trident\/4\.0|Trident\/7\.0/";
if(preg_match($ban_browser, $_SERVER['HTTP_USER_AGENT'])) {
    echo '<meta http-equiv="refresh" content="3;url=https://www.google.com/chrome/">';
    die('Your browser is not supported. (Ie, Edge)'); // Ban Internet Explorer and Edge(old version)
}

require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/function.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Controller.class.php';

$http_method = $_SERVER['REQUEST_METHOD']; // Request method
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$is_login = isset($_SESSION['username']);
$is_admin = isset($_SESSION['admin']);

$Controller = new Controller($http_method, $request_uri, $is_login, $is_admin);
