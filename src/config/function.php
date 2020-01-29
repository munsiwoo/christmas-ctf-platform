<?php
function redirect_url($url, $msg="") { // 리다이렉트 함수
	$execute  = "<script>location.href=\"{$url}\";";
	$execute .= strlen($msg) ? 'alert("'.addslashes($msg).'");' : '';
	$execute .= '</script>'; die($execute);
}

function backward_url($msg="") { // 이전 URL로
	$execute  = '<script>history.back();';
	$execute .= strlen($msg) ? 'alert("'.addslashes($msg).'");' : '';
	$execute .= '</script>'; die($execute);
}

function process_password($password) { // 패스워드 해싱 방식
	return md5(hash('sha256', sha1(md5($password).__SALT__)));
}

function anti_truncate_attack($data, $size=100) { // mysql truncate 공격 방지 함수
	return substr($data, 0, $size);
}

function get_real_ip() { // vps 호스팅 서버를 위한 ip가져오는 함수
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        preg_match('/^\d+\.\d+\.\d+\.\d+$/', $_SERVER['HTTP_X_FORWARDED_FOR'], $get_ip);
        $ip = $get_ip[0];
    }
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function datetime_to_array($datetime) {
    $timestamp = strtotime($datetime);

    $retval['y'] = date('Y', $timestamp);
    $retval['m'] = date('m', $timestamp);
    $retval['d'] = date('d', $timestamp);
    $retval['h'] = date('h', $timestamp);
    $retval['i'] = date('i', $timestamp);
    $retval['s'] = date('s', $timestamp);

    return $retval;
}