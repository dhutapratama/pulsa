<?php

/*
$prefix['prefix_id']
$prefix['operator_id']
$prefix['number']
*/

class prefix extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_prefix', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_prefix');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_prefix')
				->where('prefix_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('prefix_id', $id);
		$this->db->delete('apps_prefix');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('prefix_id', $id);
		$this->db->update('apps_prefix', $data);
	}

	public function get_by_number($number = '')
	{
		$query = $this->db->select('*')->from('apps_prefix')
				->where('number', $number)
				->get();
		return $query->row();
	}
}