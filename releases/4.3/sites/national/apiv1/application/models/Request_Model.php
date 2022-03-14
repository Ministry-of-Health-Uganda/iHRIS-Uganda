<?php 

Class Request_Model extends CI_Model
{

    public function getihrisdata()
    {
        $query=$this->db->query("SELECT
        trim(`person+id`) as ihris_pid,
        trim(`district+name`) as district_id,
        `district+name` as district,
         'dhis_facility_id',
         'dhis_district_id',
        `national_id+id_num` as nin,
        `national_id_card_no+id_num` as card_number,
        `ipps_no+id_num` as ipps,
        `facility+facility_type` as facility_type_id,
        trim(`facility+id`) as facility_id,
        `facility+name` as facility,
        `department_structure+name` as department_id,
        `department_structure+name` as department,
        trim(`job+id`) as job_id,
        `job+title` as job,
        `primary_form+employment_terms` as employment_terms,
        `person+surname` as surname,
        `person+firstname` as firstname,
        `person+othername` as othername,
        `person_contact_personal+mobile_phone` as mobile,
        `person_contact_personal+telephone` as telephone,
        `institution_type+name` as institutiontype_name,
         `institution_type+id` as institution_type_id,
         CURRENT_TIMESTAMP as last_update
        
        from  `national_manage`.`zebra_staff_list` WHERE `national_id_card_no+id_num` IS NOT NULL AND `institution_type+name`!='UCMB'");
    return $query->result();
           
    }
    public function getallihrisdata()
    {
        $query=$this->db->query("SELECT
        trim(`person+id`) as ihris_pid,
        trim(`district+id`) as district_id,
        `district+name` as district,
         'dhis_facility_id',
         'dhis_district_id',
        `national_id+id_num` as nin,
        `national_id_card_no+id_num` as card_number,
        `ipps_no+id_num` as ipps,
        `facility+facility_type` as facility_type_id,
        trim(`facility+id`) as facility_id,
        `facility+name` as facility,
        `department_structure+name` as department_id,
        `department_structure+name` as department,
        trim(`job+id`) as job_id,
        `job+title` as job,
        `person+surname` as surname,
        `person+firstname` as firstname,
        `person+othername` as othername,
        `person_contact_personal+mobile_phone` as mobile,
        `person_contact_personal+telephone` as telephone,
        `institution_type+name` as institutiontype_name,
         `institution_type+id` as institution_type_id,
         CURRENT_TIMESTAMP as last_update
        
        from  `national_manage`.`zebra_staff_list`");
    return $query->result();
           
    }

    public function attendance_data($from,$to)
    {
        $query=$this->db->query("SELECT `primary_form+parent` as ihris_pid,`position_attedance+facility`
        as facility_id,`primary_form+days_present` as P,`primary_form+days_od` as O, `primary_form+days_or` as
         R, `primary_form+days_leave` as L,DATE_FORMAT(`primary_form+month_year_day`,'%Y-%m') as duty_date 
         from zebra_person_attendance_all WHERE  DATE_FORMAT(`primary_form+month_year_day`,'%Y-%m') between '$from' AND '$to'");
    return $query->result();
           
    }
    public function practitioner_data($district){

        if(!empty($district)){
            $filter="where  `district+name`='$district'";
        }
        else{
            $filter="";
        }
        $result = $this->db->query("SELECT s.* , TO_BASE64(a.`Photo+image`) as `imagedata`, m.name as 'marital_status',c.name as 'country_name', hc.name as cadre_name, hpc.name as council FROM `zebra_staff_list` s join `zebra_staff_album` a ON a.`Photo+id`=s.`Photo+id` right join `hippo_marital_status` m on m.id=s.`demographic+marital_status`  join `hippo_country` c on c.id = s.`person+nationality` join `hippo_cadre` hc on s.`classification+cadre` = hc.id join `hippo_council` hpc on s.`registration+council` = hpc.id LIMIT 50")->result_array();

    
    return $result;
    }

    
   
}