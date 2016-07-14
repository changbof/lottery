<?php

class lib_validate {

	public function username($value) {
		return preg_match('/^[a-zA-Z0-9_]{4,16}$/', $value) ? true : false;
	}
	
	public function number($value) {
		return preg_match('/^[1-9]{1}[0-9]{0,}$/', $value) ? true : false;
	}
	
	public function number_float($value, $length) {
		return preg_match('/^[0-9]{1,}(\.)?\d{0,'.$length.'}$/', $value) ? true : false;
	}
	
	public function reg_code($value) {
		return preg_match('/^[A-Z0-9]+$/', $value) ? true : false;
	}
	
	public function qq($value) {
		return preg_match('/^[1-9]\d{4,12}$/', trim($value)) ? true : false;
	}

}