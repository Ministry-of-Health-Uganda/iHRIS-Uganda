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
      
        
        // ob_start(); //start capture of the output buffer
        // imagejpeg($image, null, 80);
        // $data = ob_get_contents();
        // ob_end_clean();
        $genInfo = array(
            "firstName"=>@$result['person+firstname'], 
            "surname"=> @$result['person+surname'], 
            "middleName"=> null, 
            "maidenName"=> null,
            "otherName1"=>@$result['person+othername'],
            "otherName2"=> null,
            "otherName3"=> null,
            "country1"=> @$this->getCountry($result['person+nationality']), // Country of birth
            "country2"=> null, // Citizenship at birth
            "country3"=> null, //Country of present citizenship
            "country4"=> null, // Country fo residence
            "country5"=> null, // Country of second citizenship (multiple citizenship)
            "dateOfBirth"=>@date('Y-m-d', strtotime($result['demographic+birth_date'])),
            "gender"=>$sex,
            "districtOrTown"=> $result["home_district+name"],
            "subCounty"=> "", 
            "tribe"=> null, // Tribe of a health worker
            "fatherName"=> null,
            "motherName"=> null,
            "maritalStatus"=> @$this->getmarital($result['demographic+marital_status']),
            "fullName"=> $result['person+surname'].' '.$result['person+firstname'].' '. @$result['person+othername'],
            "disciplinaryAction"=> ""
        );

      //contact Info
      $contactInformation = array(
                "personalAddress"=> @$result['person_contact_personal+address'],
                "residence"=> @$result['residence_district+name'],
                "telephoneNumber1"=>@$result['person_contact_personal+mobile_phone'] ,
                "telephoneNumber2"=> @$result['person_contact_personal+telephone'],
                "emailAddress1"=> @$result['person_contact_personal+email'],
                "emailAddress2"=> null,
                "placeOfWork"=> null,
                "workAddress"=>null ,
                "workPhoneNumber"=>null ,
                "locality"=> null,
                "districtTown"=>null ,
                "contactName"=> $result['person+surname'].' '.$result['person+firstname'].' '. @$result['person+othername']
      );

            $address = array(
                "entityAddress" => null,
                "zipCode" => null,
                "postalAdress" => null,
                "streetAddress" => null,
                "townOrCity" => $result['residence_district+name'],
                "country" => @$this->getCountry($result['person+nationality'])
            );

            $healthFacility = array(
                "id" => str_replace("facility|","",$result['position+facility']),
                "facilityCode" => $result['position+facility'],
                "facilityName" => $result['facility+name'],
            
            );

            $professionalEntity = array(
                "id" => $result['registration+id'],
                "professionalBody" => @$this->getCouncil($result['registration+council']) ,
                "professionalBodyId" =>@$result['registration+council'],
                "registrationNumber" => @$result['registration+registration_number'],
                "emailAddress" => null,
                "website" => null,
                "telephoneNumber1" => null,
                "telephoneNumber2" => null,
                "fileAttachment" => null
            );

            $photograph1 = array(
                "id"=>"manage_".str_replace("person_photo_passport|","",$result['Photo+id']),
                "name"=>$result['person+surname'].str_replace("person|","",$result['person+id']),
                "contentType"=>"base64",
                "extension"=>"",
                "contentAbstract"=>"",
                "content"=> @$image = str_replace("https://hris.health.go.ug/","",base64_encode($this->getImagedata($result['Photo+id']))),  
                "attachmentType"=>"",
                "attachmentTypeId"=>""
            );

            
            $dataSubmissionInstitution = array(
                "id" => 'NM1',
                "institutionName" => "Ministry of Health",
                "dateOfSubmission" => date("Y-m-d H:i:s"),
                "referenceData" => "https://hris.health.go.ug/national/view?id=".$result['person+id'],
                "healthWorker" => $result['person+surname'].' '.$result['person+firstname'].' '. @$result['person+othername']
            );

            $currentAddress = array (
                "entityAddress" => null,
                "zipCode" => null,
                "postalAdress" => @$result['person_contact_personal+address'],
                "streetAddress" => null,
                "townOrCity" => @$result['residence_district+name'],
                "country" => @$this->getCountry($result['person+nationality'])
            );

            $employmentInfoDto = array(
                "cadre" => @$this->getcadre($result['classification+cadre']),
                "date_started_work" => date('Y-m-d', strtotime($result['primary_form+start_date'])),
                "alternative_workplaces" => null,
                "former_worker_places" => "[]",
                "date_at_first_job" => date('Y-m-d', strtotime($result['primary_form+dofa_date'])),
                "currentAddress" => $result['person_contact_personal+address'],
                "healthWorker" => null,
                "working_hours_days_available" => null
            );


            

        $row = array(
            "generalInformation"=>$genInfo,
            "contactInformation"=>$contactInformation,
            "nin" => str_replace(" ","",$result['national_id+id_num']),
            "socialSecurityNumber" => null,
            "passportNumber" => null,
            "driverLicenceNumber" => null,
            "incomeTaxNumber" => null,
            "insuranceNumber" => null,
            "externalReferenceId" =>"https://hris.health.go.ug/national/view?id=".$result['person+id'],
            "address" => $address,
            "occupationalCategory" => null,
            "employmentStatus" => "Active_In_service",
            "employmentStatusId" => null,
            "employmentTitle" => $result['job+title'],
            "homePhone" => $result['person_contact_personal+telephone'],
            "businessPhone" => null,
            "mobilePhone" => $result['person_contact_personal+telephone'],
            "faxNumber" => null,
            "homeMail" => $result['person_contact_personal+email'],
            "businessMail" => $result['person_contact_personal+email'],
            "healthFacility"=>$healthFacility,
            "professionalEntity" => $professionalEntity,
            "languageInfos" => null,
            "educationalInstitutions" => null,
            "licenseRegistrationCertifications" => null,
            "internshipTrainings" => null,
            "professionalTrainings" => null,
            "facilityTypeOwnership" => null,
            "photograph1" => $photograph1,
            "photograph2" => null,
            'dataSubmissionInstitution'=>$dataSubmissionInstitution,
            "ninDateOfIssue" => null,
            "ninDateOfExpiration" => null,
            "nin_date_issue_expiration" => null,
            "name_dob_gender_id" => null,
            "psCountryOfIssuance" => null,
            "psDateOfIssue" => null,
            "psDateOfExpiration" => null,
            "educationDetailsDto" => null,
            "educationDetailId" => null,
            "currentAddress" =>$currentAddress,
            "employmentInfoDto" =>$employmentInfoDto 


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