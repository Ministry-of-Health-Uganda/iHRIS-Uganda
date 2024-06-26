<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require_once FCPATH . 'i2cebridge/I2ceBridge.php';

Class Api extends REST_Controller 
{
    public function __construct()
    {
        parent::__construct();
     
        $this->load->model('Request_Model', 'requestHandler');
        $this->load->helper('custom');
        $this->ihrisobject=new I2ceBrige;
    
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
    public function practitioner_get($key,$page_limit=FALSE) 
    {
        if($this->auth($key)){
        $page_limit=50;
        $page  = ($this->uri->segment(4))? $this->uri->segment(4) : 1;
       	
        $offset = ($page > 1) ? ($page_limit * ($page - 1)) : 0;
        $results = $this->requestHandler->practitioner_data($offset, $page_limit);
        $total_count=$this->db->get("zebra_ihris_data_api")->num_rows();
         $pages = ($total_count % $page_limit == 0) ? ($total_count / $page_limit) : (round($total_count / $page_limit, 0) + 1);
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
       
        
        $citizenship = array(
                
            array("country" => @$this->getCountry($result['person+nationality']))
              
        ); 
        
    

        $nextOfKin=array();
        foreach($this->getKins($result['person+id']) as $person_kins_form ){

            $nextOfKin['name']= !empty($person_kins_form->name)?$person_kins_form->name: ' ';
            $nextOfKin['address'] = !empty($person_kins_form->address)?$person_kins_form->address:' ';
            $nextOfKin['mobile_phone'] = !empty($person_kins_form->mobile_phone)?$person_kins_form->mobile_phone: ' ';
            $nextOfKin['telephone'] = !empty($person_kins_form->mobile_phone)?$person_kins_form->mobile_phone:' ';
            $nextOfKin['relationship'] = !empty($person_kins_form->relationship)?$person_kins_form->relationship:' ';
          
           
        }

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
         
     

        $langauge=array();
        foreach($this->getLangauges($result['person+id']) as $person_language_form ){
        
            $langauge['name']= !empty($this->langaugeName($person_language_form->language))?$this->langaugeName($person_language_form->language):null;
            $langauge['proficiency'] = 'Reading: '.str_replace('language_proficiency|','',$person_language_form->reading).', Writing: '.str_replace('language_proficiency|','',$person_language_form->writing). ', Speaking: '.str_replace('language_proficiency|','',$person_language_form->speaking);
           
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
              "speciality" => $result['education+major']
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
              "employmentTerms" => str_replace("employment_terms|","",$result['primary_form+employment_terms']),
              "facility"=>array(
                "facilityType" => @$this->getfacType($result['facility_type+id']),
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
              "classification"=> @$this->getclassification($result['classification+id']),
              "workingHours"=> "8"
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
            "name" => "Ministry of Health",
            "date" => date('Y-m-d H:i:s'),
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
            "count"=>$count=$this->db->get("zebra_ihris_data_api")->num_rows(),
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
        return $this->db->query("SELECT  `image` as `imagedata` from `hippo_person_photo_passport` WHERE `parent`='$id'")->row()->imagedata;
       }
    
    public function getcadre($id){
     return $this->db->query("SELECT `name` as cadrename  from `hippo_cadre` WHERE `id`='$id'")->row()->cadrename;
    }
    public function getFacType($id){
        return $this->db->query("SELECT `name` as factype  from `hippo_facility_type` WHERE `id`='$id'")->row()->factype;
       }
    public function getclassification($id){
        return $this->db->query("SELECT `name` as classification  from `hippo_classification` WHERE `id`='$id'")->row()->classification;
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
    public function get_physical($id)
    {

        return $this->db->query("SELECT `name` as physical  from `hippo_physical_status` WHERE `id`='$id'")->row()->physical;

    }


    public function dhis_orgunit($id){
        return $this->db->query("SELECT `dhis_orgunit` as `dhis_orgunit` from `hippo_facility` WHERE `id`='$id'")->row()->dhis_orgunit;
    }

    public function getLangauges($id){

        $rows=$this->db->query("SELECT * FROM `hippo_person_language` WHERE parent='$id'");
    return $rows->result();
    }
    public function langaugeName($id){

        $rows=$this->db->query("SELECT `name` FROM `hippo_language` WHERE id='$id'");
    return $rows->row()->name;
    }
    public function getKins($id){

        $rows=$this->db->query("SELECT * FROM `hippo_person_contact_emergency` WHERE parent='$id'");
    return $rows->result();
    }
public function ihriscsv_get($key,$district,$facility=FALSE)
{
    if ($this->auth($key)) {
        $results = $this->requestHandler->csv_practitioner_data(urldecode($district),urldecode($facility));
        $response = array();
        foreach ($results as $result) {
            $person_id = @$result['person+id'];
            $gender = $result["demographic+gender"];
            if ($gender == 'gender|M') {
                $sex = "MALE";
            } else if ($gender == 'gender|F') {
                $sex = "FEMALE";
            } else {
                $sex = "";
            }

           $mobile  =  @$result['person_contact_personal+mobile_phone'];
           if(!empty($mobile)){
            $own_mobile = "Yes";
           }

            $row = array(
                "mange_4ihris_id" => @$person_id,
                "Surname" => @$result['person+surname'],
                "Firstname" => @$result['person+firstname'],
                "Othername" => @$result['person+othername'],
                "Gender" => $sex,
                "Facility" => @$result['facility+name'],
                "Home District" => @$result["home_district+name"],
                "Residence District" => @$result['facility_district+name'],
                "Department" => @$result['job+department'],
                "Job" => @$result['job+title'],
                "Recruitment Mechanism" => '',
                "Duty Station" => @$result['facility+name'],
                "Terms of Employment" => str_replace("employment_terms|", "", $result['primary_form+employment_terms']),
                "Birth Date" => @date('Y-m-d', strtotime($result['demographic+birth_date'])),
                "National ID Number" => ucwords(str_replace(" ", "", $result['national_id+id_num'])),
                "National ID Card Number" => ucwords(str_replace(" ", "", $result['national_id_card_no+id_num'])),
                "TIN" => @$result['TIN+id_num'],
                "File No" => @$result['file_no+id_num'],
                "IPPS No" => intval($result['ipps_no+id_num']),
                "Marital Status" => @$this->getmarital($result['demographic+marital_status']),
                "Physical Status" => @$this->get_physical($result['demographic+physical_status']),
                "Training Institution" => @$result['education+institution'],
                "Qualification" => @$result['education+major'],
                "Own Mobile phone" => $own_mobile,
                "Mobile Money Number" => @$result['person_contact_personal+mobile_phone'],
                "Names of Mobile Money Number" => @$result['person+surname'].' '.@$result['person+firstname'].' '.@$result['person+othername'] ,
                "Telephone Number" => @$result['person_contact_personal+telephone'],
                "Alternative Telephone Number" => @$result['alternative_telephone_number'],
                "Date of Current Appointment" => date('Y-m-d', strtotime($result['primary_form+start_date'])),
                "DSC Minute" => @$result['primary_form+minute'],
                "Date of First Appointment" => date('Y-m-d', strtotime($result['primary_form+dofa_date'])),
                "Uniform Size" => @$this->uniform_data($person_id)['dress'],
                "Shoe Size" => @$this->uniform_data($person_id)['shoes'],
                "Email Address" => @$result['person_contact_personal+email'],
                "Registration Council" => @$this->getCouncil($result['registration+council']),
                "Registration Number" => @$result['registration+registration_number'],
                "Registration Date" => @$result['registration+registration_date'],
                "License Number" => $result['registration+license_number'],
                "Able to use English" => 'Yes',
                "Other Language" => ''
            );
            $response[] = $row;
        }

        if (!empty($results)) {
            $this->generate_csv($response,$district,$facility);
        } else {
            $response['status'] = 'FAILED';
            $response['message'] = 'Practioner Data is Not Found';
            $response['error'] = TRUE;
            $this->response($response, 400);
        }
    }
}

private function generate_csv($data,$district,$facility=FALSE)
{
    // Set the headers for CSV download
$filename = $district . '-' . $facility . '-practitioners.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');


    // Open the output stream
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, array(
        "mange_4ihris_id", "Surname", "Firstname", "Othername", "Gender", "Facility", 
        "Home District", "Residence District", "Department", "Job", "Recruitment Mechanism", 
        "Duty Station", "Terms of Employment", "Birth Date", "National ID Number", 
        "National ID Card Number", "TIN", "File No", "IPPS No", "Marital Status", 
        "Physical Status", "Training Institution", "Qualification", "Own Mobile phone", 
        "Mobile Money Number", "Names of Mobile Money Number", "Telephone Number", 
        "Alternative Telephone Number", "Date of Current Appointment", "DSC Minute", 
        "Date of First Appointment", "Uniform Size", "Shoe Size", "Email Address", 
        "Registration Council", "Registration Number", "Registration Date", "License Number", 
        "Able to use English", "Other Language"
    ));

    // Output the data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    // Close the output stream
    fclose($output);
}

public function uniform_data($person){
   $uniform =  $this->db->query("SELECT `uniform+shoe_sizes` as shoe,`uniform+uniform_size` as uniform FROM  zebra_staff_uniform WHERE `person+id`='$person'")->row();
   $shoes = $uniform->shoe;
   $dress = $uniform->uniform;
   $data['shoes']= $this->translate_shoes($shoes);
   $data['dress'] =$this->translate_uniform($dress);
return $data;
}
public function translate_uniform($uniform_id){
        return $this->db->query("SELECT name from hippo_uniform_size where id='$uniform_id'")->row()->name;

}

public function translate_shoes($shoes_id){
    return $this->db->query("SELECT name from hippo_shoe_sizes where id='$shoes_id'")->row()->name;
}
    
  

}
