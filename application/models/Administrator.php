<?php

/*
$administrator['admin_username']
$administrator['admin_password']
*/

class Administrator extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('administrator', $data);
	}

	public function get()
	{
		$query = $this->db->get('administrator');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('administrator')
				->where('administrator_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('administrator_id', $id);
		$this->db->delete('administrator');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('administrator_id', $id);
		$this->db->update('administrator', $data);
	}

	public function update_password($data = array())
	{
		$this->db->update('administrator', $data);
	}

	public function get_username($admin_username = '')
	{
		$query = $this->db->select('*')->from('administrator')
				->where('admin_username', $admin_username)
				->get();
		return $query->row();
	}
}