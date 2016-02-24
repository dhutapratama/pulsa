<?php

/*
$transaction['transaction_id']
$transaction['member_id']
$transaction['transaction_type_id']
$transaction['status']
$transaction['amount']
$transaction['date']
$transaction['balance']
$transaction['nomor_hp']
*/

class Transactions extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_transactions', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_transactions')
			->order_by('transaction_id', 'desc')
			->limit(0, 30);
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_transactions')
				->where('transaction_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('transaction_id', $id);
		$this->db->delete('apps_transactions');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('transaction_id', $id);
		$this->db->update('apps_transactions', $data);
	}

	public function get_by_member_id($member_id = '')
	{
		$query = $this->db->select('*')->from('apps_transactions')
				->where('member_id', $member_id)
				->get();
		return $query->result();
	}
}