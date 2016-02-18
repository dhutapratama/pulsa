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

	private function _add_login_session($member_id, $saldo_id, $device_id) {
		$CI =& get_instance();
        $CI->load->model('login_sessions');
		$login_key = md5(time().rand(1000, 9999));
		$login_session['login_key']	= $login_key;
		$login_session['date']		= date('Y-m-d H:i:s');
		$login_session['member_id']	= $member_id;
		$login_session['saldo_id']	= $saldo_id;
		$login_session['device_id']	= $device_id;
		$CI->login_sessions->insert($login_session);

		$feedback['error'] = false;
		$feedback['data']['login_key'] = $login_key;
		$CI->write->feedback($feedback);
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

		return $post_data;
	}
}