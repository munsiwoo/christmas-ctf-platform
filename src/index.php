<?php
error_reporting(0);
# made by munsiwoo

ini_set("session.cache_expire", 3600*24);
ini_set("session.gc_maxlifetime", 3600*24); // 세션 만료기간 늘림
session_name('session_id');
session_start();

$ban_browser = "/Edge\/18|Trident\/4\.0|Trident\/7\.0/";
if(preg_match($ban_browser, $_SERVER['HTTP_USER_AGENT'])) {
    echo '<meta http-equiv="refresh" content="3;url=https://www.google.com/chrome/">';
    die('Your browser is not supported. (Ie, Edge)'); // Ie하고 구버전 Edge는 렌더 엔진이 좀 이상하다.
}

require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/function.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Controller.class.php';

$http_method = $_SERVER['REQUEST_METHOD']; // HTTP 메소드
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$is_login = isset($_SESSION['username']); // 로그인 여부, 유저 이름 체크
$is_admin = isset($_SESSION['admin']); // 어드민 체크

new Controller($http_method, $request_uri, $is_login, $is_admin);
