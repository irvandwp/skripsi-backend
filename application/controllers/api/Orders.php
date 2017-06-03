<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

class Orders extends \Restserver\Libraries\REST_Controller {

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
        $this->db->trans_begin();
        $query = $this->db->get('orders');
        $this->db->trans_commit();

        $this->response($query->result(), 200);
    }

    public function detail_get()
    {
    	$id = $this->get('id');
    	
    	$this->response($id, 200);
    }

    public function new_post() {
        $request = json_decode(file_get_contents('php://input'));
        $name = $request->name;

        $data = array(
            'name' => $name
        );

        $this->db->trans_begin();
        $this->db->insert('courses', $data);
        $this->db->trans_commit();

        $this->response(NULL, 201);
    }

}
