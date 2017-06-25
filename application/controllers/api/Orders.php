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

    public function index_post()
    {
        $request = json_decode(file_get_contents('php://input'));

        $this->db->trans_begin();
        $token = $request->token;
        $where = array('token' => $token);
        $result = $this->db->get_where('users', $where)->result();
        $this->db->trans_commit();

        if (count($result) == 1 && $result[0]->token == $token) {
            $this->db->trans_begin();
            $where = array();
            $params = $result[0]->role == 'mentee' ? 'mentee_id' : 'mentor_id';
            $where[$params] = $result[0]->id;
            $order_result = $this->db->get_where('orders', $where)->result();
            $this->db->trans_commit();

            if (count($order_result) > 0) {
                $this->response($order_result, 200);
            } else {
                $message = array(
                    "code" => "NOT_FOUND",
                    "message" => "No data found"
                );
                $this->response($message, 404);
            }
        } else {
            $message = array(
			    "code" => "FORBIDDEN",
			    "message" => "Go, away!"
            );
            $this->response($message, 403);
        }
    }

    public function detail_post()
    {
        $request = json_decode(file_get_contents('php://input'));

        $this->db->trans_begin();
        $token = $request->token;
        $where = array('token' => $token);
        $result = $this->db->get_where('users', $where)->result();
        $this->db->trans_commit();

        if (count($result) == 1 && $result[0]->token == $token) {
	    	$id = $this->get('id');
	    	
            $this->db->trans_begin();
            $this->db->select('orders.id, orders.order_no, orders.order_description, orders.mentee_id, orders.mentor_id, orders.status, courses.name, orders.total_price, order_details.price, order_details.duration, order_details.latitude, order_details.longitude, order_details.start_time, order_details.end_time, reviews.rating, reviews.review_description, reviews.reviewed_by');
            $this->db->from('orders');
            $this->db->join('order_details', 'orders.id = order_details.order_id', 'inner');
            $this->db->join('reviews', 'orders.order_no = reviews.order_no', 'inner');
            $this->db->join('courses', 'courses.id = orders.course_id', 'inner');
            $this->db->where('orders.id', $id);
            $this->db->limit(1);
            $result = $this->db->get()->result_array()[0];

            $users = $this->db->get('users')->result();
            $this->db->trans_commit();

            foreach ($users as $user) {
                if ((int)$result['mentor_id'] == (int)$user->id){
                    $result['mentor_name'] = $user->name;
                    unset($result['mentor_id']);
                }
                if ((int)$result['mentee_id'] == (int)$user->id){
                    $result['mentee_name'] = $user->name;
                    unset($result['mentee_id']);
                }
            }

	    	$this->response($result, 200);
        } else {
            $message = array(
			    "code" => "FORBIDDEN",
			    "message" => "Go, away!"
            );
            $this->response($message, 403);
        }
    }

    public function new_post() {
        $request = json_decode(file_get_contents('php://input'));

        $this->db->trans_begin();
        $token = $request->token;
        $where = array('token' => $token);
        $result = $this->db->get_where('users', $where)->result();
        $this->db->trans_commit();

        if (count($result) == 1 && $result[0]->token == $token) {

	        /**
	         * yang dikirim :
	         * course_id, description
	         * duration, price, latitude, longitude
	         */

	        $course_id = $request->course_id;
	        $description = $request->order_description;
	        $duration = $request->duration;
	        $price = $request->price;
	        $latitude = $request->latitude;
	        $longitude = $request->longitude;

			$this->db->trans_begin();

	        $data = array(
	            'order_no' => strtoupper(substr($this->security->get_csrf_hash(), rand(0, 15), 5)),
	            'total_price' => $price * $duration,
	            'course_id' => $course_id,
	            'order_description' => $description,
                'mentee_id' => $result[0]->id,
	        );	        
	        $this->db->insert('orders', $data);

	        $this->db->select('*');
	        $this->db->from('orders');
	        $this->db->order_by('created_at', 'desc');
	        $this->db->limit(1);
	        $last_order = $this->db->get()->result()[0];

	        $data = array(
	        	'order_id' => $last_order->id,
	        	'duration' => $duration,
	        	'price' => $price,
	        	'latitude' => $latitude,
	        	'longitude' => $longitude,
	        );
	        $this->db->insert('order_details', $data);

	        $data = array(
	        	'order_no' => $last_order->order_no
	        );
	        $this->db->insert('reviews', $data);

	        $this->db->trans_commit();

	        $message = array(
	            "code" => "SUCCESSFULL",
	            "message" => "Successfully place a new order"
	        );
	        $this->response($message, 200);

        } else {
            $message = array(
			    "code" => "FORBIDDEN",
			    "message" => "Go, away!"
            );
            $this->response($message, 403);
        }
    }

    public function update_post()
    {
        $request = json_decode(file_get_contents('php://input'));

        $this->db->trans_begin();
        $token = $request->token;
        $where = array('token' => $token);
        $result = $this->db->get_where('users', $where)->result();
        $this->db->trans_commit();

        if (count($result) == 1 && $result[0]->token == $token) {

            /**
             * yang dikirim
             * status
             */

            $data = array();
            $params = $result[0]->role == 'mentee' ? 'mentee_id' : 'mentor_id';
            $data[$params] = $result[0]->id;
            $data['status'] = strtoupper($request->status);

            $id = $this->get('id');
            
            $this->db->trans_begin();
            $this->db->where('id', $id);
            $this->db->update('orders', $data);
            $this->db->trans_commit();

            $message = array(
                "code" => "SUCCESSFULL",
                "message" => "Successfully update current order"
            );
            $this->response($message, 202);
        } else {
            $message = array(
                "code" => "FORBIDDEN",
                "message" => "Go, away!"
            );
            $this->response($message, 403);
        }
    }

}
