<?php

/*
$transaction_type['transaction_type_id']
$transaction_type['transaction_name']
$transaction_type['fund_type']
*/

class Transaction_types extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_transaction_types', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_transaction_types');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_transaction_types')
				->where('transaction_type_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('transaction_type_id', $id);
		$this->db->delete('apps_transaction_types');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('transaction_type_id', $id);
		$this->db->update('apps_transaction_types', $data);
	}
}