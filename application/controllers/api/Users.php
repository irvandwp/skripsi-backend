<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

class Users extends \Restserver\Libraries\REST_Controller {

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
        $query = $this->db->get('users');
        $this->db->trans_commit();

        $this->response($query->result(), 200);
    }

    public function login_post()
    {
        $request = json_decode(file_get_contents('php://input'));

        $email = $request->email;
        $password = $request->password;
        $role = $request->role;

        $where = array(
            'email' => $email,
            'role' => $role
        );

        $query = $this->db->get_where('users', $where);

        $result = $query->result();

        if (count($result) == 1 && password_verify($password, $result[0]->password)) {
            $message = array(
                "token" => $result[0]->token,
                "email" => $result[0]->email,
                "name" => $result[0]->name
            );
            $this->response($message, 200);
        } else {
            $message = array(
                "code" => "UNAUTHORIZED",
                "message" => "Please enter the correct credentials"
            );
            $this->response($message, 401);
        }
    }

    public function new_post() {
        $request = json_decode(file_get_contents('php://input'));
        $email = $request->email;
        $name = $request->name;
        $password = password_hash($request->password, PASSWORD_BCRYPT);
        $phone = $request->phone;
        $address = $request->address;
        $role = $request->role;
        $occupation = $request->occupation;

        $data = array(
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'phone' => $phone,
            'address' => $address,
            'role' => $role,
            'occupation' => $occupation,
            'token' => $this->security->get_csrf_hash(),
        );

        $this->db->trans_begin();
        $this->db->insert('users', $data);
        $this->db->trans_commit();

        $message = array(
            "code" => "SUCCESSFULL",
            "message" => "Successfully create a new account"
        );

        $this->response(NULL, 201);
    }

    public function update_post()
    {

    }

}
