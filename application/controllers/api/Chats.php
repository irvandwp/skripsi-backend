<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

class Chats extends \Restserver\Libraries\REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index_post()
    {
        $request = json_decode(file_get_contents('php://input'));

        $this->db->trans_begin();
        $token = $request->token;
        $where = array('token' => $token);
        $result = $this->db->get_where('users', $where)->result();
        $this->db->trans_commit();

        if (count($result) == 1 && $result[0]->token == $token) {

            $order_no = $request->order_no;

            $where = array(
                'order_no' => $order_no
            );
            $this->db->trans_begin();
            $results = $this->db->get_where('chats', $where)->result_array();
            $users = $this->db->get('users')->result();
            $this->db->trans_commit();

            for ($i=0; $i < count($results); $i++) { 
                foreach ($users as $user) {
                    if ((int)$results[$i]['created_by'] == (int)$user->id){
                        $results[$i]['name'] = $user->name;
                        $results[$i]['role'] = $user->role;
                        unset($results[$i]['created_by']);
                    }
                }                    
            }

            $this->response($results, 200);

        } else {
            $message = array(
                "code" => "FORBIDDEN",
                "message" => "Go, away!"
            );
            $this->response($message, 403);
        }
    }

    public function new_post()
    {
        $request = json_decode(file_get_contents('php://input'));

        $this->db->trans_begin();
        $token = $request->token;
        $where = array('token' => $token);
        $result = $this->db->get_where('users', $where)->result();
        $this->db->trans_commit();

        if (count($result) == 1 && $result[0]->token == $token) {

            $message = $request->message;
            $order_no = $request->order_no;

            $data = array(
                'order_no' => strtoupper($order_no),
                'message' => $message,
                'created_by' => $result[0]->id,
            );

        $this->db->trans_begin();
        $this->db->insert('chats', $data);
        $this->db->trans_commit();

        $message = array(
            "code" => "SUCCESSFULL",
            "message" => "Successfully create a new chat"
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
