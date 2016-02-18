<?php

/*
$login_session['login_session_id']
$login_session['login_key']
$login_session['date']
$login_session['member_id']
$login_session['saldo_id']
$login_session['device_id']
*/

class Login_sessions extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_login_sessions', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_login_sessions');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_login_sessions')
				->where('login_session_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('login_session_id', $id);
		$this->db->delete('apps_login_sessions');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('login_session_id', $id);
		$this->db->update('apps_login_sessions', $data);
	}

	public function get_by_login_key($login_key = '')
	{
		$query = $this->db->select('*')->from('apps_login_sessions')
				->where('login_key', $login_key)
				->get();
		return $query->row();
	}
}