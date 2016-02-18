<?php

/*
$operator['operator_id']
$operator['nama']
$operator['prefix_number']
*/

class Operators extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_operators', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_operators');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_operators')
				->where('operator_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('operator_id', $id);
		$this->db->delete('apps_operators');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('operator_id', $id);
		$this->db->update('apps_operators', $data);
	}
}