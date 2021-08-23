<?php 

Class Request_Model extends CI_Model
{

    public function getihrisdata()
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
        `department+name` as department,
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
        
        from  `districts_manage`.`zebra_staff_list` WHERE `national_id_card_no+id_num` IS NOT NULL");
    return $query->result();
           
    }
}