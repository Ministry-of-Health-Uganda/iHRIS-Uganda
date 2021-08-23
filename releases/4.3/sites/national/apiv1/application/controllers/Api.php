<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

Class Api extends REST_Controller 
{
    public function __construct()
    {
        parent::__construct();
     
        $this->load->model('Request_Model', 'requestHandler');
    }
    public function index_get(){
        echo "iHRIS Manage API";

    }

    //GET ihrisdata
    public function ihrisdata_get() 
    {
            $results = $this->requestHandler->getihrisdata();
            if(!empty($results)){
  
            $this->response($results, REST_Controller::HTTP_OK);
            }
            else{

            $response['status'] = 'FAILED';
            $response['message'] = 'ihrisdata is not found. Force generate stafflist ';
            $response['error'] = TRUE;
            $this->response($response, 400);
        }
    }
  

}