<?php

function is_valid_torrent(&$arr) {
	if(is_array($arr) && is_array($arr[0])) {
		if (array_key_exists('announce',$arr[0]) && array_key_exists('info', $arr[0])) {
			if(is_array($arr[0]['info'])) {
				if(array_key_exists('piece length', $arr[0]['info']) &&
					array_key_exists('pieces', $arr[0]['info']) &&
					array_key_exists('name', $arr[0]['info']) &&
					(array_key_exists('length', $arr[0]['info']) ||
					(array_key_exists('files', $arr[0]['info']) && is_array($arr[0]['info']['files'])))) {
						return true;
				}
			}
		}
	}
	return false;
}

function get_torrent_name(&$arr) {
	return $arr[0]['info']['name'];
}
function get_lenght (&$arr) {
	if(array_key_exists('length', $arr[0]['info'])) {
		return $arr[0]['info']['length'];
	}
	return null;
}
function get_multy_lenght(&$arr) {
	if(is_array($arr[0]['info']['files'])) {
		$len = 0;
		foreach($arr[0]['info']['files'] as &$value) {
			if(is_array($value) && array_key_exists('length', $value)) {
				$len+=$value['length'];
			}
		}
		return $len;
	}
	return null;
} 
function get_multy_count(&$arr) {
	if(is_array($arr[0]['info']['files'])) {
		return count($arr[0]['info']['files']);
	}
	return null;
}
function get_multy_index(&$arr, $i) {
	if(is_array($arr[0]['info']['files'][$i]) && array_key_exists('path', $arr[0]['info']['files'][$i])) {
		$str = "";
		foreach($arr[0]['info']['files'][$i]['path'] as &$value) {
			if(strlen($str) > 0) {
				$str.='/' . $value;
			} else {
				$str.=$value;
			}
		}
		return $str;
	}
	return null;
}

function getip() {
    if (getenv('HTTP_CLIENT_IP') && long2ip(ip2long(getenv('HTTP_CLIENT_IP')))==getenv('HTTP_CLIENT_IP'))
        return getenv('HTTP_CLIENT_IP');

    if (getenv('HTTP_X_FORWARDED_FOR') && long2ip(ip2long(getenv('HTTP_X_FORWARDED_FOR')))==getenv('HTTP_X_FORWARDED_FOR'))
        return getenv('HTTP_X_FORWARDED_FOR');

    if (getenv('HTTP_X_FORWARDED') && long2ip(ip2long(getenv('HTTP_X_FORWARDED')))==getenv('HTTP_X_FORWARDED'))
        return getenv('HTTP_X_FORWARDED');

    if (getenv('HTTP_FORWARDED_FOR') && long2ip(ip2long(getenv('HTTP_FORWARDED_FOR')))==getenv('HTTP_FORWARDED_FOR'))
        return getenv('HTTP_FORWARDED_FOR');

    if (getenv('HTTP_FORWARDED') && long2ip(ip2long(getenv('HTTP_FORWARDED')))==getenv('HTTP_FORWARDED'))
        return getenv('HTTP_FORWARDED');

    $ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
    /* Added support for IPv6 connections. otherwise ip returns null */
    if (strpos($ip, '::') === 0) {
        $ip = substr($ip, strrpos($ip, ':')+1);
    }
    
   return long2ip(ip2long($ip));
}

function get_format_size($size) {
	$step = 0;
	$arr = array('B', 'KB', 'MB', 'GB');
	while($size >= 1000 && $step < 3) {
		$size/=1024;
		$step++;
	}
	return round($size, 2) . ' ' . $arr[$step];
}
