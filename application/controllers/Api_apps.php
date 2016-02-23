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

    public function registrasi() {
    	$this->load->model(array('members', 'saldo'));

		$param = array('device_id' => 'required');
		$input = $this->auth->input($param);

		$member_data = $this->members->get_by_device_id($input['device_id']);
		if (!$member_data) {
			// Create new membership
			$date = date('Y-m-d H:i:s');
			$member['device_id']			= '';
			$member['name']					= $input['name'];
			$member['email']				= $input['email'];
			$member['password']				= $input['password'];
			$member['phone']				= $input['phone'];
			$member['is_email_verified']	= 0;
			$member['is_phone_verified']	= 0;
			$member['last_update']			= $date;
			$member['ym_username']			= '';
			$member['ym_password']			= '';
			$member['pin']					= '';


			$member['device_id']			= $input['device_id'];
			$member['is_email_verified']	= '';
			$member['is_phone_verified']	= '';
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

	public function get_login_key() {
		$this->load->model(array('members', 'saldo'));

		$param = array('device_id' => 'required');
		$input = $this->auth->input($param);

		$member_data = $this->members->get_by_device_id($input['device_id']);
		if (!$member_data) {
			// Create new membership
			$date = date('Y-m-d H:i:s');

			$member['device_id']			= $input['device_id'];
			$member['is_email_verified']	= '0';
			$member['is_phone_verified']	= '0';
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

	public function yahoo_messenger() {
		$this->load->library(array('jymengine'));
		$ym_username = 'dhuta_pratama';
		$ym_password = '48624862aA';
		$consumer_key = 'dj0yJmk9cm5iUVliUkhEVmFMJmQ9WVdrOU4wOVFlREJXTkhNbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD1iOA--';
		$secret_key = '15e488b278f236337dd4fb133d8611b4c7384331';

		$this->jymengine($consumer_key, $secret_key, $ym_username, $ym_password)
			->debug = true;

		if ($this->jymengine->debug) echo '> Fetching request token'. PHP_EOL;
		if (!$this->jymengine->fetch_request_token()) die('Fetching request token failed');

		if ($this->jymengine->debug) echo '> Fetching access token'. PHP_EOL;
		if (!$this->jymengine->fetch_access_token()) die('Fetching access token failed');

		if ($this->jymengine->debug) echo '> Signon as: '. $ym_username. PHP_EOL;
		if (!$this->jymengine->signon('I am login from PHP code')) die('Signon failed');

		$seq = -1;
		$resp = $this->jymengine->fetch_long_notification($seq+1);

		if (isset($resp))
		{	
			if ($resp === false) 
			{		
				if ($this->jymengine->get_error() != -10)
				{
					if ($this->jymengine->debug) echo '> Fetching access token'. PHP_EOL;
					if (!$this->jymengine->fetch_access_token()) die('Fetching access token failed');				
					
					if ($this->jymengine->debug) echo '> Signon as: '. USERNAME. PHP_EOL;
					if (!$this->jymengine->signon(date('H:i:s'))) die('Signon failed');
					
					$seq = -1;
				}				
			}
			
			
			foreach ($resp as $row)
			{
				foreach ($row as $key=>$val)
				{
					if ($val['sequence'] > $seq) $seq = intval($val['sequence']);
					
					/*
					 * do actions
					 */
					if ($key == 'buddyInfo') //contact list
					{
						if (!isset($val['contact'])) continue;
						
						if ($this->jymengine->debug) echo PHP_EOL. 'Contact list: '. PHP_EOL;
						foreach ($val['contact'] as $item)
						{
							if ($this->jymengine->debug) echo $item['sender']. PHP_EOL;
						}
						if ($this->jymengine->debug) echo '----------'. PHP_EOL;
					}
					
					else if ($key == 'message') //incoming message
					{
						if ($this->jymengine->debug) echo '+ Incoming message from: "'. $val['sender']. '" on "'. date('H:i:s', $val['timeStamp']). '"'. PHP_EOL;
						if ($this->jymengine->debug) echo '   '. $val['msg']. PHP_EOL;
						if ($this->jymengine->debug) echo '----------'. PHP_EOL;
						
						//reply
						$words = explode(' ', trim(strtolower($val['msg'])));
						if ($words[0] == 'help')
						{
							$out = 'This is Yahoo! Open API demo'. PHP_EOL;
							$out .= '  To get recent news from yahoo type: news'. PHP_EOL;
							$out .= '  To get recent entertainment news from yahoo type: omg'. PHP_EOL;						
							$out .= '  To change my/robot status type: status newstatus'. PHP_EOL;
						}
						else if ($words[0] == 'news')
						{
							if ($this->jymengine->debug) echo '> Retrieving news rss'. PHP_EOL;
							$rss = file_get_contents('http://rss.news.yahoo.com/rss/topstories');
													
							if (preg_match_all('|<title>(.*?)</title>|is', $rss, $m))
							{
								$out = 'Recent Yahoo News:'. PHP_EOL;
								for ($i=2; $i<7; $i++)
								{
									$out .= str_replace("\n", ' ', $m[1][$i]). PHP_EOL;
								}
							}
						}
						else if ($words[0] == 'omg')
						{
							if ($this->jymengine->debug) echo '> Retrieving OMG news rss'. PHP_EOL;
							$rss = file_get_contents('http://rss.omg.yahoo.com/latest/news/');
													
							if (preg_match_all('|<title>(.*?)</title>|is', $rss, $m))
							{
								$out = 'Recent OMG News:'. PHP_EOL;
								for ($i=2; $i<7; $i++)
								{
									$out .= str_replace(array('<![CDATA[', ']]>'), array('', ''), $m[1][$i]). PHP_EOL;
								}
							}
						}	
						else if ($words[0] == 'status')
						{
							$this->jymengine->change_presence(str_replace('status ', '', strtolower($val['msg'])));
							$out = 'My status is changed';
						}	
						else
						{
							$out = 'Please type: help';
						}
						
						//send message
						if ($this->jymengine->debug) echo '> Sending reply message '. PHP_EOL;
						if ($this->jymengine->debug) echo '    '. $out. PHP_EOL;	
						if ($this->jymengine->debug) echo '----------'. PHP_EOL;
						$this->jymengine->send_message($val['sender'], json_encode($out));
					}
					
					else if ($key == 'buddyAuthorize') //incoming contact request
					{
						if ($this->jymengine->debug) echo PHP_EOL. 'Accept buddy request from: '. $val['sender']. PHP_EOL;					
						if ($this->jymengine->debug) echo '----------'. PHP_EOL;	
						if (!$this->jymengine->response_contact($val['sender'], true, 'Welcome to my list'))
						{
							$this->jymengine->delete_contact($val['sender']);
							$this->jymengine->response_contact($val['sender'], true, 'Welcome to my list');
						}
					}
				}
			}
		}
	}
}
