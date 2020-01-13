<?php
function redirect_url($url, $msg="") { // redirect function
	$execute  = "<script>location.href=\"{$url}\";";
	$execute .= strlen($msg) ? 'alert("'.addslashes($msg).'");' : '';
	$execute .= '</script>'; die($execute);
}

function backward_url($msg="") { // history backward
	$execute  = '<script>history.back();';
	$execute .= strlen($msg) ? 'alert("'.addslashes($msg).'");' : '';
	$execute .= '</script>'; die($execute);
}

function process_password($password) {
	return md5(hash('sha256', sha1(md5($password).__SALT__)));
}

function anti_truncate_attack($data, $size=100) { // anti mysql truncate attack
	return substr($data, 0, $size);
}

function get_real_ip() {
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