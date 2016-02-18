<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth {

	public function __construct()
	{
		$CI =& get_instance();
	}

	function login_key() {
		$CI =& get_instance();
        $CI->load->model('login_sessions');
        
		$login_data = $this->login_sessions->get_by_login_key($CI->input->post('login_key'));
		if (!$login_data) {
			$this->write->error('Your session was expired');	
		}
		return true;
	}

	function input($input = array()) {
		$CI =& get_instance();

		foreach ($input as $key => $value) {
			$CI->form_validation->set_rules($key, '', $value);
			$post_data[$key]	= $CI->input->post($key);
		}

		if (!$CI->form_validation->run()) {
			$CI->write->error('There some missing input');
			exit();
		}
	}
}