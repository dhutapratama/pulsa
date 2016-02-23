<?php

/*
$member['member_id']
$member['device_id']
$member['name']
$member['email']
$member['password']
$member['phone']
$member['is_email_verified']
$member['is_phone_verified']
$member['last_update']
$member['ym_username']
$member['ym_password']
$member['pin']
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

	public function get_by_device_id($device_id = '')
	{
		$query = $this->db->select('*')->from('apps_members')
				->where('device_id', $device_id)
				->get();
		return $query->row();
	}
}