<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

class Reviews extends \Restserver\Libraries\REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index_get()
    {
        $id = $this->get('id');
        $this->response($id, 200);
    }

    public function index_post()
    {
    	$id = $this->get('id');
        $this->response($id, 200);
    }

}
