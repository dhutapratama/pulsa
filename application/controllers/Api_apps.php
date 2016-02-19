<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_apps extends CI_Controller {

	public function __construct() {
        parent::__construct();
        //$this->load->library();
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
        	redirect();
        }
    }

	public function get_login_key() {
		$this->load->model(array('members', 'saldo'));

		$param = array('device_id' => 'required');
		$input = $this->auth->input($param);

		$member_data = $this->members->get_by_device_id($input['device_id']);
		if (!$member_data) {
			// Create new membership
			$date = date('Y-m-d H:i:s');

			$member['device_id']			= $input['device_id'];
			$member['is_email_verified']	= '';
			$member['is_phone_verified']	= '';
			$member['last_update']			= $date;
			$this->members->insert($member);
			$member_id = $this->db->insert_id();

			$saldo['member_id']		= $member_id;
			$saldo['amount']		= 0;
			$saldo['last_update']	= $date;
			$this->saldo->insert($saldo);
			$saldo_id = $this->db->insert_id();

			$this->auth->add_login_session($member_id, $saldo_id, $input['device_id']);
		}
		$saldo_data = $this->saldo->get_by_member_id($member_data->member_id);
		$this->auth->add_login_session($member_data->member_id, $saldo_data->saldo_id, $input['device_id']);
	}

	public function check_login_key() {
		$this->load->model(array('login_sessions'));
		$this->auth->login_key();
		$feedback['error'] = false;
		$this->write->feedback($feedback);
	}

	public function get_main_data() {
		$this->load->model(array('saldo', 'transactions', 'transaction_types'));
		$login_data			= $this->auth->login_key();
		$saldo_data			= $this->saldo->get_by_id($login_data->saldo_id);
		$transaction_data	= $this->transactions->get_by_member_id($login_data->member_id);

		if ($transaction_data) {
			$i = 0;
			$transactions = array();
			foreach ($transaction_data as $value) {
				$transaction_type_data = $this->transaction_types->get_by_id($value->transaction_type_id);
				$transactions[$i]['type']		= $transaction_type_data->transaction_name;
				$transactions[$i]['date']		= date('d M Y H:i', strtotime($value->date));
				$transactions[$i]['status']		= $value->status;
				$transactions[$i]['amount']		= "Rp ".number_format($value->amount, 0, '', '.');
				$transactions[$i]['balance']	= "Rp ".number_format($value->balance, 0, '', '.');
				$i++;
			}
		} else {
			$transactions = false;
		}

		$feedback['error'] 					= false;
		$feedback['data']['saldo']			= "Rp ".number_format($saldo_data->amount, 0, '', '.');
		$feedback['data']['transactions_data'] = $transactions ? true : false;
		$feedback['data']['transactions'] 	= $transactions;

		$this->write->feedback($feedback);
	}

	public function get_daftar_harga() {
		$this->load->model(array('products', 'operators'));
		$login_data	= $this->auth->login_key();
		$products_data = $this->products->get();

		if ($products_data) {
			$i = 0;
			$products = array();
			foreach ($products_data as $value) {
				$operator_data = $this->operators->get_by_id($value->operator_id);
				$products[$i]['operator']	= $operator_data->nama;
				$products[$i]['produk']		= $value->tipe_pembelian;
				$products[$i]['harga']		= "Rp ".number_format($value->harga, 0, '', '.');
				$products[$i]['keterangan']	= $value->keterangan;
				$i++;
			}
		} else {
			$products = false;
		}

		$feedback['error'] 					= false;
		$feedback['data']['products_data']	= $products ? true : false;
		$feedback['data']['products']		= $products;

		$this->write->feedback($feedback);
	}

	public function cek_nomor() {
		$this->load->model(array('products', 'operators', 'prefix'));

		$login_data	= $this->auth->login_key();
		$post_input = array('prefix' => 'required|numeric');
		$input = $this->auth->input($post_input);

		$prefix_data = $this->prefix->get_by_number($input['prefix']);
		if (!$prefix_data) {
			$this->write->error("Prefix nomor handphone salah");
		}

		$operators_data = $this->operators->get_by_id($prefix_data->operator_id);
		if (!$operators_data) {
			$this->write->error("Operator tidak ada untuk nomor ini");
		}

		$products_data = $this->products->get_by_operator_id($prefix_data->operator_id);
		if (!$products_data) {
			$this->write->error("Tidak ada produk yang dijual untuk operator ini");
		}

		if ($products_data) {
			$i = 0;
			$products = array();
			foreach ($products_data as $value) {
				$operator_data = $this->operators->get_by_id($value->operator_id);
				$products[$i]['operator']	= $operator_data->nama;
				$products[$i]['kode']		= $value->kode_sms;
				$products[$i]['produk']		= $value->tipe_pembelian;
				$products[$i]['harga']		= "Rp ".number_format($value->harga, 0, '', '.');
				$products[$i]['keterangan']	= $value->keterangan;
				$i++;
			}
		} else {
			$products = false;
		}

		$feedback['error'] 					= false;
		$feedback['data']['operator']		= $operators_data->nama;
		$feedback['data']['products_data']	= $products ? true : false;
		$feedback['data']['products']		= $products;

		$this->write->feedback($feedback);
	}

	public function konfirmasi_pembelian() {
		$this->load->model(array('products', 'operators', 'prefix', 'transactions', 'saldo'));

		$login_data	= $this->auth->login_key();
		$post_input = array('nomor' => 'required|numeric', 'kode_sms' => 'required');
		$input = $this->auth->input($post_input);

		$prefix_3 = substr($input['nomor'], 0, 3);
		$prefix_4 = substr($input['nomor'], 0, 4);


		$prefix_data = $this->prefix->get_by_number($prefix_4);
		if (!$prefix_data) {
			$prefix_data = $this->prefix->get_by_number($prefix_3);
			if (!$prefix_data) {
				$this->write->error("Nomor prefix tidak ditemukan");
			}
		}

		$operators_data = $this->operators->get_by_id($prefix_data->operator_id);
		if (!$operators_data) {
			$this->write->error("Operator tidak ada untuk nomor ini");
		}

		$products_data = $this->products->get_by_operator_id_kode_sms($operators_data->operator_id, $input['kode_sms']);
		if (!$products_data) {
			$this->write->error("Nomor, Operator dan Kode tidak sama");
		}

		// Proses transaksi

		$randomizer = rand(0,2);
		$status[0] = "Pending";
		$status[1] = "Sukses";
		$status[2] = "Gagal";
		$saldo_data = $this->saldo->get_by_id($login_data->saldo_id);

		$transaction['transaction_id']		= md5(time().rand(1000, 9999));
		$transaction['member_id']			= $login_data->member_id;
		$transaction['transaction_type_id']	= 2;
		$transaction['status']				= $status[$randomizer];
		$transaction['amount']				= $products_data->harga;
		$transaction['date']				= date("Y-m-d H:i:s");
		$transaction['balance']				= $saldo_data->amount;
		$this->transactions->insert($transaction);

		$feedback['error'] 				= false;
		$feedback['data']['message']	= "Transaksi ".$status[$randomizer];
		$feedback['data']['refference']	= "TRX : ".md5(time());
		$this->write->feedback($feedback);
	}
}
