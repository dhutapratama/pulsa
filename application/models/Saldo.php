<?php

/*
$saldo['saldo_id']
$saldo['member_id']
$saldo['amount']
$saldo['last_update']
*/

class Saldo extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_saldo', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_saldo');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_saldo')
				->where('saldo_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('saldo_id', $id);
		$this->db->delete('apps_saldo');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('saldo_id', $id);
		$this->db->update('apps_saldo', $data);
	}

	public function get_by_member_id($member_id = '')
	{
		$query = $this->db->select('*')->from('apps_saldo')
				->where('member_id', $member_id)
				->get();
		return $query->row();
	}

	public function update_by_member_id($member_id = '', $data = array())
	{
		$this->db->where('member_id', $member_id);
		$this->db->update('apps_saldo', $data);
	}
}