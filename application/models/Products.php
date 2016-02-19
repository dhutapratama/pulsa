<?php

/*
$product['product_id']
$product['operator_id']
$product['tipe_pembelian']
$product['kode_sms']
$product['harga']
$product['keterangan']
*/

class Products extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function insert($data = array())
	{
		$this->db->insert('apps_products', $data);
	}

	public function get()
	{
		$query = $this->db->get('apps_products');
		return $query->result();
	}

	public function get_by_id($id = '')
	{
		$query = $this->db->select('*')->from('apps_products')
				->where('product_id', $id)
				->get();
		return $query->row();
	}

	public function delete($id = '')
	{
		$this->db->where('product_id', $id);
		$this->db->delete('apps_products');
	}

	public function update($id = '', $data = array())
	{
		$this->db->where('product_id', $id);
		$this->db->update('apps_products', $data);
	}

	public function get_by_operator_id($operator_id = '')
	{
		$query = $this->db->select('*')->from('apps_products')
				->where('operator_id', $operator_id)
				->get();
		return $query->result();
	}
}