<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_apps extends CI_Controller {

	public function __construct() {
        parent::__construct();
        //$this->load->library();
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
        	redirect();
        }
     }

	public function index() {


		$this->write->feedback($feedback);
	}

	public function get_login_key() {
		$this->load->model(array('members'));

		$input = array('device_id' => 'required');

		$member_data = $this->members->get_by_device_id($input['device_id']);
		if (!$member_data) {
			// Create new membership
			$date = date('Y-m-d H:i:s');

			$member['device_id']			= $input['device_id'];
			$member['is_email_verified']	= '';
			$member['is_phone_verified']	= '';
			$member['last_update']			= $date;
			$this->members->insert($member);
			$member_id = $this->db->insert_id();

			$saldo['member_id']		= $member_id;
			$saldo['amount']		= 0;
			$saldo['last_update']	= $date;
			$this->saldo->insert($saldo);
			$saldo_id = $this->db->insert_id();

			$this->_add_login_session($member_id, $saldo_id);
		}
		$this->_add_login_session($member_data->member_id, $member_data->saldo_id);
	}

	private function _add_login_session($member_id, $saldo_id) {
		$login_key = md5(time().rand(1000, 9999));
		$login_session['login_key']	= $login_key;
		$login_session['date']		= date('Y-m-d H:i:s');
		$login_session['member_id']	= $member_id;
		$login_session['saldo_id']	= $saldo_id;
		$login_session['device_id']	= $input['device_id'];
		$this->login_sessions->insert($login_session);

		$feedback['error'] = false;
		$feedback['data']['login_key'] = $login_key;
		$this->write->feedback($feedback);
	}

	public function check_login_key() {
		$this->load->model(array('login_sessions'));
		$this->auth->login_key();
		$feedback['error'] = false;
		$this->write->feedback($feedback);
	}

	public function get_main_data() {
		$this->auth->login_key();
	}
}
