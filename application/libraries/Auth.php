<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth {

	public function __construct()
	{
		$CI =& get_instance();
	}

	function account($feedcome = array()) {
		$CI =& get_instance();
		$CI->load->library('form_validation');
        //$CI->load->model('login_sessions');
        

		if ($CI->input->post('login_key') != "") {
			# code...
		} else if ($CI->input->post('login_key') != "") {
			# code...
		} else if ($CI->input->post('device_id') != "") {
			# code...
		} else {
			$CI->write->error("Server can't register your device");
		}
        // Cek apakah login_key ada
        // jika tidak cek device_key
        // jika tidak daftarkan device_id, balance user_id dan berikan device_key

		// Session Key is needed every time user using private content
		$CI->form_validation->set_rules('session_key', 'Session Key', 'required');
		$session_key = $CI->input->post('session_key');

		// Create validation and bypass input to $feedback[$value] from $feedcome
		foreach ($feedcome as $key => $value) {
			$CI->form_validation->set_rules($key, $value, 'required');
			$feedback[$key]	= $CI->input->post($key);
		}

		// Validate input process
		if (!$CI->form_validation->run()) {
			$error_field = '';
			$this->_error('-', 'There some missing input');
			exit();
		}

		// Get login data and check if it's available
		$feedback['login_data'] = $CI->login_sessions->select_by_session_key($session_key);
		if (!$feedback['login_data']) {
			$this->_error('108', 'Expired login session');
			exit();
		}
		$feedback['login_data'] = $feedback['login_data'][0];
		return $feedback;
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