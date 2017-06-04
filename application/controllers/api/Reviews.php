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

    public function new_post()
    {
        $request = json_decode(file_get_contents('php://input'));

        $this->db->trans_begin();
        $token = $request->token;
        $where = array('token' => $token);
        $result = $this->db->get_where('users', $where)->result();
        $this->db->trans_commit();

        if (count($result) == 1 && $result[0]->token == $token && $result[0]->role == 'mentee') {

            $order_no = $request->order_no;
            $rating = $request->rating;
            $description = $request->description;

            $data = array(
                'rating' => $rating,
                'review_description' => $description,
                'reviewed_by' => $result[0]->id, 
            );
            $this->db->trans_begin();
            $this->db->where('order_no', $order_no);
            $this->db->update('reviews', $data);
            $this->db->trans_commit();

            $message = array(
                "code" => "SUCCESSFULL",
                "message" => "Successfully create a new review!"
            );
            $this->response($message, 201); 

        } else {
            $message = array(
                "code" => "FORBIDDEN",
                "message" => "Go, away!"
            );
            $this->response($message, 403);
        }
    }

}
