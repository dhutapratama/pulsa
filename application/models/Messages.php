<?php

/*
$message['message_id']
$message['member_id']
$message['message']
$message['date']
*/

class Messages extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_messages', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_messages');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_messages')
				->where('message_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('message_id', $id);
		$this->db->delete('apps_messages');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('message_id', $id);
		$this->db->update('apps_messages', $data);
	}
}