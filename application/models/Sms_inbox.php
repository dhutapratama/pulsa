<?php

/*
$sms_inbox['sms_inbox_id']
$sms_inbox['number']
$sms_inbox['content']
$sms_inbox['date']
*/

class Sms_inbox extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('api_sms_inbox', $data);
	}

	public function get()
	{
		$query = $this->db->get('api_sms_inbox');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('api_sms_inbox')
				->where('sms_inbox_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('sms_inbox_id', $id);
		$this->db->delete('api_sms_inbox');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('sms_inbox_id', $id);
		$this->db->update('api_sms_inbox', $data);
	}
}