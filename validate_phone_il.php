<?php
/**
 * usage:
 * $phone = new validate_phone_il($str);
 * if ($phone->valid){
 * 	//phone number is valid
 * }else{
 * 	//phone not valid
 * }
 */
class validate_phone_il{
	public $phone  = '';
	public $valid  = false;
	public $length = 0;
	function __construct($phone = ''){
		if (empty($phone)){
			$this->valid = false;
			return;
		}
		$this->phone  = $this->digits_only($phone);
		$this->length = strlen($this->phone);
		$this->validate();
	}

	function digits_only($str){
		return preg_replace('/\D/', '', $str);
	}

	function validate(){
		$phone = $this->phone;
		$i1    = $phone[0];
		$i2    = $phone[1];
		$i13   = $phone[0] . $phone[1] . $phone[2];
		if ( $i1 == 0 && ($i2 == 5 || $i2 == 7)){
			$pre = array("050","052","053","054","055","056","057","058","059","072","073","074","076","077","078");
			if (in_array($i13, $pre) && $this->length == 10 ){
				$this->valid =  true;
			}
		}elseif($i1 == 0 && (in_array( $i2,array(2,3,4,8,9) ) && $this->length == 9 )){
			$this->valid =  true;
		}else{
			$this->valid =  false;
		}
	}
}