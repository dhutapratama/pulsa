<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_server extends CI_Controller {

	public function __construct() {
        parent::__construct();
        //$this->load->library();
     }

	public function index() {
		$this->load->view('make_json');
	}
}
