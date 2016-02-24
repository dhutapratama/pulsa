<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_apps extends CI_Controller {

	private $consumer_key 	= 'dj0yJmk9VXpraU05Tko3NGNpJmQ9WVdrOWVUaE5Tbmw0TTJNbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD1kNg--';
	private $secret_key 	= '13b7d68db3f35f230363fdc9ae14d6aad5d3db5a';
	private $ym_center		= 'dutasms';

	public function __construct() {
        parent::__construct();
        //$this->load->library();
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
        	redirect();
        }
    }

    public function login() {
    	$param = array('ym_username' => 'required', 'ym_password' => 'required', 'pin' => 'required');
		$input = $this->auth->input($param);

		$this->load->model(array('members', 'saldo'));
		$member_data = $this->members->get_by_ym_username($input['ym_username']);
		if ($member_data) {
			$login_data = $this->members->get_by_ym_login($input['ym_username'], $input['ym_password'], $input['pin']);
			if (!$login_data) {
				$this->write->error("YM Password / PIN anda salah");
			}
			$this->_cek_id_ym(true, $login_data);
		} else {
			$this->_cek_id_ym(false);
		}
    }

    private function _cek_id_ym($is_member = false, $login_data = array()) {
    	$param = array('ym_username' => 'required', 'ym_password' => 'required', 'pin' => 'required');
		$input = $this->auth->input($param);

		$this->load->library('jymengine');
		$this->jymengine->initialize($this->consumer_key, $this->secret_key, $input['ym_username'], $input['ym_password']);

		if (!$this->jymengine->fetch_request_token()) {
			$this->write->error("Akun YM anda terkunci, Mohon tunggu 1x24 Jam");
		}
		if (!$this->jymengine->fetch_access_token()) {
			$this->write->error("Server error silahkan coba beberapa saat lagi");
		}
		if (!$this->jymengine->signon('AyoIsiPulsa')) {
			$this->write->error("Tidak dapat masuk");
		}
		
		// Berhasil masuk
		$signon_data = $this->jymengine->get_signon();
		$token_data = $this->jymengine->get_token();
/*
		// Kirim data pin
		$this->jymengine->send_message($this->ym_center, json_encode('S.'.$input['pin']));
		sleep(3);

		// Cek PIN
		$no_reply = false;
		if (isset($resp))
		{	
			$resp = $this->jymengine->fetch_long_notification($seq+1);
			foreach ($resp as $row)
			{
				foreach ($row as $key => $val)
				{
					if ($key == 'message') //incoming message
					{
						if ($val['sender'] == $ym_center) {
							if (stripos($val['msg'], 'PIN yang Anda masukkan salah') === false){
								$this->write->error("Pin anda salah");
							} else {
								//$saldo = 
							}
						}
					}
				}
			}
		} else {
			$no_reply = true;
		}

		
		if ($no_reply) {
			$this->write->error("YM Anda tidak terdaftar di server kami.");
		}
*/
		if (!$is_member) {
			$member['name']			= 'AyoIsiPulsa';
			$member['ym_username']	= $input['ym_username'];
			$member['ym_password']	= $input['ym_password'];
			$member['handphone']	= '081';
			$member['pin']			= $input['pin'];
			$member['last_update']	= date("Y-m-d H:i:s");
			$this->members->insert($member);
			$member_id = $this->db->insert_id();

			$saldo['member_id']		= $member_id;
			$saldo['amount']		= 0;
			$saldo['last_update']	= date("Y-m-d H:i:s");
			$this->saldo->insert($saldo);
			$saldo_id = $this->db->insert_id();
		} else {
			$member_id = $login_data->member_id;
			$saldo_data = $this->saldo->get_by_member_id($member_id);
			$saldo_id = $saldo_data->saldo_id;
		}
		$this->auth->add_login_session($member_id, $saldo_id, serialize($signon_data), serialize($token_data));
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

	public function pembelian() {
		$this->load->model(array('products', 'operators', 'prefix'));
		$this->load->library('jymengine');

		$login_data	= $this->auth->login_key();
		$post_input = array('prefix' => 'required|numeric');
		$input = $this->auth->input($post_input);

		$member_data = $this->members->get_by_id($login_data->member_id);

		$this->jymengine->initialize($this->consumer_key, $this->secret_key, $member_data->ym_username, $member_data->ym_password);
		$this->jymengine->set_signon(unserialize($login_data->oauth_session));
		$this->jymengine->set_token(unserialize($login_data->oauth_token));

		$this->jymengine->send_message($this->ym_center, json_encode('KODE.NOHP.'.$input['pin']));

		$feedback['error'] 					= false;
		$feedback['data']['operator']		= "Transaksi di tracking";

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

		//$transaction['transaction_id']		= md5(time().rand(1000, 9999));
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
