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
  public function hwdata_get($district=FALSE){
      if(!empty($district)){
          $filter="where  `district+name`='$district'";
      }
      else{
          $filter="";
      }
        
    $result = $this->db->query("SELECT
    DISTINCT trim(`person+id`) as ihris_pid,
    trim(`district+id`) as district_id,
    `district+name` as district,
    `national_id+id_num` as national_id,
    `facility+facility_type` as facility_type,
    trim(`facility+id`) as facility_id,
    `facility+name` as facility,
    trim(`job+id`) as job_id, 
    `job+title` as job,
    `person+surname` as surname,
    `person+firstname` as firstname,
    `person+othername` as othername,
    `person_contact_personal+mobile_phone` as phone ,
    DATE_FORMAT(`demographic+birth_date`,'%Y-%m-%d') as birth_date,
    CASE WHEN `demographic+gender` ='gender|M' THEN 'Male'
    WHEN `demographic+gender` ='gender|F' THEN 'Female' 
    ELSE 'NULL' END  as gender
    from  `national_manage`.`zebra_staff_list` $filter
     ")->result();
     $this->response($result);
    } 
    public function allihrisdata_get() 
    {
            $results = $this->requestHandler->getallihrisdata();
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
    public function person_attend_get($fro,$t) 
    {
            $from=date("Y-m", strtotime($fro) );
            $to=date("Y-m", strtotime($t) );   
            $results = $this->requestHandler->attendance_data($from,$to);
            if(!empty($results)){
  
            $this->response($results, REST_Controller::HTTP_OK);
            }
            else{

            $response['status'] = 'FAILED';
            $response['message'] = 'Attendance Data not Found';
            $response['error'] = TRUE;
            $this->response($response, 400);
        }
    }
    //get praction json
    public function practitioner_get($district=FALSE) 
    {
        $results = $this->requestHandler->practitioner_data($district);
        $response = array();

        foreach($results as $result):  
            
        $gender = $result["demographic+gender"];
                if($gender='gender|M'){
                $sex="MALE";
                }
                else if ($gender='gender|F'){
                    $sex="FEMALE";
                }
                else{
                    $sex=""; 
                }
      
      
        $genInfo = array(
            "firstName"=>$result['person+firstname'], 
            "surname"=> $result['person+surname'], 
            "middleName"=> null, // 
            "maidenName"=> null,
            "otherName1"=>$result['person+othername'],
            "otherName2"=> null,
            "otherName3"=> null,
            "country1"=> $result['country_name'], // Country of birth
            "country2"=> null, // Citizenship at birth
            "country3"=> null, //Country of present citizenship
            "country4"=> null, // Country fo residence
            "country5"=> null, // Country of second citizenship (multiple citizenship)
            "dateOfBirth"=>date('Y-m-d', strtotime($result['demographic+birth_date'])),
            "gender"=>null,
            "districtOrTown"=> $result["home_district+name"],
            "subCounty"=> "", 
            "tribe"=> null, // Tribe of a health worker
            "fatherName"=> null,
            "motherName"=> null,
            "maritalStatus"=> $result['marital_status'],
            "fullName"=> $result['person+surname'].' '.$result['person+firstname'].' '. @$result['person+othername'],
            "disciplinaryAction"=> ""
        );

      //contact Info
      $contactInformation = array(
                "personalAddress"=> $result['person_contact_personal+address'],
                "residence"=> $result['residence_district+name'],
                "telephoneNumber1"=>$result['person_contact_personal+mobile_phone'] ,
                "telephoneNumber2"=> $result['person_contact_personal+telephone'],
                "emailAddress1"=> $result['person_contact_personal+email'],
                "emailAddress2"=> null,
                "placeOfWork"=> null,
                "workAddress"=>null ,
                "workPhoneNumber"=>null ,
                "locality"=> null,
                "districtTown"=>null ,
                "contactName"=> $result['person+surname'].' '.$result['person+firstname'].' '. @$result['person+othername']
      );

        $row = array(
            "generalInformation"=>$genInfo,
            "contactInformation"=>$contactInformation,
        );
        
         $response[] = $row;

        endforeach;

        if(!empty($results)){
        $this->response($response, REST_Controller::HTTP_OK);
        }
        else{
        $response['status'] = 'FAILED';
        $response['message'] = 'Practioner Data is Not Found. Generate Stafflist';
        $response['error'] = TRUE;
        $this->response($response, 400);
    }

    }
    
  

}