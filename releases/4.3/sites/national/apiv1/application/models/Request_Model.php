<?php

class Request_Model extends CI_Model
{

    public function getihrisdata()
    {
        $query = $this->db->query("SELECT
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
         CURRENT_TIMESTAMP as last_update,
         CASE WHEN `demographic+gender` ='gender|M' THEN 'Male'
         WHEN `demographic+gender` ='gender|F' THEN 'Female' 
         ELSE 'NULL' END  as gender,
         `demographic+birth_date` as birth_date,
         CASE WHEN `classification+cadre` ='cadre|AHPC' THEN 'Allied Health Professionals'
         WHEN `classification+cadre` ='cadre|Admin' THEN 'Administration Professionals' 
         WHEN `classification+cadre` ='cadre|Dental' THEN 'Dental Professionals' 
         WHEN `classification+cadre` ='cadre|Medical' THEN 'Medical Professionals' 
         WHEN `classification+cadre` ='cadre|Midwife' THEN 'Midwifery Professionals' 
         WHEN `cadre|Non_health` ='cadre|Midwife' THEN 'Non Health Professionals' 
         WHEN `cadre|Nurse` ='cadre|Midwife' THEN 'Nursing Professionals' 
         WHEN `cadre|Non_health` ='cadre|Pharma' THEN 'Pharmacy Professionals' 
         WHEN `cadre|Support` ='cadre|Midwife' THEN 'Support Staffs' 
         ELSE 'Others' END  as cadre
        
        from  `national_manage`.`zebra_staff_list` WHERE `national_id_card_no+id_num` IS NOT NULL AND `institution_type+name`!='UCMB'");
        return $query->result();
    }
    public function getallihrisdata()
    {
        $query = $this->db->query("SELECT
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

    public function attendance_data($from, $to)
    {
        $query = $this->db->query("SELECT `primary_form+parent` as ihris_pid,`position_attedance+facility`
        as facility_id,`primary_form+days_present` as P,`primary_form+days_od` as O, `primary_form+days_or` as
         R, `primary_form+days_leave` as L,DATE_FORMAT(`primary_form+month_year_day`,'%Y-%m') as duty_date 
         from zebra_person_attendance_all WHERE  DATE_FORMAT(`primary_form+month_year_day`,'%Y-%m') between '$from' AND '$to'");
        return $query->result();
    }
    public function practitioner_data($offset = FALSE, $page_limit = FALSE)
    {


        $result = $this->db->query("SELECT * FROM `zebra_ihris_data_api` where `national_id+id_num`!='' and `institution_type+name`!='UCMB' LIMIT $offset, $page_limit")->result_array();


        return $result;
    }
}
