<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');

		if (!$this->session->has_userdata('login_key') && $this->uri->segment(2) != "login") {
			redirect('admin/login');
		}

		if ($this->session->has_userdata('login_key') && $this->uri->segment(2) == "login") {
			redirect('admin');
		}

	}
	public function index()
	{
		$this->load->model(array('products', 'operators'));
		$products_data = $this->products->get();
		$output['products'] = $products_data;

		$this->load->view('web/header', $output);
		$this->load->view('web/table_pulsa');
	}

	public function add_pulsa() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$this->load->model(array('products'));
			$product['operator_id']		= $this->input->post('provider');
			$product['tipe_pembelian']	= $this->input->post('tipe_pembelian');
			$product['kode_sms']		= $this->input->post('kode_sms');
			$product['harga']			= $this->input->post('harga');
			$product['keterangan']		= $this->input->post('nama');
			$this->products->insert($product);
			redirect('admin');
		} else {
			$this->load->model(array('operators'));
			$operators_data = $this->operators->get();

			$output['operators'] = $operators_data;

			$this->load->view('web/header', $output);
			$this->load->view('web/form_add_pulsa');
		}
	}

	public function ubah_pulsa($product_id = '') {
		if ($product_id == '') {
			redirect('admin');
		} else {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$this->load->model(array('products'));
				$product['operator_id']		= $this->input->post('provider');
				$product['tipe_pembelian']	= $this->input->post('tipe_pembelian');
				$product['kode_sms']		= $this->input->post('kode_sms');
				$product['harga']			= $this->input->post('harga');
				$product['keterangan']		= $this->input->post('nama');
				$this->products->update($product_id, $product);
				redirect('admin');
			} else {
				$this->load->model(array('products', 'operators'));

				$operators_data = $this->operators->get();
				$products_data = $this->products->get_by_id($product_id);
				if (!$product_id) {
					$output['error'] = "Produk pulsa tidak ditemukan";
					redirect('admin');
				}

				$output['operators'] = $operators_data;
				$output['products'] = $products_data;

				$this->load->view('web/header', $output);
				$this->load->view('web/form_edit_pulsa');
			}
		}
	}

	public function delete_pulsa($product_id = '') {
		$this->load->model(array('products'));
		$this->products->delete($product_id);
		redirect('admin');
	}

	public function change_password()
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$this->load->model(array('administrator'));
			$this->load->library(array('form_validation'));

			$this->form_validation->set_rules('password', 'Password', 'required');
			$this->form_validation->set_rules('repassword', 'Ulangi Password', 'required|matches[password]');

			if ($this->form_validation->run() == FALSE) {
				$output['alert'] = validation_errors('', '');
            } else {
                $password['admin_password']	= md5($this->input->post('password'));
				$this->administrator->update_password($password);
				$output['alert'] = "Sukses ganti password.";
            }
			
			$this->load->view('web/header', $output);
			$this->load->view('web/change_password');
		} else {
			$this->load->view('web/header');
			$this->load->view('web/change_password');
		}
	}

	public function login() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$this->load->model('administrator');

			$administrator_data = $this->administrator->get_username($this->input->post('username'));
			if ($administrator_data->admin_password == md5($this->input->post('password'))) {
				$this->session->set_userdata('login_key', md5(time()));
				redirect('admin');
			} else {
				$this->load->view('web/login');
			}
		} else {
			$this->load->view('web/login');
		}
	}
}
