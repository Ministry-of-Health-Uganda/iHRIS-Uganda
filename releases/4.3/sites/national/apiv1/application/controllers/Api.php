<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require_once(__DIR__.'/../../i2ceBridge/I2ceBridge.php');

Class Api extends REST_Controller 
{
    public function __construct()
    {
        parent::__construct();
     
        $this->load->model('Request_Model', 'requestHandler');
        $this->load->helper('custom');
        //$this->ihrisobject=new I2ceBrige;
    
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
        $perPage=50;
        $page  = ($this->uri->segment(4))? $this->uri->segment(4) : 1;
        $offset = ($page-1) * $perPage; 
        $results = $this->requestHandler->practitioner_data($offset, $page);
        
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
        $pid = $result['primary_form+parent'];
        $person = I2CE_FormFactory::instance()->createContainer( "person|$pid" );
        $person->populate();
        
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
                "nin"=>ucwords(str_replace(" ","",$result['national_id+id_num'])),
                "cardNo"=> ucwords(str_replace(" ","",$result['national_id_card_no+id_num'])),
                "expiryDate"=> $result['national_id_card_no+expiration_date']
              ),
              "passport"=> array(
                "passportNo"=> "",
                "expiryDate"=> ""
              ),
              "driverLicense"=> array(
                "regNo"=> "",
                "expiryDate"=> ""
              ),
              "employeeIPPS"=>intval($result['ipps_no+id_num'])
        
        ));
         
        // $language = array(
        // array(
        //       "name"=>"",
        //       "proficiency"=> ""
        // ),
        // array(
        //     "name"=>"",
        //     "proficiency"=> ""
        //  ),
        //  array(
        //     "name"=>"",
        //     "proficiency"=> ""
        //  ),
        // );

        $langauge = array();
        $person->populateChildren('person_language');
        foreach($person->getChildren('person_language') as $person_language_form ){

            $langauge['name']= $person_language_form->getField('language')->getDisplayValue();
            $langauge['proficiency']= 'Reading: '.$person_language_form->reading.' Writing: '.$person_language_form->writing. ' Speaking: '.$person_language_form->speaking;
           
        }

        $contact=
            array(
              "phone1" =>@$result['person_contact_personal+mobile_phone'],
              "phone2"=> @$result['person_contact_personal+telephone'],
              "phone3"=> "",
              "email1" =>@$result['person_contact_personal+email'],
              "email2" => "",
              "emergencyContact"=>array(
                "name"=> $result['next_of_kin+name'],
                "phone"=> $result['next_of_kin+telephone'],
                "phone1"=>$result['next_of_kin+alt_telephone'],
                "email"=>$result['next_of_kin+email']
              ),
              "mobileMoney"=>array(
                "name"=> "",
                "phone"=> "",
                "kycVerified"=>""
              )
              );
        

        $education = array(
        
              "primary"=> "",
              "secondary"=>array(
                "upper" => "",
                "ordinary"=> ""
              ),
              "tertiary" =>$result['education+institution'],
              "other" => "",
              "speciality" => ""
        );

        $professionalLicense = array(
            "professionalCouncil" =>@$this->getCouncil($result['registration+council']),
            "dateOfIssue" => "",
            "dateOfExpiry" => "",
            "attachment" => "",
            "licenseNo" => $result['registration+license_number']
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
      //regid is from the facility registry 
        $positionInformation = array(
            
            array(
            
              "position" => $result['job+title'],
              "startDate" => date('Y-m-d', strtotime($result['primary_form+start_date'])),
              "endDate" => "",
              "dateOfFirst" => date('Y-m-d', strtotime($result['primary_form+dofa_date'])),
              "positionStatus" => "Active",
              "employmentTerms" => str_replace("primary_form+employment_terms","",$result['primary_form+employment_terms']),
              "facility"=>array(
                "facilityType" => "",
                "instituteCategory" => $this->getCategory($result["institution_type+institution_category"]),
                "instituteType" =>$result['institution_type+name'],
                "district" => $result['facility_district+name'],
                "region"=> $this->getRegion($result['facility_district+region']),
                "dhis2Id"=> $this->dhis_orgunit($result['facility+id']),
                "ihrisId" => $result['facility+id'],
                "facilityRegId" => "",
                "facilityName"=>$result['facility+name']
              ),
              "cadre"=> @$this->getcadre($result['classification+cadre']),
              "workingHours"=> ""
        )
    
    );
        $trainingInformation = array(
        array(
            "trainingProvider"=>"",
            "program"=>"",
            "dateFrom"=>"",
            "dateTo"=>"",
            "trainer"=>""
        )
    
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
            "gender"=>$sex,
            "maritalStatus"=> @$this->getmarital($result['demographic+marital_status']),
            "photo" => @$image=base64_encode($this->getImagedata($result['primary_form+parent'])),
            "birthDate"=>@date('Y-m-d', strtotime($result['demographic+birth_date'])),
            "countryOfOrigin"=>@$this->getCountry($result['person+nationality']),
            "citizenship" => $citizenship,
            "district"=>$result["home_district+name"],
            "subCounty"=> "",
            "parish"=> "",
            "nextOfKin"=>$nextOfKin,
            "langauge"=>$langauge,
            "contact" =>$contact,
            "education"=>$education,
            "professionalLicense" => $professionalLicense,
            "professionalRegistration" => $professionalRegistration,
            "professionalGazzette" =>$professionalGazzette,
            "positionInformation" => $positionInformation,
            "trainingInformation"=>$trainingInformation,
            "submittingEntities" => $submittingEntities
        );

       
        
         $responses[] = $row;

        endforeach;
        $final= array(
            "count"=>$count=$this->db->get("zebra_staff_list")->num_rows(),
            "source"=>"https://hris.health.go.ug/national",
            "page"=>($page)." of ".round(($count/50),0),
            "per_page"=>"50",
            "increament"=>"+1",
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

    public function getRegion($id){
        return $this->db->query("SELECT `name` as `name` from `hippo_region` WHERE `id`='$id'")->row()->name;
    }
   
    public function getCategory($id){
        return $this->db->query("SELECT `name` as `name` from `hippo_institution_category` WHERE `id`='$id'")->row()->name;
    }

    public function getImagedata($id){
        return $this->db->query("SELECT `image` as `imagedata` from `hippo_person_photo_passport` WHERE `parent`='$id'")->row()->imagedata;
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

    public function dhis_orgunit($id){
        return $this->db->query("SELECT `dhis_orgunit` as `dhis_orgunit` from `hippo_facility` WHERE `id`='$id'")->row()->dhis_orgunit;
    }
   
    
  

}