<?php

$ben_start = 0;
function ben_decode(&$str) {
	global $ben_start;
	$ben_start = 0;
	$ben_array = array();
	$index = 0;
	while(($ret = ben_get_element($str)) !== null) {
		$ben_array[$index++] = $ret;
	}
	return $ben_array;
}

function ben_get_element(&$str) {
	global $ben_start;
	$state = 0;
	$length = strlen($str);
	for ($i=$ben_start; $i<$length; $i++) {
		switch($state) {
			case 0:
				switch ($str[$i]) {
					case "i":
						$state = 1;
						$tmp_str = "";
						break;
					case "l":
						$state = 2;
						$ret = array();
						break;
					case "d":
						$state = 7;
						$ret = array();
						break;
				}
				if($state === 0) {
					if(is_numeric($str[$i])) {
						$state = 6;
						$tmp_str = $str[$i];
					} else {
						$state = 10;
					}
				}
				break;
			case 1:
				if(is_numeric($str[$i]) || $str[$i] == "-") {
					$tmp_str .= $str[$i];
				} else if($str[$i] == "e") {
					$ben_start = $i+1;
					return (int)$tmp_str;
				} else {
					$state = 10;
				}
				break;
			case 2:
				if($str[$i] == "e") {
					$ben_start = $i+1;
					$ret[0] = null;
					return $ret;
				} else {
					$ben_start = $i;
					$index = 0;

					while($ben_start < $length && $str[$ben_start] != "e" && ($tmp = ben_get_element($str)) !== null) {
						$ret[$index++] = $tmp;
					}

					$ben_start++;
					return $ret;
				}
				break;
			case 6:
				if(is_numeric($str[$i])) {
					$tmp_str .= $str[$i];
				} else if($str[$i] == ":") {
					$ben_start = $i+(int)$tmp_str+1;
					return substr($str, $i+1, (int)$tmp_str);
				} else {
					$state = 10;
				}
				break;
			case 7:
				if($str[$i] == "e") {
					$ben_start = $i+1;
					$ret[0] = null;
					return $ret;
				} else {
					$ben_start = $i;
					while($ben_start < $length && $str[$ben_start] != "e") {
						$index = ben_get_element($str);
						if($index !== null) {
							$tmp = ben_get_element($str);
							$ret[$index] = $tmp;
						} else {
							break;
						}
					}
					$ben_start++;
					return $ret;
				}
				break;
		}
	}
	$ben_start = $i;
	return null;
}
function ben_is_assoc(&$arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}
function ben_encode(&$arr) {
	$ret = "";
	$start = 0;
	if(is_array($arr)) {
		foreach ($arr as $key => $value) {
			if(ben_is_assoc($arr)) {
				if (is_integer($key)) {
					$ret .= "i".$key."e";
				} else if(is_string($key)) {
					$ret .= strlen($key) . ':' . $key;
				}
			}
			if(is_integer($value)) {
				$ret .= "i".$value."e";
			} else if(is_string($value)) {
				$ret .= strlen($value) . ':' . $value;
			} else if (is_array($value)){
				if(ben_is_assoc($value)) {
					$ret .= "d" . ben_encode($value) . "e";
				} else {
					$ret .= "l" . ben_encode($value) . "e";
				}
			}
		}
	}
	return $ret;
}
