<?php
namespace lib;

class PayUtils {

	/**
	 * =¨¢&
	 * @param $para 
	 * return 
	 */
	static public function createLinkstring($para) {
		$arg  = "";
		foreach ($para as $key=>$val) {
			$arg.=$key."=".$val."&";
		}
		//&
		$arg = substr($arg,0,-1);

		return $arg;
	}
	/**
	 * =¨¢&urlencode
	 * @param $para 
	 * return 
	 */
	static public function createLinkstringUrlencode($para) {
		$arg  = "";
		foreach ($para as $key=>$val) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//&
		$arg = substr($arg,0,-1);

		return $arg;
	}
	/**
	 * §Ö
	 * @param $para 
	 * return 
	 */
	static public function paraFilter($para) {
		$para_filter = array();
		foreach ($para as $key=>$val) {
			if($key == "sign" || $key == "sign_type" || $val == "" || $key == "stype" || $key == "request_method" || $key == "u_channel" )continue;
			else $para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	/**
	 * 
	 * @param $para 
	 * return 
	 */
	static public function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
	/**
	 * 
	 * @param $prestr 
	 * @param $key 
	 * return 
	 */
	static public function md5Sign($prestr, $key) {
		$prestr = $prestr . $key;
		return md5($prestr);
	}

	/**
	 * 
	 * @param $prestr 
	 * @param $sign 
	 * @param $key 
	 * return 
	 */
	static public function md5Verify($prestr, $sign, $key) {
		$prestr = $prestr . $key;
		$mysgin = md5($prestr);
 
		if($mysgin == $sign) {
			return true;
		}
		else {
			return false;
		}
	}
}
