<?php

/*
$member['member_id']
$member['name']
$member['ym_username']
$member['ym_password']
$member['handphone']
$member['pin']
$member['last_update']
*/

class Members extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_members', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_members');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_members')
				->where('member_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('member_id', $id);
		$this->db->delete('apps_members');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('member_id', $id);
		$this->db->update('apps_members', $data);
	}

	public function get_by_ym_login($ym_username = '', $ym_password = '', $pin = '')
	{
		$query = $this->db->select('*')->from('apps_members')
				->where('ym_username', $ym_username)
				->where('ym_password', $ym_password)
				->get();
		return $query->row();
	}

	public function get_by_ym_username($ym_username = '')
	{
		$query = $this->db->select('*')->from('apps_members')
				->where('ym_username', $ym_username)
				->get();
		return $query->row();
	}
}