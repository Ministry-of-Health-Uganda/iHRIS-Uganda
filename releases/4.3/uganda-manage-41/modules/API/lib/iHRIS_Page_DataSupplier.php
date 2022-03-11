<?php
/**
* © Copyright 218-present IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License 
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
* @package iHRIS
* @subpackage Manage
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.3.0
* @since v4.3.0
* @filesource 
*/ 
/** 
* Class iHRIS_Page_FHIR_Resource
* 
* @access public
*/


class iHRIS_Page_DataSupplier extends I2CE_Page{

    /**
     * @var DOMDocument The XML document for the resource.
     */
    protected $doc;

    /**
     * @var string The date to use for queries.
     */
    protected $since;
    
    /**
     * @var boolean To use JSON or XML
     */
    protected $useJSON;

    protected $factory;

    /**
     * Handles creating hte I2CE_TemplateMeister templates and loading any default templates
     * @returns boolean true on success
     */
    protected function initializeTemplate() {
        //we don't want any templates for this
        $this->template = new I2CE_Template();
        $this->template->loadRootText('');
        return true;
    }

    /**
     * Perform the main action of this page.
     * @return boolean
     */
    protected function displayWeb($supress_output = false) {
        if ( !array_key_exists( 'resource', $this->args ) || !$this->args['resource'] ) {
            http_response_code(404);
            I2CE::raiseError("No resource set for Resource page.");
            return true;
        }

        /*if ( $this->request('_since') ) {
            $this->since = date('Y-m-d H:i:s', strtotime( $this->request('_since') ) );
        } else {
            $this->since = null;
        }*/
        $this->factory = I2CE_FormFactory::instance();

        if ( $this->request('_id') ) {
            $this->id = $this->request('_id');
        } else {
            $this->id = null;
        }

        if ( $this->request('_name') ) {
            $this->name = $this->request('_name');
        } else {
            $this->name = null;
        }

        if ( $this->request('_facility') ) {
            $this->facility = $this->request('_facility');
        } else {
            $this->facility = null;
        }

        if ( $this->request('_department') ) {
            $this->department = $this->request('_department');
        } else {
            $this->department = null;
        }
    
    if ( $this->request('_district') ) {
            $this->district = $this->request('_district');
        } else {
            $this->district = null;
        }

        $page = array_shift ($this->request_remainder);

        $this->doc = new DOMDocument();
        $this->useJSON = true;


        if ( array_key_exists( 'HTTP_ACCEPT', $_SERVER ) ) {
            $header = new AcceptHeader( $_SERVER['HTTP_ACCEPT'] );
            foreach( $header as $accept ) {
                if ( $accept['type'] == 'xml' ) {
                    break;
                } elseif ( $accept['type'] == 'json' ) {
                    $this->useJSON = true;
                    break;
                } elseif( $accept['type'] == 'text' ) {
                    if ( $accept['subtype'] == 'xml' ) {
                        break;
                    }
                } elseif ( $accept['type'] == 'application' ) {
                    if ( $accept['subtype'] == 'xml' ) {
                        break;
                    } elseif ( $accept['subtype'] == 'json') {
                        $this->useJSON = true;
                        break;
                    }
                }
            }
        }

        if ( $this->get('_format') ) {
            if ( $this->get('_format') == 'json' || $this->get('_format') == 'application/json' ) {
                $this->useJSON = true;
            }
        }


        if ( $page == '_history' ) {
            if ( $this->useJSON  ) {
                //$top = array( "resourceType" => "Bundle" );
                $top = array();
            } else {
                $this->doc->loadXML('<dataValueSet xmlns="http://dhis2.org/schema/dxf/2.0"></dataValueSet>');
                $top = $this->doc->documentElement;
            }
            if ( call_user_func_array( array( $this, "loadData_" . $this->args['resource'] ), array( &$top ) ) ) {
                if ( $this->useJSON ) {
                    echo json_encode( $top, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES );
                } else {
                    echo $this->doc->saveXML();
                }
                return true;
            }
            return false;
        } else {
            if ( $this->useJSON ) {
                $top = array();
            } else {
                $this->doc->loadXML('<' . $this->args['resource'] . ' xmlns="http://dhis2.org/schema/dxf/2.0"></' . $this->args['resource'] . '>');
                $top = $this->doc->documentElement;
            }
            if ( call_user_func_array( array( $this, "loadData_" . $this->args['resource'] ), array( &$top ) ) ) {
                if ( $this->useJSON ) {
                    echo json_encode( $top, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES );
                 } else {
                    echo $this->doc->saveXML();
                }
                return true;
            } else {
                http_response_code(404);
                I2CE::raiseError("Couldn't find resource (".$this->args['resource'].") for $page.");
                return true;
            }
        }

    }


    /**
     * Return the site base used for the current site.
     * @return string
     */
    protected function getSiteBase() {
        return I2CE::getProtocol() . '://' . $_SERVER['HTTP_HOST']  . substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/")) . '/'; 
    }

     /**
     * Load and set the data for Practitioners
     * @param string $uuid
     * @param DOMNode &$top The node to append data to.
     * @return boolean
     */
    protected function loadData_Practitioner ( &$top ) {

        if(isset($this->id)){
            $person_ids = array();
            $ids = I2CE_FormStorage::listFields( 'person_id', array('parent'), false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'equals',
                'field' => 'id_num',
                'data' => array( 'value' => $this->id )
                ), array(), 1 );
            foreach($ids as $id=>$field) {
                $person_ids[] = $field["parent"];
            }
        }elseif(isset($this->name)){
            $where = array( "operator"=>"OR",
                            "operand"=>array (0 => array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"firstname",
                                                      "style"=>"lowerequals",
                                                      "data" => array("value"=> $this->name)),
                                             1 => array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"othername",
                                                      "style"=>"lowerequals",
                                                      "data" => array('value' => $this->name)),
                                             2 => array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"surname",
                                                      "style"=>"lowerequals",
                                                      "data" => array('value' => $this->name)))
                          );
            $person_ids = I2CE_FormStorage::search( 'person', false, $where);

        }elseif(isset($this->facility)){

            $where = array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'contains',
                'field' => 'name',
                'data' => array( 'value' => $this->facility )
            );
            $facilities = array();
            $facility_ids = I2CE_FormStorage::search( 'facility', false, $where);

        
            foreach($facility_ids as $id){
                $facilities[] = "facility|$id";
            }
        
            $person_ids = array();
            $position_ids = array();
            $pids = I2CE_FormStorage::listFields( 'position', array('id'), false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'in',
                'field' => 'facility',
                'data' => array( 'value' => $facilities )
                ),array(),false);
        
                foreach($pids as $id=>$field) {
                    $position_ids[] = "position|".$field["id"];
                }
        
            
            $where = array( "operator"=>"AND",
                            "operand"=>array (0 => array("operator"=>"FIELD_LIMIT",
                                                    'style' => 'in',
                                                    'field' => 'position',
                                                    'data' => array('value' => $position_ids)),
                                             1 => array( "operator"=>"OR",
                                                         "operand"=>array (0 => array("operator"=>"FIELD_LIMIT",
                                                                                  "field"=>"end_date",
                                                                                  "style"=>"null"),
                                                                           1 => array("operator"=>"FIELD_LIMIT",
                                                                                  "field"=>"end_date",
                                                                                  "style"=>"greaterthan_now")))
                                         ));
            $ids = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, $where);
            
            /* $ids = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'in',
                'field' => 'position',
                'data' => array( 'value' => $position_ids )
                ),array(),false); */ 

            foreach($ids as $id=>$field) {
                if(!in_array($field["parent"], $person_ids)){
                    $person_ids[] = $field["parent"];
                }
        
            }
        
        }elseif(isset($this->district)){
            $where = array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'contains',
                'field' => 'name',
                'data' => array( 'value' => $this->district )
            );
            $facilities = array();
            $districts = array();
            $district_ids = I2CE_FormStorage::search( 'district', false, $where);
            foreach($district_ids as $id){
                $districts[] = "district|$id";
 
            }
        
        $fids = I2CE_FormStorage::listFields( 'facility', array('id'), false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'in',
                'field' => 'location',
                'data' => array( 'value' => $districts )
                ),array(),false);
                foreach($fids as $id=>$field) {
                    $facilities[] = $field["id"];
                }
                
            $person_ids = array();
            $position_ids = array();
            $pids = I2CE_FormStorage::listFields( 'position', array('id'), false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'in',
                'field' => 'facility',
                'data' => array( 'value' => $facilities )
                ),array(),false);
                foreach($pids as $id=>$field) {
                    $position_ids[] = $field["id"];
                }
        
        
            $where = array( "operator"=>"AND",
                            "operand"=>array (0 => array("operator"=>"FIELD_LIMIT",
                                                    'style' => 'in',
                                                    'field' => 'position',
                                                    'data' => array('value' => $position_ids)),
                                             1 => array( "operator"=>"OR",
                                                         "operand"=>array (0 => array("operator"=>"FIELD_LIMIT",
                                                                                  "field"=>"end_date",
                                                                                  "style"=>"null"),
                                                                           1 => array("operator"=>"FIELD_LIMIT",
                                                                                  "field"=>"end_date",
                                                                                  "style"=>"greaterthan_now")))
                                         ));
            $ids = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, $where);

            /* $ids = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'in',
                'field' => 'position',
                'data' => array( 'value' => $position_ids )
                ),array(),false);   */


            foreach($ids as $id=>$field) {
                if(!in_array($field["parent"], $person_ids)){
                    $person_ids[] = $field["parent"];
                }
        
        
            }
        }elseif(isset($this->department)){
            $facility_office_ids = array();
            $did = explode("|",$this->department);
            $directorate_ids = I2CE_FormStorage::listFields( 'department', array('directorate'), false, array(
                'operator' => 'FIELD_LIMIT',
                        'style' => 'equals',
                        'field' => 'id',
                        'data' => array( 'value' => $did[1] )
                        ),array(),false);

             
             $where = array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'in',
                'field' => 'office_structure',
                'data' => array( 'value' => $directorate_ids )
             );
            
            $foids = I2CE_FormStorage::search( 'facility_office', false, $where);
            foreach($foids as $id){
                $facility_offices[] = "facility_office|$id";
            }
            $person_ids = array();
            $position_ids = array();
            $pids = I2CE_FormStorage::listFields( 'position', array('id'), false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'in',
                'field' => 'facility_office',
                'data' => array( 'value' => $facility_offices )
                ),array(),false); 
            foreach($pids as $id=>$field) {
                    $position_ids[] = "position|".$field["id"];
                }
            

            $where = array( "operator"=>"AND",
                            "operand"=>array (0 => array("operator"=>"FIELD_LIMIT",
                                                    'style' => 'in',
                                                    'field' => 'position',
                                                    'data' => array('value' => $position_ids)),
                                             1 => array( "operator"=>"OR",
                                                         "operand"=>array (0 => array("operator"=>"FIELD_LIMIT",
                                                                                  "field"=>"end_date",
                                                                                  "style"=>"null"),
                                                                           1 => array("operator"=>"FIELD_LIMIT",
                                                                                  "field"=>"end_date",
                                                                                  "style"=>"greaterthan_now")))
                                         ));
            $ids = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, $where);

            /* $ids = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'in',
                'field' => 'position',
                'data' => array( 'value' => $position_ids )
                ),array(),false);  */

            foreach($ids as $id=>$field) {
                if(!in_array($field["parent"], $person_ids)){
                    $person_ids[] = $field["parent"];
                }
            }
        } else {
            $person_ids = array();
            $search_person_ids = array();
            $search_person_ids = I2CE_FormStorage::search( 'person', false);

            $where = array( "operator"=>"OR",
                            "operand"=>array (0 => array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"end_date",
                                                      "style"=>"null"),
                                             1 => array("operator"=>"FIELD_LIMIT",
                                                      "field"=>"end_date",
                                                      "style"=>"greaterthan_now"))
                          );
            $ids = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, $where);

            //$ids = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, array() ,array(),false);
            foreach($ids as $id=>$field) {
                if(!in_array($field["parent"], $search_person_ids)){
                    $person_ids[] = $field["parent"];
                }
            }
        }

        //$this->userMessage( "Request: ".$this->id. " Name " . $this->name . " Council ". $this->council . " Cadre " . $this->cadre ." RegNo " . $this->regNo );
                       
        /*if($uuid){
            $person_ids = I2CE_FormStorage::search( 'person', false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'equals',
                'field' => 'csd_uuid',
                'data' => array( 'value' => $uuid )
                ), array(), 1 );
        }else{*/
           
       // }
        if(count($person_ids) > 0){
            if ( $this->useJSON ) {
            }
            foreach ($person_ids as $person_id){
               // I2CE::raiseError("Person Ids $person_id");
                if ( !$person_id ) {
                    return false;
                } else {
                    $entry = array();
                    $person = I2CE_FormFactory::instance()->createContainer( "person|$person_id" );
                    $person->populate();
                    
                    $data = array();
        
                    $data['uuid'] =  $person->uuid;
                    $data['lastUpdated'] = $person->getField('last_modified')->getValue()->getDateTimeObj()->format('c');
                    $data['id_system'] = $this->getSiteBase();
                    $data['ihrisID'] = $person->getNameId();
                    $data['active'] = 'true';
                    $data['surname'] = $person->surname;
                    $data['firstname'] = $person->firstname;
                    $data['othername'] = $person->othername;
                    //$data['nationality'] = $person->getField('nationality')->getDisplayValue();
        
                    $person->populateChildren('demographic');
                    $demo = current($person->getChildren('demographic'));
                    if ( $demo ) {
                        if ( $demo->getField('gender')->getDBValue() == 'gender|M' ) {
                            $data['gender'] = 'Male';
                        } elseif ( $demo->getField('gender')->getDBValue() == 'gender|F' ) {
                            $data['gender'] = 'Female';
                        }
                        if ( !$demo->birth_date->isBlank() ) {
                            $birth = $demo->birth_date->dbFormat();
                            $data['birthDate'] = $birth;
                        }
                       
                        //$data['marital_status'] = $demo->getField('marital_status')->getDisplayValue();
                    }
                    /*
                    $data['contact'] = array( 
                            'personal' => array( 'phone' => array(), 'email' => array(), 'mobile_phone' => array() ), 
                            'emergency' => array( 'phone' => array(),
                                                    'mobile_phone' => array(),
                                                    'email' => array(), 
                                                    'firstname' => array(),
                                                    'othername' => array(),
                                                    'surname' => array()
                                                )
                            );
                    
                    $person->populateChildren('person_contact_emergency');
                    $emergency = current($person->getChildren('person_contact_emergency'));
                    if ( $emergency ) {
                        $data['contact']['emergency'] = array();
                        if ( $emergency->telephone ) {
                            $data['contact']['emergency']['phone'][] = $emergency->telephone;
                        }
                        if ( $emergency->mobile_phone ) {
                            $data['contact']['emergency']['mobile_phone'][] = $emergency->mobile_phone;
                        }
                        if ( $emergency->email ) {
                            $data['contact']['emergency']['email'][] = $emergency->email;
                        }
                        $data['contact']['emergency']['name'][] = "".$emergency->firstname." ". $emergency->othername." ".$emergency->surname;
                    }
                    */
                    $person->populateChildren('person_contact_personal');
                    $personal = current($person->getChildren('person_contact_personal'));
                    if ( $personal ) {
                        $data['contact']['personal'] = array();
                        if ( $personal->telephone ) {
                            $data['contact']['personal']['phone'][] = $personal->telephone;
                        }
                        if ( $personal->mobile_phone ) {
                            $data['contact']['personal']['mobile_phone'][] = $personal->mobile_phone;
                        }
                        if ( $personal->email ) {
                            $data['contact']['personal']['email'][] = $personal->email;
                        }
            
                    }
                    /*
                    $data['address'] = array( 
                        'permanent' => array( 'location' => array(),
                                                'ward' => array(),
                                                'place' => array(),
                                                'house' => array(), 
                                                'postal_code' => array() 
                                            ), 
                        'current' => array( 'location' => array(),
                                                'ward' => array(),
                                                'place' => array(),
                                                'house' => array(), 
                                                'postal_code' => array()
                                            )
                        );
                        */
                        
                        $data['identification'] = array();
                        $id_count = 0;
                        $person->populateChildren('person_id');
                        foreach($person->getChildren('person_id') as $person_id_form ){
                            $data['identification'][$id_count] = array();
                            $data['identification'][$id_count]['id_type'] = $person_id_form->getField('id_type')->getDisplayValue();
                            $data['identification'][$id_count]['id_num'] = $person_id_form->id_num;
                           // $data['identification'][$id_count]['expirationDate'] = $person_id_form->expiration_date;
                           // if ( !$person_id_form->expiration_date->isBlank() ) {
                           //     $expiration_date = $person_id_form->expiration_date->dbFormat();
                           //     $data['identification'][$id_count]['expirationDate'] = $expiration_date;
                           // }
                            $id_count++;
                        }
                        /*
                        $data['registration'] = array();
                        $registration_count = 0;
                        $person->populateChildren('nepal_registration');
                        foreach($person->getChildren('nepal_registration') as $registration ){
                            $data['registration'][$registration_count] = array();
                            $cadre_id = $registration->getField('cadre')->getDBValue();
                            if($cadre_id){
                                $cadre = $this->factory->createContainer($cadre_id);
                                $cadre->populate();    
                                $data['registration'][$registration_count]['council'] = $cadre->getField('council')->getDisplayValue();   
                            }
                            $data['registration'][$registration_count]['cadre'] = $registration->getField('cadre')->getDisplayValue();
                            $data['registration'][$registration_count]['degree'] = $registration->getField('degree')->getDisplayValue(); 
                            $data['registration'][$registration_count]['registration_no'] = $registration->registration_no;
                            $data['registration'][$registration_count]['registrationDate'] = $registration->date->dbFormat();
                            $data['registration'][$registration_count]['expiryDate'] = $registration->expiry_date->dbFormat();
                            $registration_count++;
                        }
        
                        $data['education'] = array();
                        $education_count = 0;
                        $person->populateChildren('nepal_education');
                        foreach($person->getChildren('nepal_education') as $education ){
                            $data['education'][$education_count] = array();
                            $degree_id = $education->getField('degree')->getDBValue();
                            if($degree_id){
                                $degree = $this->factory->createContainer($degree_id);
                                $degree->populate();    
                                $data['education'][$education_count]['edu_type'] = $degree->getField('edu_type')->getDisplayValue();   
                            }
                            $data['education'][$education_count]['degree'] = $education->getField('degree')->getDisplayValue();
                            $data['education'][$education_count]['specialization'] = $education->getField('specialization')->getDisplayValue();
                            $data['education'][$education_count]['country'] = $education->getField('country')->getDisplayValue();
                            $data['education'][$education_count]['address'] = $education->getField('location')->getDisplayValue();
                            $data['education'][$education_count]['institution'] = $education->getField('institution')->getDisplayValue();
                            $data['education'][$education_count]['passoutDate'] = $education->date_of_passout->dbFormat();
                            $data['education'][$education_count]['passoutDateNepali'] = $education->date_of_passout_nep;
                            $education_count++;
                        }
        
                        $data['employment'] = array();
                        $employment_count = 0;
                        $person->populateChildren('employment');
                        foreach($person->getChildren('employment') as $employment ){
                            $data['employment'][$employment_count] = array();
                            $data['employment'][$employment_count]['facility'] = $employment->getField('facility')->getDisplayValue();
                            $data['employment'][$employment_count]['position'] = $employment->position;
                            $data['employment'][$employment_count]['startDate'] = $employment->start_date->dbFormat();
                            if ( !$employment->end_date->isBlank() ) {
                                $data['employment'][$employment_count]['endDate'] = $employment->end_date->dbFormat();
                            }
                            $employment_count++;
                        }
                        */
                        //$data['position'] = array();
                /* $person->populateChildren('person_position');
                        $person_position = current($person->getChildren('person_position'));
                        if($person_position){
                            $positionID = $person_position->getField('position')->getDBValue();
                            $position = I2CE_FormFactory::instance()->createContainer( $positionID); */

                    $person->populateChildren('person_position');            
                    $current_person_position = 0;                    
                    if (count($person->children['person_position']) > 0){
                        foreach ($person->children['person_position'] as $person_position){
                            //$person_position->getfieldID;
                            // echo $person_positon->getfield('id')->getDBValue();
                             $values = explode("|", $person_position->getfield('id')->getDBValue());
                            
                            if((int)$values[1] > $current_person_position){
                                $current_person_position = (int)$values[1];
                                $positionID = $person_position->getField('position')->getDBValue();
                            }    
                        }
                    }                    
                    if($current_person_position){                       
                            $position = I2CE_FormFactory::instance()->createContainer( $positionID);
                            if($positionID){
                                $position->populate();
                                $data['position'] = $position->getField('job')->getDisplayValue();
                                $data['positionID'] = $position->getField('job')->getDBValue();
                                $data['facility'] = $position->getField('facility')->getDisplayValue();
                                $data['facilityID'] = $position->getField('facility')->getDBValue();
                                $facility_id = $position->getField('facility_office')->getDBValue();
                                if($facility_id){
                                    $facility_office = I2CE_FormFactory::instance()->createContainer($facility_id);
                                    $facility_office->populate();
                                    $office_structure = $facility_office->getField('office_structure')->getDBValue();

                                    if(strpos("$office_structure","department") !== false){
                                        $office_structure = $office_structure;
                                    }elseif(strpos("$office_structure","division") !== false){
                                        $division = I2CE_FormFactory::instance()->createContainer($office_structure);
                                                    $division->populate();
                                        $department_id = $division->getField('department')->getDBValue(); 
                                        $office_structure = $department_id;
                                    }elseif(strpos("$office_structure","section") !== false){
                                        $section = I2CE_FormFactory::instance()->createContainer($office_structure);
                                                    $section->populate();
                                        $division_id = $section->getField('division')->getDBValue();
                                        $division = I2CE_FormFactory::instance()->createContainer($division_id);
                                                    $division->populate();
                                        $department_id = $division->getField('department')->getDBValue(); 
                                        $office_structure = $department_id;
                                    }elseif(strpos("$office_structure","unit") !== false){
                                        $unit = I2CE_FormFactory::instance()->createContainer($office_structure);
                                                    $unit->populate();
                                        $section_id = $unit->getField('section')->getDBValue();
                                        $section = I2CE_FormFactory::instance()->createContainer($section_id);
                                                    $section->populate();
                                        $division_id = $section->getField('division')->getDBValue();
                                        $division = I2CE_FormFactory::instance()->createContainer($division_id);
                                                    $division->populate();
                                        $department_id = $division->getField('department')->getDBValue(); 
                                        $office_structure = $department_id;
                                    }

                                    $department = I2CE_FormFactory::instance()->createContainer($office_structure);
                                                    $department->populate();
                                    $department = $department->getField('name')->getDBValue();

                			        if($office_structure){
                					
                				        $data['departmentID'] = $office_structure; 
                				        $data['department'] = $department;
                				    }
                                
                                    unset($facility_office);
                                }

                                unset($position);
                            }
                        
                        //$data['facility'] = $person_position->getField('facility')->getDisplayValue();
                        //$data['facilityID'] = $person_position->getField('facility')->getDBValue();
                        $data['employment_terms'] = $person_position->getField('employment_terms')->getDisplayValue();
                        $data['endDate'] = $person_position->end_date->dbFormat();
                        
                    }
                    if ( $this->useJSON ) {
                        $this->create_Practitioner( $data, $entry['Practitioner'] );
                        $top['entry'][] = $entry;
                    }
                //$this->create_Practitioner( $data, $top );
        
                }
            }
        }else {
            I2CE::raiseError("No Data set for Practitioner Resource page.");
        }     
        return true;
    }

    /**
     * Create a practitioner FHIR object based on the given array
     * Valid format is:
     * $data = array( 
     *         'uuid' => VALUE,
     *         'lastUpdated' => 'YYYY-MM-DDTHH:MM:SS-HH:MM'
     *         'id_system' => 'identifier system',
     *         'id_code' => 'iHRIS identifier code',
     *         'active' => 'true|false',
     *         'surname' => 'surname',
     *         'firstname' => 'firstname',
     *         'othername' => 'othername',
     *          surname_nepali' => 'surname_nepali',
     *         'firstname_nepali' => 'firstname_nepali',
     *         'othername_nepali' => 'othername_nepali',
     *         'gender' => 'Male|Female',
     *         'birthDate' => 'YYYY-MM-DD',
     *         'birthDateNep' => 'YYYY-MM-DD',
     *         'caste' =>'caste',
     *         'religion' => 'religion',
     *         'marital_status' => 'marital_status',
     *         'contact' => array( 
     *                      'personal' => array( 'phone' => array( '#', ... ), 'email' => array( '@', ... ) ),
     *                      'emergency' => array( 'phone' => array( '#', ... ),'email' => array( '@', ... ), ....),
     *                      ),
     *         'address' => array(
     *                      'permanent' => array( 'location' => 'location',
     *                                   'ward' => 'ward',
     *                                  'place' => 'place',
     *                                  'house' => 'house', 
     *                                  'postal_code' => 'postal_code' 
     *                               ), 
     *                      'current' => array( 'location' => 'location',
     *                                   'ward' => 'ward',
     *                                  'place' => 'place',
     *                                  'house' => 'house', 
     *                                  'postal_code' => 'postal_code' 
     *                               )
     *                          ),
     *          .......
     *         
     *         );
     *
     * @param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_Practitioner( $data, &$top ) {

        if ( $this-> useJSON ) {
            $top['id'] = $data['uuid'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('ihrisID', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['ihrisID'] );
            }

            if ( array_key_exists( 'active', $data ) ) {
                $top['active'] = ( $data['active'] == 'true' );
            }

            $top['name'] = array();
            $top['name'] = array( 'firstname' => $data['firstname'] , 'othername' => $data['othername'], 'surname' => $data['surname']);
            
            if( array_key_exists( 'gender', $data ) ) {
                $top['gender'] = $data['gender'];
            }
            if ( array_key_exists( 'birthDate', $data ) ) {
                $top['birthDate'] = $data['birthDate'];
            }
            $element = array();
            if ( array_key_exists( 'identification', $data ) ) {
                $top['identification'] = array();
                foreach( $data['identification'] as $ids => $id) {
                    foreach ( $id as $field => $value ) {
                        $element[$field] = $value;
                    }
                    $top['identification'][$ids] = $element;
                }
            }
        $top['facility'] = array();
            $top['facility'] = array('facilityID' => $data['facilityID'], 'facility_name' => $data['facility']);
            //$top['facility'] = $data['facility'];
        $top['position'] = array();
            $top['position'] = array('positionID' => $data['positionID'], 'position' => $data['position']);
            //$top['position'] = $data['position'];
        $top['department'] = array();
            $top['department'] = array('departmentID' => $data['departmentID'], 'department' => $data['department']);
            //$top['department'] = $data['department'];
            $top['employment_terms'] = $data['employment_terms'];
            $top['endDate'] = $data['endDate'];
           
            if ( array_key_exists( 'contact', $data ) ) {
                $top['contact'] = array();
                foreach ( $data['contact'] as $use => $contact ) {
                    foreach ( $contact as $system => $telecoms ) {
                        foreach ( $telecoms as $telecom ) {
                            $top['contact'][] = array( 'system' => $system, 'use' => $use, 'value' => $telecom );
                        }
                    }
                }
            }
            /* 
            $element = array();
            if ( array_key_exists( 'address', $data ) ) {
                $top['address'] = array();
                foreach( $data['address'] as $type => $address ) {
                    foreach ( $address as $field => $value ) {
                        $element[$field] = $value;
                    }
                    $top['address'][$type] = $element;
                }
            }*/
            /*
            $element = array();
            if ( array_key_exists( 'registration', $data ) ) {
                $top['registration'] = array();
                foreach( $data['registration'] as $registrations => $registration) {
                    foreach ( $registration as $field => $value ) {
                        $element[$field] = $value;
                    }
                    $top['registration'][$registrations] = $element;
                }
            }
            $element = array();
            if ( array_key_exists( 'education', $data ) ) {
                $top['education'] = array();
                foreach( $data['education'] as $educations => $education) {
                    foreach ( $education as $field => $value ) {
                        $element[$field] = $value;
                    }
                    $top['education'][$educations] = $element;
                }
            }
            $element = array();
            if ( array_key_exists( 'employment', $data ) ) {
                $top['employment'] = array();
                foreach( $data['employment'] as $employments => $employment) {
                    foreach ( $employment as $field => $value ) {
                        $element[$field] = $value;
                    }
                    $top['employment'][$employments] = $element;
                }
            }*/
         } else{

            I2CE::raiseError("No resource set for Practitioner Resource page.");
          }

    }

     /**
     * Load and set the data for Departments
     * @param DOMNode &$top The node to append data to.
     * @return boolean
     */
    protected function loadData_Department( &$top ) {
        if(isset($this->id)){
           // $ids = $this->id;
      $ids = array();
          $ids[] = $this->id;
        } else {
            $ids = I2CE_FormStorage::search( 'department', false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'equals',
                'field' => 'i2ce_hidden',
                'data' => array( 'value' => 0 )));
        }
        if(count($ids) > 0){
            foreach ($ids as $id){
                if ( !$id ) {
                    return false;
                } else {
                    $entry = array();
                    $department = I2CE_FormFactory::instance()->createContainer( "department|$id" );
                    $department->populate();
                    
                    $data = array();
                    $data['lastUpdated'] = $department->getField('last_modified')->getValue()->getDateTimeObj()->format('c');
                    $data['id_system'] = $this->getSiteBase();
                    $data['ihrisID'] = $department->getNameId();
                    $data['active'] = 'true';
                    $data['name'] = $department->name;
                    $data['directorate'] = $department->getField('directorate')->getDisplayValue();
                    $data['directorateID'] = $department->getField('directorate')->getDBValue();
                    unset($department);
                    if ( $this->useJSON ) {
                        $this->create_Department( $data, $entry['Department'] );
                        $top['entry'][] = $entry;
                    }
                }
            }
        } else {

            I2CE::raiseError("No Data set for Department Resource page.");
        }

        return true;
    }
    /**
     * Create a practitioner FHIR object based on the given array
     * *@param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_Department( $data, &$top ) {

        if ( $this-> useJSON ) {
            $top['id'] = $data['ihrisID'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('ihrisID', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['ihrisID'] );
            }

            if ( array_key_exists( 'active', $data ) ) {
                $top['active'] = ( $data['active'] == 'true' );
            }

            $top['name'] = $data['name'];
            $top['directorate'] = array();
            $top['directorate'] = array('directorate' => $data['directorate'], 'directorateID' => $data['directorateID']);

        } else{

            I2CE::raiseError("No resource set for Department Resource page.");
          }
    }

     /**
     * Load and set the data for Facilities
     * @param DOMNode &$top The node to append data to.
     * @return boolean
     */
    protected function loadData_Facility( &$top ) {
        if(isset($this->id)){
           // $ids = $this->id;
      $ids = array();
          $ids[] = $this->id;
        } else {
            $ids = I2CE_FormStorage::search( 'facility', false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'equals',
                'field' => 'i2ce_hidden',
                'data' => array( 'value' => 0 )));
        }
        if(count($ids) > 0){
            foreach ($ids as $id){
                if ( !$id ) {
                    return false;
                } else {
                    $entry = array();
                    $facility = I2CE_FormFactory::instance()->createContainer( "facility|$id" );
                    $facility->populate();
                    
                    $data = array();
                    $data['lastUpdated'] = $facility->getField('last_modified')->getValue()->getDateTimeObj()->format('c');
                    $data['id_system'] = $this->getSiteBase();
                    $data['ihrisID'] = $facility->getNameId();
                    $data['active'] = 'true';
                    $data['name'] = $facility->name;
                    $data['location'] = $facility->getField('location')->getDisplayValue();
                    $data['location_id'] = $facility->getField('location')->getDBValue();
                    $data['facility_type'] = $facility->getField('facility_type')->getDisplayValue();
                    $data['facility_type_id'] = $facility->getField('facility_type')->getDBValue();
                    unset($facility);
                    if ( $this->useJSON ) {
                        $this->create_Facility( $data, $entry['Facility'] );
                        $top['entry'][] = $entry;
                    }
                }
            }
        } else {

            I2CE::raiseError("No Data set for Facility Resource page.");
        }

        return true;
    }
    /**
     * Create a practitioner FHIR object based on the given array
     * *@param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_Facility( $data, &$top ) {

        if ( $this-> useJSON ) {
            $top['id'] = $data['ihrisID'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('ihrisID', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['ihrisID'] );
            }

            if ( array_key_exists( 'active', $data ) ) {
                $top['active'] = ( $data['active'] == 'true' );
            }

            $top['name'] = $data['name'];
            $top['facilityType'] = array();
            $top['facilityType'] = array('name' => $data['facility_type'], 'ihrisID' => $data['facility_type_id']);
            $top['location'] = array();
            $top['location'] = array('name' => $data['location'], 'ihrisID' => $data['location_id']);

        } else{

            I2CE::raiseError("No resource set for Department Resource page.");
          }
    }

     /**
     * Load and set the data for Designations
     * @param DOMNode &$top The node to append data to.
     * @return boolean
     */
    protected function loadData_Designation( &$top ) {
        if(isset($this->id)){
           // $ids = $this->id;
      $ids = array();
          $ids[] = $this->id;
        } else {
            $ids = I2CE_FormStorage::search( 'job', false, array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'equals',
                'field' => 'i2ce_hidden',
                'data' => array( 'value' => 0 )));
        }
        if(count($ids) > 0){
            foreach ($ids as $id){
                if ( !$id ) {
                    return false;
                } else {
                    $entry = array();
                    $job = I2CE_FormFactory::instance()->createContainer( "job|$id" );
                    $job->populate();
                    
                    $data = array();
                    $data['lastUpdated'] = $job->getField('last_modified')->getValue()->getDateTimeObj()->format('c');
                    $data['id_system'] = $this->getSiteBase();
                    $data['ihrisID'] = $job->getNameId();
                    $data['active'] = 'true';
                    $data['name'] = $job->title;
                    $data['classification'] = $job->getField('classification')->getDisplayValue();
                    $data['classification_id'] = $job->getField('classification')->getDBValue();
                    $data['salary_grade'] = $job->getField('salary_grade')->getDisplayValue();
                    $data['salary_grade_id'] = $job->getField('salary_grade')->getDBValue();
                    unset($job);
                    if ( $this->useJSON ) {
                        $this->create_Designation( $data, $entry['Designation'] );
                        $top['entry'][] = $entry;
                    }
                }
            }
        } else {

            I2CE::raiseError("No Data set for Designation Resource page.");
        }

        return true;
    }
    /**
     * Create a practitioner FHIR object based on the given array
     * *@param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_Designation( $data, &$top ) {

        if ( $this-> useJSON ) {
            $top['id'] = $data['ihrisID'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('ihrisID', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['ihrisID'] );
            }

            if ( array_key_exists( 'active', $data ) ) {
                $top['active'] = ( $data['active'] == 'true' );
            }

            $top['name'] = $data['name'];
            $top['classification'] = array();
            $top['classification'] = array('name' => $data['classification'], 'ihrisID' => $data['classification_id']);
            $top['salaryGrade'] = array();
            $top['salaryGrade'] = array('name' => $data['salary_grade'], 'ihrisID' => $data['salary_grade_id']);

        } else{

            I2CE::raiseError("No resource set for Designation Resource page.");
          }
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
