<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_apps extends CI_Controller {

	private $consumer_key 	= 'dj0yJmk9VXpraU05Tko3NGNpJmQ9WVdrOWVUaE5Tbmw0TTJNbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD1kNg--';
	private $secret_key 	= '13b7d68db3f35f230363fdc9ae14d6aad5d3db5a';
	//private $ym_center		= 'one.pulsa';
	private $ym_center		= 'misterh2h';

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
			$this->_cek_id_ym(true, $member_data);
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
			$this->write->error("Password Anda Salah / YM Terkunci");
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

		// Kirim Cek Saldo
		$this->jymengine->send_message($this->ym_center, json_encode('S.'.$input['pin']));

		$resp = $this->jymengine->fetch_long_notification(1);

		if (!$resp) {
			$this->write->error("Anda tidak terdaftar. Baca Panduan Pendaftaran.");
		}

		$no_reply = true;
		if (isset($resp))
		{	
			foreach ($resp as $row)
			{
				foreach ($row as $key => $val)
				{
					if ($key == 'message') //incoming message
					{
						if ($val['sender'] == $this->ym_center) {
							if (stripos($val['msg'], 'PIN yang Anda masukkan salah') !== false){
								$this->write->error("PIN Anda Salah");
							} else {
								if (stripos($val['msg'], 'Account Anda tidak aktif') !== false){
									$this->write->error("Account anda diblokir, Hub CS.");
								}

								if (stripos($val['msg'], 'DIBAYAR') !== false){
									$arr_message = explode("Rp.", $val['msg']);
									$arr_message = explode(",", $arr_message[1]);

									$saldos = str_replace(".", "", $arr_message[0]);
									$jumlah_saldo = str_replace(",", "", $saldos);
									$jenis_saldo = "hutang";
								} else if (stripos($val['msg'], 'Debet:') !== false){
									$arr_message = explode(",", $val['msg']);
									$arr_message = explode("Rp.", $arr_message[0]);
									$saldos = str_replace(".", "", $arr_message[1]);
									$jumlah_saldo = str_replace(",", "", $saldos);
									$jenis_saldo = "saldo";
								}

								if ($is_member) {
									// Update
									$member['pin'] = $input['pin'];
									$this->members->update($login_data->member_id, $member);
									$login_data = $this->members->get_by_id($login_data->member_id);

									$this->load->model(array('messages'));
									$message['member_id']	= $login_data->member_id;
									$message['message']		= $val['msg'];
									$message['date']		= date('Y-m-d H:i:s');
									$message['is_read']		= 1;
									$this->messages->insert($message);
								}
							}
						}
						$no_reply = false;
					}
				}
			}
		}

		if ($no_reply) {
			$this->write->error("YM Anda tidak terdaftar di server kami.");
		}

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
			$saldo['amount']		= $jumlah_saldo;
			$saldo['last_update']	= date("Y-m-d H:i:s");
			$saldo['jenis_saldo']	= $jenis_saldo;
			$this->saldo->insert($saldo);
			$saldo_id = $this->db->insert_id();
		} else {
			$member_id = $login_data->member_id;
			$saldo_data = $this->saldo->get_by_member_id($member_id);
			$saldo_id = $saldo_data->saldo_id;

			if ($jenis_saldo) {
				$saldo_update['amount']			= $jumlah_saldo;
				$saldo_update['jenis_saldo']	= $jenis_saldo;
				$saldo_update['last_update']	= date('Y-m-d H:i:s');
				$this->saldo->update_by_member_id($login_data->member_id, $saldo_update);
			}
		}
		$this->auth->add_login_session($member_id, $saldo_id, serialize($token_data), serialize($signon_data));
    }

	public function check_login_key() {
		$this->load->model(array('login_sessions'));
		$this->auth->login_key();
		$feedback['error'] = false;
		$this->write->feedback($feedback);
	}

	public function get_main_data() {
		$this->load->library('jymengine');
		$this->load->model(array('members', 'saldo', 'transactions', 'transaction_types', 'operators', 'products', 'messages', 'login_sessions'));
		$login_data			= $this->auth->login_key();
		$member_data		= $this->members->get_by_id($login_data->member_id);

		$saldo_data			= $this->saldo->get_by_id($login_data->saldo_id);
		$transaction_data	= $this->transactions->get_by_member_id($login_data->member_id);

		if ($transaction_data) {
			$i = 0;
			$transactions = array();
			foreach ($transaction_data as $value) {
				$transaction_type_data = $this->transaction_types->get_by_id($value->transaction_type_id);
				$operators_data = $this->operators->get_by_id($value->operator_id);
				$products_data = $this->products->get_by_id($value->product_id);

				$transactions[$i]['type']		= $transaction_type_data->transaction_name;
				$transactions[$i]['date']		= date('d M Y H:i', strtotime($value->date));
				$transactions[$i]['nomor']		= $value->nomor_hp;
				$transactions[$i]['provider']	= $operators_data->nama;
				$transactions[$i]['produk']		= $products_data->keterangan;
				$transactions[$i]['status']		= $value->status;
				$transactions[$i]['amount']		= "Rp ".number_format($value->amount, 0, '', '.');
				$transactions[$i]['balance']	= "Rp ".number_format($value->balance, 0, '', '.');
				$i++;
			}
		} else {
			$transactions = false;
		}

		$feedback['error'] 						= false;
		$feedback['data']['jenis_saldo'] 		= $saldo_data->jenis_saldo;
		$feedback['data']['saldo']				= "Rp ".number_format($saldo_data->amount, 0, '', '.');
		$feedback['data']['nama'] 				= $member_data->name;
		$feedback['data']['transactions_data'] 	= $transactions ? true : false;
		$feedback['data']['transactions'] 		= $transactions;

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
			$prefix_data = array('operator_id' => 8);
			$prefix_data = (object)$prefix_data;
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
		$this->load->model(array('members', 'saldo', 'transactions', 'prefix', 'operators', 'products'));
		$this->load->library('jymengine');

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

		$member_data = $this->members->get_by_id($login_data->member_id);
		$this->jymengine->initialize($this->consumer_key, $this->secret_key, $member_data->ym_username, $member_data->ym_password);
		$this->jymengine->set_signon(unserialize($login_data->oauth_session));
		$this->jymengine->set_token(unserialize($login_data->oauth_token));
		$this->jymengine->send_message($this->ym_center, json_encode($input['kode_sms'].'.'.$input['nomor'].'.'.$member_data->pin));
		

		$resp = $this->jymengine->fetch_long_notification($login_data->ym_sequence + 1);

		if (!$resp) {
			$resp = $this->jymengine->fetch_long_notification($login_data->ym_sequence);

			if (!$resp) {
				$this->write->error("Sesi anda berakhir, Mohon login kembali");
			}
		}

		if (isset($resp))
		{	
			$jenis_saldo = false;
			foreach ($resp as $row)
			{
				foreach ($row as $key => $val)
				{
					if ($key == 'message') //incoming message
					{
						if ($val['sender'] == $this->ym_center) {
							$this->load->model(array('messages'));
							$message['member_id']	= $login_data->member_id;
							$message['message']		= $val['msg'];
							$message['date']		= date('Y-m-d H:i:s');
							$message['is_read']		= 1;
							$this->messages->insert($message);

							if (stripos($val['msg'], 'tdk kami proses') !== false){
								$status = "Gagal";
							} else {
								$status = "Sukses";
							}

							$login_session['ym_sequence']	= $val['sequence']; 
							$this->login_sessions->update($login_data->login_session_id, $login_session);
						}
					}
				}
			}
		}

		$this->jymengine->send_message($this->ym_center, json_encode('S.'.$member_data->pin));
		
		$resp = $this->jymengine->fetch_long_notification($login_data->ym_sequence + 1);

		if (!$resp) {
			// Re login w
			$this->jymengine->send_message($this->ym_center, json_encode('S.'.$member_data->pin));
			$resp = $this->jymengine->fetch_long_notification($login_data->ym_sequence);

			if (!$resp) {
				$this->write->error("Sesi anda berakhir, Mohon login kembali");
			}
		}

								
		if (isset($resp))
		{	
			$jenis_saldo = false;
			foreach ($resp as $row)
			{
				foreach ($row as $key => $val)
				{
					if ($key == 'message') //incoming message
					{
						if ($val['sender'] == $this->ym_center) {
							if (stripos($val['msg'], 'Account Anda tidak aktif') !== false){
								$this->write->error("Account anda diblokir, Hub CS.");
							}

							if (stripos($val['msg'], 'PIN') !== false){
								$this->write->error("PIN anda Salah!");
							} else {
								$this->load->model(array('messages'));
								$message['member_id']	= $login_data->member_id;
								$message['message']		= $val['msg'];
								$message['date']		= date('Y-m-d H:i:s');
								$message['is_read']		= 1;
								$this->messages->insert($message);

								if (stripos($val['msg'], 'DIBAYAR') !== false){
									$arr_message = explode("Rp.", $val['msg']);
									$arr_message = explode(",", $arr_message[1]);

									$saldo = str_replace(".", "", $arr_message[0]);
									$saldo = str_replace(",", "", $saldo);
									$jenis_saldo = "hutang";
								} else if (stripos($val['msg'], 'Debet:') !== false){
									$arr_message = explode(",", $val['msg']);
									$arr_message = explode("Rp.", $arr_message[0]);
									$saldo = str_replace(".", "", $arr_message[1]);
									$saldo = str_replace(",", "", $saldo);
									$jenis_saldo = "saldo";
								}

								if ($jenis_saldo) {
									$saldo_update['amount']			= $saldo;
									$saldo_update['jenis_saldo']	= $jenis_saldo;
									$saldo_update['last_update']	= date('Y-m-d H:i:s');
									$this->saldo->update_by_member_id($login_data->member_id, $saldo_update);
								}
								$login_session['ym_sequence']	= $val['sequence']; 
								$this->login_sessions->update($login_data->login_session_id, $login_session);
							}
						}
					}
				}
			}
		}

		$saldo_data = $this->saldo->get_by_id($login_data->saldo_id);
		if (!isset($status)) {
			$status = "Gagal";
		}

		if ($status == "Sukses") {
			$sisa_saldo = $saldo_data->amount - $products_data->harga;
		} else {
			$sisa_saldo = $saldo_data->amount;
		}
		

		//$transaction['transaction_id']		= md5(time().rand(1000, 9999));
		$transaction['member_id']			= $login_data->member_id;
		$transaction['transaction_type_id']	= 2;
		$transaction['status']				= $status;
		$transaction['amount']				= $products_data->harga;
		$transaction['date']				= date("Y-m-d H:i:s");
		$transaction['balance']				= $sisa_saldo;
		$transaction['nomor_hp']			= $input['nomor'];
		$transaction['operator_id']			= $operators_data->operator_id;
		$transaction['product_id']			= $products_data->product_id;
		$this->transactions->insert($transaction);


		$feedback['error'] 					= false;
		$feedback['data']['message']		= "Transaksi pembelian anda ".$status;
		$feedback['data']['refference']		= "TRX : ".md5(time());

		$this->write->feedback($feedback);
	}

	public function save_nama() {
		$this->load->model(array('members'));

		$login_data	= $this->auth->login_key();
		$post_input = array('nama' => 'required');
		$input = $this->auth->input($post_input);

		$member['name'] = $input['nama'];
		$this->members->update($login_data->member_id, $member);

		$feedback['error'] 					= false;
		$feedback['data']['message']		= "Berhasil";
		$this->write->feedback($feedback);
	}

	public function save_pin() {
		$this->load->model(array('members'));

		$login_data	= $this->auth->login_key();
		$post_input = array('pin' => 'required');
		$input = $this->auth->input($post_input);

		$member['pin'] = $input['pin'];
		$this->members->update($login_data->member_id, $member);

		$feedback['error'] 					= false;
		$feedback['data']['message']		= "Berhasil";
		$this->write->feedback($feedback);
	}

	private function _init_ym($login_data) {
		$this->load->model('members');
		$member_data = $this->members->get_by_id($login_data->member_id);
		$this->jymengine->initialize($this->consumer_key, $this->secret_key, $member_data->ym_username, $member_data->ym_password);
		$this->jymengine->set_signon(unserialize($login_data->oauth_session));
		$this->jymengine->set_token(unserialize($login_data->oauth_token));
	}

	public function get_pesan() {
		$this->load->model(array('messages'));
		$login_data	= $this->auth->login_key();

		$messages_data = $this->messages->get_latest($login_data->member_id);

		if ($messages_data) {
			$i = 0;
			$messages = array();
			foreach ($messages_data as $value) {
				$messages[$i]['message']	= $value->message;
				$messages[$i]['date']		= date('d M Y H:i', strtotime($value->date));
				$i++;
			}
		} else {
			$messages = false;
		}

		$feedback['error'] 					= false;
		$feedback['data']['messages_data']	= $messages ? true : false;
		$feedback['data']['messages']		= $messages;

		$this->write->feedback($feedback);
	}

	public function get_info() {
		$login_data	= $this->auth->login_key();

		$this->load->library('jymengine');
		$this->load->model(array('messages', 'login_sessions'));

		$this->_init_ym($login_data);

		$this->jymengine->send_message($this->ym_center, json_encode('OLAPP'));
		$this->jymengine->send_message($this->ym_center, json_encode('OLAP2'));
		$olapp = "-";
		$olap2 = "-";
		$resp = $this->jymengine->fetch_long_notification($login_data->ym_sequence + 1);

		if (isset($resp))
		{	
			$jenis_saldo = false;
			foreach ($resp as $row)
			{
				foreach ($row as $key => $val)
				{
					if ($key == 'message') //incoming message
					{
						if ($val['sender'] == $this->ym_center) {
							if (stripos($val['msg'], 'Account Anda tidak aktif') !== false){
								$this->write->error("Account anda diblokir, Hub CS.");
							}

							$this->load->model(array('messages'));
							$message['member_id']	= $login_data->member_id;
							$message['message']		= $val['msg'];
							$message['date']		= date('Y-m-d H:i:s');
							$message['is_read']		= 1;
							$this->messages->insert($message);

							if (stripos($val['msg'], 'OL:') !== false){
								$olapp = str_replace("OL:", "", $val['msg']);
							}

							if (stripos($val['msg'], 'OP:') !== false){
								$olap2 = str_replace("OP:", "", $val['msg']);
							}

							$login_session['ym_sequence']	= $val['sequence']; 
							$this->login_sessions->update($login_data->login_session_id, $login_session);
						}
					}
				}
			}
		}

		$feedback['error'] 				= false;
		$feedback['data']['olapp']		= $olapp;
		$feedback['data']['olap2']		= $olap2;

		$this->write->feedback($feedback);
	}

	public function create_tiket() {
		$login_data	= $this->auth->login_key();

		$this->load->library('jymengine');
		$this->load->model(array('messages', 'login_sessions', 'members'));

		$this->_init_ym($login_data);

		$member_data = $this->members->get_by_id($login_data->member_id);
		$param = array('tiket' => 'required');
		$input = $this->auth->input($param);

		$this->jymengine->send_message($this->ym_center, json_encode('TIKET.'.$input['tiket'].'.'.$member_data->pin));
		$resp = $this->jymengine->fetch_long_notification($login_data->ym_sequence + 1);

		if (!$resp) {
			$this->jymengine->send_message($this->ym_center, json_encode('TIKET.'.$input['tiket'].'.'.$member_data->pin));
			
			$resp = $this->jymengine->fetch_long_notification($login_data->ym_sequence);

			if (!$resp) {
				$this->write->error("Server tidak merespon, mungkin dia sedang keluar");
			}
		}

		if (isset($resp))
		{	
			$jenis_saldo = false;
			foreach ($resp as $row)
			{
				foreach ($row as $key => $val)
				{
					if ($key == 'message') //incoming message
					{
						if ($val['sender'] == $this->ym_center) {
							if (stripos($val['msg'], 'Account Anda tidak aktif') !== false){
								$this->write->error("Account anda diblokir, Hub CS.");
							}

							$this->load->model(array('messages'));
							$message['member_id']	= $login_data->member_id;
							$message['message']		= $val['msg'];
							$message['date']		= date('Y-m-d H:i:s');
							$message['is_read']		= 1;
							$this->messages->insert($message);

							if (stripos($val['msg'], 'A/N') !== false){
								$tiket = $val['msg'];
							}

							$login_session['ym_sequence']	= $val['sequence']; 
							$this->login_sessions->update($login_data->login_session_id, $login_session);
						}
					}
				}
			}
		}

		$feedback['error'] 				= false;
		$feedback['data']['tiket']		= $tiket;

		$this->write->feedback($feedback);
	}

	public function create_komplain() {
		$login_data	= $this->auth->login_key();

		$this->load->library('jymengine');
		$this->load->model(array('messages', 'login_sessions', 'members'));

		$this->_init_ym($login_data);

		$member_data = $this->members->get_by_id($login_data->member_id);
		$param = array('info' => 'required');
		$input = $this->auth->input($param);

		$this->jymengine->send_message($this->ym_center, json_encode('INFO.'.$input['info']));
		$resp = $this->jymengine->fetch_long_notification($login_data->ym_sequence);

		if (isset($resp))
		{	
			$jenis_saldo = false;
			foreach ($resp as $row)
			{
				foreach ($row as $key => $val)
				{
					if ($key == 'message') //incoming message
					{
						if ($val['sender'] == $this->ym_center) {
							if (stripos($val['msg'], 'Account Anda tidak aktif') !== false){
								$this->write->error("Account anda diblokir, Hub CS.");
							}
							
							$this->load->model(array('messages'));
							$message['member_id']	= $login_data->member_id;
							$message['message']		= $val['msg'];
							$message['date']		= date('Y-m-d H:i:s');
							$message['is_read']		= 1;
							$this->messages->insert($message);

							if (stripos($val['msg'], 'Account Anda tidak aktif') !== false){
								$this->write->error("Account anda diblokir, Hub CS.");
							}

							if (stripos($val['msg'], 'Komplain') !== false){
								$komplain = $val['msg'];
							}

							$login_session['ym_sequence']	= $val['sequence']; 
							$this->login_sessions->update($login_data->login_session_id, $login_session);
						}
					}
				}
			}
		}

		$feedback['error'] 				= false;
		$feedback['data']['komplain']	= $komplain;

		$this->write->feedback($feedback);
	}
}
