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
    public function auth($key){
        $keys= array('92cfdef7-8f2c-433e-ba62-49fa7a243974','3b7abf71-f644-4ff4-a2b9-6b4a9892438c','330b3bc3-4990-4727-98e9-39c32350184b');
        if (in_array($key, $keys)){
         return true;
           
        }
        else{
         return false;
        }
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
    public function practitioner_get($key,$page=FALSE) 
    {
        if($this->auth($key)){
        $results = $this->requestHandler->practitioner_data($page);
        $response = array();

        foreach($results as $result):  
            
        $gender = $result["demographic+gender"];
                if($gender=='gender|M'){
                $sex="MALE";
                }
                else if ($gender=='gender|F'){
                    $sex="FEMALE";
                }
                else{
                    $sex=""; 
                }
      
        
        $citizenship = array(
                
            array("country" => @$this->getCountry($result['person+nationality']))
              
        ); 
        
        $nextOfKin = array(
            array("name" =>"","type"=>"")
            
        );

        $identity = array(
            "HWID"=> "",
            "uhwr"=>array(
              "nationalID"=> array(
                "nin"=>str_replace(" ","",$result['national_id+id_num']),
                "cardNo"=> "",
                "expiryDate"=> ""
              ),
              "passport"=> array(
                "passportNo"=> "",
                "expiryDate"=> ""
              ),
              "driverLicense"=> array(
                "regNo"=> "",
                "expiryDate"=> ""
              ),
              "employeeIPPS"=>"" 
        
        ));

        $language = array(
        array(
              "name"=>"",
              "proficiency"=> ""
        ),
        array(
            "name"=>"",
            "proficiency"=> ""
         ),
         array(
            "name"=>"",
            "proficiency"=> ""
         ),
        );

        $contact=
            array(
              "phone1" =>@$result['person_contact_personal+mobile_phone'],
              "phone2"=> @$result['person_contact_personal+telephone'],
              "phone3"=> "",
              "email1" =>@$result['person_contact_personal+email'],
              "email2" => "",
              "emergencyContact"=>array(
                "name"=> "",
                "phone"=> ""
              ),
              "mobile_money"=>array(
                "name"=> "",
                "phone"=> "",
                "kyc_verified"=>""
              )
              );
        

        $education = array(
        
              "primary"=> "",
              "secondary"=>array(
                "upper" => "",
                "ordinary"=> ""
              ),
              "tertiary" =>"",
              "other" => "",
              "speciality" => ""
        );

        $professionalLicense = array(
            "professionalCouncil" =>@$this->getCouncil($result['registration+council']),
            "dateOfIssue" => "",
            "dateOfExpiry" => "",
            "attachment" => "",
            "licenseNo" => ""
        );

        $professionalRegistration = array(
            "professionalCouncil" => @$this->getCouncil($result['registration+council']),
            "dateOfRegistration" => "",
            "registrationNo" => @$result['registration+registration_number']
        );

        $professionalGazzette = array(
            "registrationNo" => @$result['registration+registration_number'],
            "startDate" => "",
            "endDate" =>""
        );

        $positionInformation = array(
            
              "position" => $result['job+title'],
              "startDate" => date('Y-m-d', strtotime($result['primary_form+start_date'])),
              "endDate" => "",
              "dateOfFirst" => date('Y-m-d', strtotime($result['primary_form+dofa_date'])),
              "positionStatus" => "",
              "facility"=>array(
                "type" => "",
                "instituteCategory" => "",
                "instituteType" => "",
                "district" => "",
                "subCounty"=> "",
                "dhis2Id"=> "",
                "ihrisId" => "",
                "facilityRegId" => "",
                "facility_name"=>$result['facility+name']
              ),
              "cadre"=> @$this->getcadre($result['classification+cadre']),
              "workingHours"=> ""
        );

        $submittingEntities = array(
            "name" => "",
            "date" => "",
            "externalRef" => "https://hris.health.go.ug/national/view?id=".$result['person+id']
        );

        $row = array(
            "identity"=>$identity,
            "surname"=>@$result['person+surname'] ,
            "firstname"=> @$result['person+firstname'],
            "othername"=>@$result['person+othername'],
            "gender"=>$gender,
            "maritalStatus"=> @$this->getmarital($result['demographic+marital_status']),
            "photo": @$image=base64_encode($this->getImagedata($result['Photo+id'])),
            "birthDate"=>@date('Y-m-d', strtotime($result['demographic+birth_date'])),
            "countryOfOrigin"=>@$this->getCountry($result['person+nationality']),
            "citizenship" => $citizenship,
            "district"=>$result["home_district+name"],
            "subCounty"=> "",
            "parish"=> "",
            "nextOfKin"=>$nextOfKin,
            "langauge"=>$language,
            "contact" =>$contact,
            "education"=>$education,
            "professionalLicense" => $professionalLicense,
            "professionalRegistration" => $professionalRegistration,
            "professionalGazzette" =>$professionalGazzette,
            "positionInformation" => $positionInformation,
            "submittingEntities" => $submittingEntities
        );

       
        
         $responses[] = $row;

        endforeach;
        $final= array(
            "count"=>$count=$this->db->get("zebra_staff_list")->num_rows(),
            "source"=>"https://hris.health.go.ug/national",
            "page"=>($page/50)." of ".round(($count/50),0),
            "per_page"=>"50",
            "increament"=>"+50",
            "data" =>$responses
            );

        if(!empty($results)){
        $this->response($final, REST_Controller::HTTP_OK);
        }
        else{
        $response['status'] = 'FAILED';
        $response['message'] = 'Practioner Data is Not Found';
        $response['error'] = TRUE;
        $this->response($response, 400);
    }}

    }

    public function getImagedata($id){
     return $this->db->query("SELECT `Photo+image` as `imagedata` from `zebra_staff_album` WHERE `Photo+id`='$id'")->row()->imagedata;
    }
    
    public function getcadre($id){
     return $this->db->query("SELECT `name` as cadrename  from `hippo_cadre` WHERE `id`='$id'")->row()->cadrename;
    }
    public function getCouncil($id){

    return $this->db->query("SELECT `name` as council  from `hippo_council` WHERE `id`='$id'")->row()->council;
  
    }
    public function getCountry($id){
        return $this->db->query("SELECT `name` as country  from `hippo_country` WHERE `id`='$id'")->row()->country;
   
    }
    public function getmarital($id){

    return $this->db->query("SELECT `name` as marital  from `hippo_marital_status` WHERE `id`='$id'")->row()->marital;
   
        
        
    }
    
  

}