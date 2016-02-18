<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Write {
	public function __construct() {
		$CI =& get_instance();
	}

	public function error($message = 'Unknown error') {
		$json['error']			= true;
		$json['error_message']	= $message;
		$this->feedback($json);
		exit();
	}

	public function feedback($array_data) {
		$CI 				=& get_instance();
		$output['data'] 	= $array_data;
		$CI->load->view('make_json', $output);
		exit();
	}
}