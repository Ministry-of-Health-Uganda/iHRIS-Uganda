<?php
/**
* © Copyright 2018-present IntraHealth International, Inc.
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


class iHRIS_Page_FHIR_Resource extends I2CE_Page{

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

    /**
     * Handles creating hte I2CE_TemplateMeister templates and loading any default templates
     * @returns boolean true on success
     */
    protected function initializeTemplate() {
        //we don't want any templates for this
        //but we need to make something for post processing calls that
        //may happen
        $this->template = new I2CE_Template();
        $this->template->loadRootText('');
        return true;
    }



    /**
     * Perform the main action of this page.
     * @return boolean
     */
    protected function _display($supress_output = false) {
        
        if ( !array_key_exists( 'resource', $this->args ) || !$this->args['resource'] ) {
            http_response_code(404);
            I2CE::raiseError("No resource set for FHIR Resource page.");
            return true;
        }

        if ( $this->request('_since') ) {
            $this->since = date('Y-m-d H:i:s', strtotime( $this->request('_since') ) );
        } else {
            $this->since = null;
        }
        if ( $this->request('_name') ) {
            $this->name = $this->request('_name');
        } else {
            $this->name = null;
        }

        if ( $this->request('_orgunit') ) {
            $this->facility = $this->request('_orgunit');
        } else {
            $this->facility = null;
        }

        if ( $this->request('_district') ) {
            $this->district = $this->request('_district');
        } else {
            $this->district = null;
        }

        if ( $this->request('_parish') ) {
            $this->parish = $this->request('_parish');
        } else {
            $this->parish = null;
        }

        if ( $this->request('_village') ) {
            $this->village = $this->request('_village');
        } else {
            $this->village = null;
        }
        $page = array_shift ($this->request_remainder);

        $this->doc = new DOMDocument();
        $this->useJSON = false;


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
                    if ( $accept['subtype'] == 'xml' || $accept['subtype'] == 'fhir+xml' || $accept['subtype'] == 'xml+fhir' ) {
                        break;
                    } elseif ( $accept['subtype'] == 'json' || $accept['subtype'] == 'fhir+json' || $accept['subtype'] == 'json+fhir' ) {
                        $this->useJSON = true;
                        break;
                    }
                }
            }
        }

        if ( $this->get('_format') ) {
            if ( $this->get('_format') == 'json' ||
                    $this->get('_format') == 'application/json' ||
                    $this->get('_format') == 'application/fhir+json' ||
                    $this->get('_format') == 'application/json+fhir' ) {
                $this->useJSON = true;
            }
        }

        if ( $page == '_history' ) {
            
            if ( $this->useJSON  ) {
                $top = array( "resourceType" => "Bundle" );
            } else {
                $this->doc->loadXML('<Bundle xmlns="http://hl7.org/fhir"></Bundle>');
                $top = $this->doc->documentElement;
            }
            if ( call_user_func_array( array( $this, "getUpdates_" . $this->args['resource'] ), array( &$top ) ) ) {
                if ( $this->useJSON ) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode( $top, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES );
                } else {
                    echo $this->doc->saveXML();
                }
                return true;
            }
            return false;
        } else {
            if ( $this->useJSON ) {
                $top = array( "resourceType" => $this->args['resource'] );
            } else {
                $this->doc->loadXML('<' . $this->args['resource'] . ' xmlns="http://hl7.org/fhir"></' . $this->args['resource'] . '>');
                $top = $this->doc->documentElement;
            }
            if ( call_user_func_array( array( $this, "loadData_" . $this->args['resource'] ), array( $page, &$top ) ) ) {
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
    protected function loadData_Practitioner( $uuid, &$top ) {
        //echo "Tracing: Enter Load practitioner!";

        $person_id = I2CE_FormStorage::search( 'person', false, array(
                    'operator' => 'FIELD_LIMIT',
                    'style' => 'equals',
                    'field' => 'csd_uuid',
                    'data' => array( 'value' => $uuid )
                    ), array(), 1 );
        if ( !$person_id ) {
            return false;
        } else {
            $person = I2CE_FormFactory::instance()->createContainer( "person|$person_id" );
            $person->populate();
            
            $data = array();

            $data['uuid'] = $uuid;
            $data['lastUpdated'] = $person->getField('last_modified')->getValue()->getDateTimeObj()->format('c');
            $data['id_system'] = $this->getSiteBase();
            $data['id_code'] = $person->getNameId();
            $data['active'] = 'true';
            $data['fullname'] = $person->firstname . " " . $person->othername . " " . $person->surname;
            $data['family'] = $person->surname;
            $data['given'] = array( $person->firstname, $person->othername );
            if ( $person->getField('gender')->getDBValue() == 'gender|M' ) {
                $data['gender'] = 'male';
            } elseif ( $person->getField('gender')->getDBValue() == 'gender|F' ) {
                $data['gender'] = 'female';
            }
            if ( !$person->birth_date->isBlank() ) {
                $birth = $person->birth_date->dbFormat();
                $data['birthDate'] = $birth;
            }
        
            $data['telecom'] = array(  
                    'mobile' => array( 'phone' => array() ), 
                    'home' => array( 'phone' => array() )
                    );
            $data['telecom']['work']['phone'][] = $person->alt_telephone;
            $data['telecom']['mobile']['phone'][] = $person->mobile_phone;
            
            //get additional identification
            $data['identification'] = array();
            $id_count = 0;
            $data['identification'][$id_count] = array();
            $data['identification'][$id_count]['id_type'] = "NIN";
            $data['identification'][$id_count]['id_num'] = $person->national_id;

            /*$person->populateChildren('person_id');
            foreach($person->getChildren('person_id') as $person_id_form ){
                $data['identification'][$id_count] = array();
                $data['identification'][$id_count]['id_type'] = $person_id_form->getField('id_type')->getDisplayValue();
                $data['identification'][$id_count]['id_num'] = $person_id_form->id_num;
                if(isset($person_id_form->place))
                {
                    $data['identification'][$id_count]['assigner'] = $person_id_form->place;
                }
                if ( !$person_id_form->expiration_date->isBlank() ) {
                    $expiration_date = $person_id_form->expiration_date->dbFormat();
                    $data['identification'][$id_count]['expirationDate'] = $expiration_date;
                }
                if (!$person_id_form->issue_date->isBlank() ) {
                    $issue_date = $person_id_form->issue_date->dbFormat();
                    $data['identification'][$id_count]['issueDate'] = $issue_date;
                }
                $id_count++;
            }*/
            $data['communication'] = array();
            $comCount = 0;
            $person->populateChildren('person_language');
            foreach($person->getChildren('person_language') as $person_language_form ){
                $data['communication'][$comCount]=array();
                $data['communication'][$comCount]['language']= $person_language_form->getField('language')->getDisplayValue();
                $data['communication'][$comCount]['reading']= $person_language_form->reading;
                $data['communication'][$comCount]['writing']= $person_language_form->writing;
                $data['communication'][$comCount]['speaking']= $person_language_form->speaking;
                $comCount++;
            }

            $data['education'] = array();
            $id_count = 0;
            $person->populateChildren('education');
            foreach($person->getChildren('education') as $person_education_form ){
                $data['education'][$id_count]=array();
                $data['education'][$id_count]['degree']= $person_education_form->getField('edu_type')->getDisplayValue();
                $data['education'][$id_count]['institution']=$person_education_form->institution;
                $data['education'][$id_count]['location']=$person_education_form->location;
                $data['education'][$id_count]['end_year']=$person_education_form->getField('year')->getValue()->getDateTimeObj()->format('c');

            }
            
            $this->create_Practitioner( $data, $top );

        }
        return true;
    }
       
    /**
     * Create the bundle of practitioners updated since a given time.
     * @param DOMNode $top
     * @return boolean
     */
    protected function getUpdates_Practitioner( &$top ) {
        //echo "Tracing: Enter updatge practitioner!";
        if ( $this->useJSON ) {
            $top['type'] = 'history';
            $top['total'] = 0;
        } else {
            $type = $this->doc->createElement("type");
            $type->setAttribute("value", "history");
            $top->appendChild($type);

            $total = $this->doc->createElement("total");
            $top->appendChild($total);
        }


        $required_forms = array( "person","person_language","language","education", "edu_type" );
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }

        $where = '';
        if ( $this->since ) {
            $where = "WHERE GREATEST(person.last_modified, '0000-00-00 00:00:00') >= ". $this->since;
        }

        $qry = "SELECT person.csd_uuid AS uuid, DATE_FORMAT(GREATEST(person.last_modified,'0000-00-00 00:00:00'), '%Y-%m-%d %T') AS lastupdated, 
        person.id, person.surname AS family, person.firstname AS given, person.othername AS given2, person.gender,
        DATE_FORMAT(person.birth_date,'%Y-%m-%d') AS birthdate, person.alt_telephone AS home_phone, person.mobile_phone AS home_mobile, 
        person.national_id AS national_id 
        FROM hippo_person AS person 
        $where ORDER BY lastupdated ASC";
        
        $db = MDB2::singleton();
		$results = $db->query( $qry );
        $count = 0;
        if ( $this->useJSON ) {
            $top['entry'] = array();
        }
        if ( !I2CE::pearError( $results, "Failed to get cached data for mCSD Update Supplier." ) ) {

            while ( $row = $results->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
                //subquery to extract person ids
                $data = array();
                $count++;
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Practitioner/" . $row['uuid'];
                    $entry['resource'] = array( 'resourceType' => 'Practitioner' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Practitioner/" . $row['uuid'] );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $practitioner = $this->doc->createElement( "Practitioner" );
                }
                
                //subQuery to extract person languages
                $subQuery="SELECT person_language.id,language.name as language,person_language.reading,person_language.speaking,person_language.writing
                    from hippo_person_language as person_language 
                    LEFT JOIN hippo_language as language on language.id= person_language.language
                    where person_language.parent = '". $row['id'] ."'";

                $result_lang = $db->query( $subQuery );
                $counter=0;

                while ( $subRow = $result_lang->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
                    $data['communication'][$counter] = array();
                    $data['communication'][$counter]['language']= $subRow['language'];
                    if(isset($subRow['reading']))
                    {
                        $data['communication'][$counter]['reading']= $subRow['reading'];
                    }
                    if(isset($subRow['speaking']))
                    {
                        $data['communication'][$counter]['speaking']= $subRow['speaking'];
                    }
                    if(isset($subRow['writing']))
                    {
                        $data['communication'][$counter]['writing']= $subRow['writing'];
                    }
                    $counter++;
                }
                //subQuery to extract educations
                $subQuery="SELECT edu_type.name AS degree,education.institution AS institution,
                DATE_FORMAT(education.year,'%Y') AS end_date 
                FROM `hippo_education` AS education 
                LEFT JOIN hippo_edu_type AS edu_type ON edu_type.id = education.edu_type
                WHERE education.parent = '". $row['id'] ."'" ;

                $result_educ = $db->query( $subQuery );
		//$data = array();
                $counter=0;
                while ( $subRow = $result_educ->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
                    $data['education'][$counter] = array();

                    if($subRow['degree']){
                        $data['education'][$counter]['degree']= $subRow['degree'];
                    }
                    if($subRow['end_date'] && $subRow['end_date']!="0000-00-00"){
                        $formatedDate=null;
                        if(strlen($subRow['end_date'])<=4)
                        {
                            $formatedDate=new DateTime($subRow['end_date']."-01-01");
                        }
                        else{
                            $formatedDate=new DateTime($subRow['end_date']);
                        }
                        
                        $data['education'][$counter]['end_year']=$formatedDate->format('c');
                    }
                    if($subRow->institution){
                        /*$institutionRefId=strtolower(preg_replace('/\s+/', '', $subRow->institution));
                        $institutionRefId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $subRow->institution));
                        $data['qualification'][$counter]['issuer']="Organization/".$institutionRefId;
                    }
                    else{*/
                        $data['education'][$counter]['issuer']=null;
                    }
                    $counter++;
                }
                
                $data['uuid'] = $row['uuid'];
                $lastUpdated = new DateTime($row['lastupdated']);
                $data['lastUpdated'] = $lastUpdated->format('c');
           //     $data['id_system'] = $this->getSiteBase();

            //    $data['id_code'] = $row['id'];
                $data['active'] = 'true';
                $data['fullname'] = $row['family'];
                $data['family'] = $row['family'];
                $data['given'] = array( $row['given'] );
                if ( $row['given2'] ) {
                    $data['given'][] = $row['given2'];
                    $data['fullname'] = $row['given'] . " " . $row['given2'] . " " . $row['family'];
                } else { 
                    $data['fullname'] = $row['given'] . " " . $row['family']; 
                }
                if ( $row['gender'] == 'gender|M' ) {
                    $data['gender'] = 'male';
                } elseif ( $row['gender'] == 'gender|F' ) {
                    $data['gender'] = 'female';
                }
                
		if ( !$row['birthdate'] || $row['birthdate'] == '0000-00-00' ) {
                  //  $data['birthDate'] = null;
                }else {
                    $data['birthDate'] = $row['birthdate'];
                }

                $data['telecom'] = array( 
                        'home' => array( 'phone' => array()),
                        'mobile' => array( 'phone' => array())
                        );
                /*if ( $row['home_phone'] ) {
                    $data['telecom']['home']['phone'][] = $row['home_phone'];
                }
                if ( $row['home_mobile'] ) {
                    $data['telecom']['mobile']['phone'][] = $row['home_mobile'];
                }*/
		$pfx = "\+256";
$regexp = "/^[0]{1}[7]{1}[0,1,5,7-9]{1}[0-9]{7}$/";
if ( $row['home_mobile'] && $row['home_phone']) {
    if ( $row['home_phone'] ) {
        $val = $row['home_phone'];
        if(preg_match($regexp, $val )){                                                                                                                                                         
                $val = ltrim($val, '0');
                $data['telecom']['mobile']['phone'][] = "+256".$val;
        }else if(preg_match('/^'.$pfx.'/', $val)){
                $data['telecom']['mobile']['phone'][] = $val;
        }else {
                $data['telecom']['mobile']['phone'][] = 'null';
        }
    }
    if ( $row['home_mobile'] ) {
        $val = $row['home_mobile'];                                                                                                                                                      
        if(preg_match($regexp, $val )){
                $val = ltrim($val, '0');
                $data['telecom']['home']['phone'][] = "+256".$val;
        }else if(preg_match('/^'.$pfx.'/', $val)){
                $data['telecom']['home']['phone'][] = $val;
        } else {
            
        }

    }
} else if ( $row['home_phone'] && !$row['home_mobile']) {
    $val = $row['home_phone'];                                                                                                                                                      
    if(preg_match($regexp, $val )){
            $val = ltrim($val, '0');
            $data['telecom']['home']['phone'][] = "+256".$val;
    }else if(preg_match('/^'.$pfx.'/', $val)){
            $data['telecom']['home']['phone'][] = $val;
    } else {
        
    }
} else if (!$row['home_phone'] && $row['home_mobile']){
    $val = $row['home_mobile'];                                                                                                                                                      
    if(preg_match($regexp, $val )){
            $val = ltrim($val, '0');
            $data['telecom']['home']['phone'][] = "+256".$val;
    }else if(preg_match('/^'.$pfx.'/', $val)){
            $data['telecom']['home']['phone'][] = $val;
    } else {
        
    }
}
                
                $data['identification'] = array();
                $id_count = 0 ; 
                $data['identification'][$id_count] = array();
                $data['identification'][$id_count]['id_type'] = "NIN";
                $data['identification'][$id_count]['id_num'] = $row['national_id'];

                if ( $this->useJSON ) {
                    $this->create_Practitioner( $data, $entry['resource'] );
                    $top['entry'][] = $entry;
                } else {
                    $this->create_Practitioner( $data, $practitioner );

                    $resource->appendChild($practitioner);
                    $entry->appendChild( $resource );
                    $top->appendChild( $entry );
                }
            }
            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
            return true;
        } else {
            http_response_code(500);
            return false;
        }

    }

    /**
     * Create a practitioner FHIR object based on the given array
     * Valid format is:
     * $data = array( 
     *         'uuid' => VALUE,
     *         'lastUpdated' => 'YYYY-MM-DDTHH:MM:SS-HH:MM'
     *         'id_system' => 'identifier system',
     *         'id_code' => 'identifier code',
     *         'active' => 'true|false',
     *         'family' => 'surname',
     *         'given' => array( 'given name', 'other name', ... ),
     *         'gender' => 'male|female',
     *         'birthDate' => 'YYYY-MM-DD',
     *         'telecom' => array( 
     *                      'work' => array( 'phone' => array( '#', ... ), 'email' => array( '@', ... ) ),
     *                      'mobile' => array( 'phone' => array( '#', ... ) ),
     *                      'home' => array( 'phone' => array( '#', ... ), 'email' => array( '@', ... ) ),
     *                      ),
     *         'address' => array( 'work' => 'ADDRESS', 'home' => 'ADDRESS' ),
     *         );
     *
     * @param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_Practitioner( $data, &$top ) {

        $languageProficiencyDict=array('language_proficiency|elementary'=>'Elementary',
        'language_proficiency|limited'=>'Limited','language_proficiency|professional'=>'Professional');
        if ( $this-> useJSON ) {    
            $top['id'] = $data['uuid'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }
                $top['meta']['profile'] = array('http://ihris.org/fhir/StructureDefinition/iHRISPractitioner');
            $globalIdSystem=null;
            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $globalIdSystem= $data['id_system'];
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }
            //$element = array();
            if ( array_key_exists( 'identification', $data ) ) {
                //$top['identification'] = array();
		 $top['identifier'] = array();
                foreach( $data['identification'] as $ids => $id) {
                    if($id['id_type'] == 'NIN'){
                        $globalIdSystem = "https://www.nira.go.ug/"; 
                    }
                    $globalIdSystem=$globalIdSystem!=null?$globalIdSystem:"http://ihris.org/";
                    $identifierItem= array( 'system' =>$globalIdSystem."".$id['id_type'], 
                    'value' => $id['id_num'],'period'=>array('start'=>$id['issueDate'],'end'=>$id['expirationDate']),
                    'assigner' => $id['assigner']
                    );

                    if(isset($top['identifier']))
                    {
                        array_push($top['identifier'],$identifierItem);
                    }
                }
                
            }

            if ( array_key_exists( 'active', $data ) ) {
                $top['active'] = ( $data['active'] == 'true' );
            }

            if ( array_key_exists( 'family', $data ) || array_key_exists( 'given', $data ) ) {
                $top['name'] = array();
                $top['name'][] = array( 'resourceType' => 'HumanName' );
                $top['name'][0]['use'] = "official";
                if ( array_key_exists('fullname', $data) ) {
                    $top['name'][0]['text'] = $data['fullname'];
                }
                if ( array_key_exists( 'family', $data ) ) { 
                    $top['name'][0]['family'] = $data['family'];
                }
                if ( array_key_exists('given', $data) ) {
                    if ( !is_array( $data['given'] ) ) {
                        $data['given'] = array( $data['given'] );
                    }
                    $top['name'][0]['given'] = array();
                    foreach( $data['given'] as $given ) {
                        if ( !$given ) continue;
                        $top['name'][0]['given'][] = $given;
                    }
                }
                
            }


            if( array_key_exists( 'gender', $data ) ) {
                $top['gender'] = $data['gender'];
            }
            if ( array_key_exists( 'birthDate', $data ) ) {
                $top['birthDate'] = $data['birthDate'];
            }

            if ( array_key_exists( 'telecom', $data ) ) {
                $top['telecom'] = array();
                foreach ( $data['telecom'] as $use => $telecom ) {
                    
                    foreach ( $telecom as $system => $contacts ) {
                        if(is_array( $contacts)){
                            foreach ( $contacts as $key=>$contact ) {
                                
                                $top['telecom'][] = array( 'resourceType' => 'ContactPoint', 'system' => $system, 'use' => $use, 'value' => $contact );
                            
                            }
                        }
                    }
                }
            }

            if ( array_key_exists( 'communication', $data ) ) {
                $top['communication'] = array();
                foreach( $data['communication'] as $ids => $language) {
                    
                    $proficiencyCodeReading=null;
                    $proficiencyCodeWriting=null;
                    $proficiencycodeSpeaking=null;
                    foreach( $languageProficiencyDict as $iHRISKey => $code) {
                        if($iHRISKey==$language['reading']){
                            $proficiencyCodeReading=$code;
                        }
                        if($iHRISKey==$language['writing']){
                            $proficiencyCodeWriting=$code;
                        }
                        if($iHRISKey==$language['speaking']){
                            $proficiencycodeSpeaking=$code;
                        }
                    }
                    $communication = array('text'=>$language['language'],
                            'extension'=>array(array('url'=>'http://ihris.org/fhir/StructureDefinition/iHRISCommunicationDetails',
                            'extension'=>array(array('url'=>"reading",'valueCoding'=>array('code'=>$proficiencyCodeReading)),
                                    array('url'=>"writing",'valueCoding'=>array('code'=>$proficiencyCodeWriting)),
                                    array('url'=>"speaking",'valueCoding'=>array('code'=>$proficiencycodeSpeaking))
                            ))));
                    array_push($top['communication'],$communication);
                }
            }
            if ( array_key_exists( 'qualification', $data ) ) {
                $top['qualification'] = array();
                foreach( $data['qualification'] as $ids => $education) {
            
                    $qualification = array('identifier'=> null,
                    'code'=>array('text'=>$education['edu_type']),
                    'period'=>array('end'=>$education['end_date']),
                    'issuer'=>$education['issuer']
                    );
                    array_push($top['qualification'],$qualification);
                  

                }
            }

         } else {
            $id = $this->doc->createElement("id");
            $id->setAttribute( 'value', $data['uuid'] );
            $top->appendChild($id);

            if ( array_key_exists('lastUpdated', $data ) ) {
                $meta = $this->doc->createElement("meta");
                $lastUpdated = $this->doc->createElement("lastUpdated"); 
                $lastUpdated->setAttribute('value', $data['lastUpdated']);
                $meta->appendChild( $lastUpdated );
                $top->appendChild($meta);
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $ident = $this->doc->createElement("identifier");
                $sys = $this->doc->createElement("system");
                $sys->setAttribute('value', $data['id_system']);
                $ident->appendChild($sys);
                $code = $this->doc->createElement("code");
                $code->setAttribute('value', $data['id_code']);
                $ident->appendChild($code);
                $top->appendChild($ident);
            }

            if ( array_key_exists( 'active', $data ) ) {
                $active = $this->doc->createElement("active");
                $active->setAttribute("value", $data['active']);
                $top->appendChild($active);
            }

            if ( array_key_exists( 'family', $data ) || array_key_exists( 'given', $data ) ) {
                $name = $this->doc->createElement("name");
                if ( array_key_exists( 'family', $data ) ) {
                    $family = $this->doc->createElement("family");
                    $family->setAttribute('value', $data['family']);
                    $name->appendChild($family);
                }
                if ( array_key_exists('given', $data) ) {
                    if ( !is_array( $data['given'] ) ) {
                        $data['given'] = array( $data['given'] );
                    }
                    foreach( $data['given'] as $given ) {
                        if ( !$given ) continue;
                        $giv = $this->doc->createElement('given');
                        $giv->setAttribute('value', $given);
                        $name->appendChild($giv);
                    }
                }
                $top->appendChild($name);
            }


            if( array_key_exists( 'gender', $data ) ) {
                $gender = $this->doc->createElement('gender');
                $gender->setAttribute('value', $data['gender']);
                $top->appendChild($gender);
            }

            if ( array_key_exists( 'birthDate', $data ) ) {
                $birth = $this->doc->createElement('birthDate');
                $birth->setAttribute('value', $data['birthDate']);
                $top->appendChild($birth);
            }

            if ( array_key_exists( 'telecom', $data ) ) {
                foreach ( $data['telecom'] as $use => $telecom ) {
                    foreach ( $telecom as $system => $contacts ) {
                        foreach ( $contacts as $contact ) {
                            $tele = $this->doc->createElement("telecom");
                            $sys = $this->doc->createElement("system");
                            $sys->setAttribute('value', $system);
                            $tele->appendChild($sys);
                            $u = $this->doc->createElement("use");
                            $u->setAttribute('value', $use);
                            $tele->appendChild($u);
                            $value = $this->doc->createElement('value');
                            $value->setAttribute('value', $contact);
                            $tele->appendChild($value);
                            $top->appendChild($tele);
                        }
                    }
                }
            }

            if ( array_key_exists( 'address', $data ) ) {
                foreach( $data['address'] as $use => $address ) {
                    $addr = $this->doc->createElement('address');
                    $u = $this->doc->createElement("use");
                    $u->setAttribute('value', $use);
                    $addr->appendChild($u);
                    $text = $this->doc->createElement('text');
                    $text->setAttribute('value', $address);
                    $addr->appendChild($text);
                    $top->appendChild($addr);
                }
            }
        }

    }

    /**
     * Load and set the forms for Location
     * @param string $uuid
     * @param DOMNode $top The node to append data to.
     * @return boolean
     */
    protected function loadData_Location( $uuid, &$top ) {
        $fac_id = I2CE_FormStorage::search( 'facility', false, array(
                    'operator' => 'FIELD_LIMIT',
                    'style' => 'equals',
                    'field' => 'csd_uuid',
                    'data' => array( 'value' => $uuid )
                    ), array(), 1 );
        $factory = I2CE_FormFactory::instance();

        if ( !$fac_id ) {
            $geos = array( 'village' => 'parish', 'parish' => 'county','county' => 'district', 'district' => 'region', 'region' => 'country', 'country' => null);
            $geo_id = null;
            $geo_type = null;
            foreach ( $geos as $geo => $partOf ) {
                $geo_id = I2CE_FormStorage::search( $geo, false, array(
                            'operator' => 'FIELD_LIMIT',
                            'style' => 'equals',
                            'field' => 'csd_uuid',
                            'data' => array( 'value' => $uuid )
                            ), array(), 1 );
                if ( $geo_id ) {
                    $geo_type = $geo;
                    break;
                }
            }
            if ( !$geo_id ) {
                return false;
            }
            $geo = $factory->createContainer( "$geo_type|$geo_id" );
            $geo->populate();

            $data['uuid'] = $uuid;
            $data['lastUpdated'] = $geo->getField('last_modified')->getValue()->getDateTimeObj()->format('c');
            $data['status'] = 'active';
            $data['mode'] = 'instance';
            $data['name'] = $geo->name;
            $data['id_system'] = $this->getSiteBase();
            $data['id_code'] = $geo->getNameId();
            $data['physicalType_system'] = 'http://hl7.org/fhir/location-physical-type';
            $data['physicalType'] = 'jdn';
            $data['type_text'] = 'Geographic Jurisdiction';
            if ( $geos[$geo_type] ) {
                $above = $geo->getField($geos[$geo_type])->getDBValue();
                $aboveObj = $factory->createContainer( $above );
                $aboveObj->populate();
                //$data['partOf'] = $this->getSiteBase() . "FHIR/Location/" . $aboveObj->csd_uuid;
                $data['partOf'] = "Location/" . $aboveObj->csd_uuid;
            }

        } else {
            $facility = $factory->createContainer( "facility|$fac_id" );
            $facility->populate();
            
            $data = array();

            $data['uuid'] = $uuid;
            $data['lastUpdated'] = $facility->getField('last_modified')->getValue()->getDateTimeObj()->format('c');

            $data['status'] = 'active';
            $data['name'] = $facility->name;
            $dhis2_name = explode("(",$facility->getField('dhis_orgunit')->getDisplayValue());
            $data['alias'] = $dhis2_name[0];
            $data['mode'] = 'instance';
            $data['id_system'] = $this->getSiteBase();
            $data['id_code'] = $facility->getNameId();
            $data['type_system'] = $this->getSiteBase();
            $data['type'] = $facility->getField('facility_type')->getDBValue();
            $data['type_text'] = $facility->getField('facility_type')->getDisplayValue();
	    
	    $data['identification'] = array();
	    $data['identification'][0] = array();
            $data['identification'][0]['id_num'] = $facility->getField('dhis_orgunit')->getDBValue();
            $data['identification'][0]['system'] = 'https://hmis1.health.go.ug';

            $data['physicalType_system'] = 'http://hl7.org/fhir/location-physical-type';
            $data['physicalType'] = 'bu';

            $above = $facility->getField('location')->getDBValue();
            $aboveObj = $factory->createContainer( $above );
            $aboveObj->populate();
            //$data['partOf'] = $this->getSiteBase() . "FHIR/Location/" . $aboveObj->csd_uuid;
            $data['partOf'] = "Location/" . $aboveObj->csd_uuid;
 

        }
        $this->create_Location( $data, $top );
        return true;
    }

    /**
     * Create the bundle of locations updated since a given time.
     * @param DOMNode $top
     * @return boolean
     */
    protected function getUpdates_Location( &$top ) {
        if ( $this->useJSON ) {
            $top['type'] = 'history';
            $top['total'] = 0;
        } else {
            $type = $this->doc->createElement("type");
            $type->setAttribute("value", "history");
            $top->appendChild($type);
    
            $total = $this->doc->createElement("total");
            $top->appendChild($total);
        }


        $required_forms = array( "facility", "facility_type", "village", "parish", "county", "district", "region", "country" );
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }

        $queries = array( 
                'facility' => "SELECT facility.csd_uuid as uuid, DATE_FORMAT(facility.last_modified, '%Y-%m-%d %T') AS lastupdated, facility.id, facility.name, facility.facility_type, ft.name AS facility_type_name, dh.name AS dhis_name, dh.code AS dhis_code, IFNULL(county.csd_uuid, IFNULL(district.csd_uuid, IFNULL(region.csd_uuid, country.alpha_two))) AS partof FROM hippo_facility AS facility LEFT JOIN hippo_county AS county ON county.id = facility.location LEFT JOIN hippo_district AS district ON district.id = facility.location LEFT JOIN hippo_region AS region ON region.id = facility.location LEFT JOIN hippo_country AS country ON country.id = facility.location LEFT JOIN hippo_facility_type ft ON ft.id = facility.facility_type LEFT JOIN hippo_dhis_orgunit dh ON dh.id = facility.dhis_orgunit",
                'village' => "SELECT village.csd_uuid as uuid, DATE_FORMAT(village.last_modified, '%Y-%m-%d %T') AS lastupdated, village.id, village.name, 'na' AS facility_type, 'na' AS facility_type_name, 'na' AS dhis_name, 'na' AS dhis_code,  parish.csd_uuid AS partof FROM hippo_village AS village LEFT JOIN hippo_parish AS parish ON parish.id = village.parish",
                'parish' => "SELECT parish.csd_uuid as uuid, DATE_FORMAT(parish.last_modified, '%Y-%m-%d %T') AS lastupdated, parish.id, parish.name, 'na' AS facility_type, 'na' AS facility_type_name, 'na' AS dhis_name, 'na' AS dhis_code, county.csd_uuid AS partof FROM hippo_parish AS parish LEFT JOIN hippo_county AS county ON county.id = parish.county",
                'county' => "SELECT county.csd_uuid as uuid, DATE_FORMAT(county.last_modified, '%Y-%m-%d %T') AS lastupdated, county.id, county.name, 'na' AS facility_type, 'na' AS facility_type_name, 'na' AS dhis_name, 'na' AS dhis_code, district.csd_uuid AS partof FROM hippo_county AS county LEFT JOIN hippo_district AS district ON district.id = county.district",
                'district' => "SELECT district.csd_uuid as uuid, DATE_FORMAT(district.last_modified, '%Y-%m-%d %T') AS lastupdated, district.id, district.name, 'na' AS facility_type, 'na' AS facility_type_name, 'na' AS dhis_name, 'na' AS dhis_code, region.csd_uuid AS partof FROM hippo_district AS district LEFT JOIN hippo_region AS region ON region.id = district.region",
                'region' => "SELECT region.csd_uuid as uuid, DATE_FORMAT(region.last_modified, '%Y-%m-%d %T') AS lastupdated, region.id, region.name, 'na' AS facility_type, 'na' AS facility_type_name, 'na' AS dhis_name, 'na' AS dhis_code, country.alpha_two AS partof FROM hippo_region AS region LEFT JOIN hippo_country AS country ON country.id = region.country",
                'country' => "SELECT country.alpha_two as uuid, DATE_FORMAT(country.last_modified, '%Y-%m-%d %T') AS lastupdated, country.id, country.name, 'na' AS facility_type, 'na' AS facility_type_name, 'na' AS dhis_name, 'na' AS dhis_code, null AS partof FROM hippo_country AS country",
                );

        $qry = '';
        foreach ($queries as $main => $query) {
            $where = '';
            if ( $this->since ) {
                $where = "WHERE $main.last_modified >= " . $this->since;
            }
            $query .= " $where";
            $finals[] = $query;
        }
        $qry = implode( ' UNION ', $finals );
        $qry .= " ORDER BY lastupdated ASC";

        $count = 0;
        $db = MDB2::singleton();
        $results = $db->query($qry);
        if ( $this->useJSON ) {
            $top['entry'] = array();
        }
        if ( !I2CE::pearError( $results, "Failed to get cached data for mCSD Update Supplier." ) ) {

            while ( $row = $results->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
                
                $data = array();
                if(!$row['uuid'])
                {  
                    continue;
                }
                $count++;
                $entry = $this->doc->createElement("entry");
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Location/" .$row['uuid'];
                    $entry['resource'] = array( 'resourceType' => 'Location' );
                } else {
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Location/" . $row['uuid'] );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $location = $this->doc->createElement( "Location" );
                }
                $data['uuid'] = $row['uuid'];
                $lastUpdated = new DateTime($row['lastupdated']);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row['id'];
                $data['status'] = 'active';
                $data['mode'] = 'instance';
                $data['name'] = $row['name'];

                if ( $row['facility_type'] == 'na' ) {
                    $data['physicalType_system'] = 'http://hl7.org/fhir/location-physical-type';
                    $data['physicalType'] = 'jdn';
                    $data['type_text'] = 'Geographic Jurisdiction';
                } else {
                    $data['alias'] = $row['dhis_name'];
                    $data['type_system'] = $this->getSiteBase();
                    $data['type'] = $row['facility_type'];
                    $data['type_text'] = $row['facility_type_name'];
	            $data['identification'] = array();
		    $data['identification'][0] = array();
                    $data['identification'][0]['id_num'] = $row['dhis_code'];
                    $data['identification'][0]['system'] = 'https://hmis1.health.go.ug';
                    $data['physicalType_system'] = 'http://hl7.org/fhir/location-physical-type';
                    $data['physicalType'] = 'bu';
                }

                if ( $row['partof'] ) {
                    $data['partOf'] = "Location/" . $row['partof'];
                }

                if ( $this->useJSON ) {
                    $this->create_Location( $data, $entry['resource'] );
                    $top['entry'][] = $entry;
                } else {
                    $this->create_Location( $data, $location );

                    $resource->appendChild($location);
                    $entry->appendChild( $resource );
                    $top->appendChild( $entry );
                }
            }
            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
            return true;
        }else {
            http_response_code(500);
            return false; 
        }
    }


    /**
     * Create a locations FHIR object based on the given array
     * Valid format is:
     * $data = array( 
     *         'uuid' => VALUE,
     *         'lastUpdated' => 'YYYY-MM-DDTHH:MM:SS-HH:MM'
     *         'id_system' => 'identifier system',
     *         'id_code' => 'identifier code',
     *         'status' => 'active|suspended|inactive',
     *         'mode' => 'instance|kind',
     *         'name' => VALUE,
     *         'type_system' => 'http://hl7.org/fhir/ValueSet/v3-ServiceDeliveryLocationRoleType' || VALUE,
     *         'type' => VALUE,
     *         'type_text' => VALUE,
     *         'physicalType_system' => 'http://hl7.org/fhir/location-physical-type' || VALUE,
     *         'physicalType' => VALUE,
     *         'partOf' => 'URL Reference',
     *         );
     *
     * @param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_Location( $data, &$top ) {

        if ( $this->useJSON ) {
            $top['id'] = $data['uuid'];

            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }
            $top['identifier'] = array();
            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }
            if ( array_key_exists('identification', $data)) {
                $top['identifier'][] = array( 'system' => $data['identification'][0]['system'], 'value' => $data['identification'][0]['id_num'] );
            }

            if ( array_key_exists( 'status', $data ) ) {
                $top['status'] = $data['status'];
            }

            if ( array_key_exists( 'name', $data ) ) {
                $top['name'] = $data['name'];
            }

            if ( array_key_exists( 'alias', $data ) ) {
                $top['alias'] = $data['alias'];
            }

            if ( array_key_exists( 'mode', $data ) ) {
                $top['mode'] = $data['mode'];
            }


            if ( array_key_exists( 'type', $data ) || array_key_exists( 'type_system', $data ) || array_key_exists( 'type_text', $data ) ) {
                $top['type'] = array();
                if ( array_key_exists( 'type', $data ) || array_key_exists( 'type_system', $data ) ) {
                    $coding = array();
                    if ( array_key_exists( 'type_system', $data ) ) {
                        $coding['system'] = $data['type_system'];
                    }
                    if ( array_key_exists( 'type', $data ) ) {
                        $coding['code'] = $data['type'];
                    }
                    $top['type']['coding'] = array( $coding );
                }
                if ( array_key_exists( 'type_text', $data ) ) {
                    $top['type']['text'] = $data['type_text'];
                }
            }

            if ( array_key_exists( 'physicalType', $data ) || array_key_exists( 'physicalType_system', $data ) ) {
                $top['physicalType'] = array();
                $coding = array();
                if ( array_key_exists( 'physicalType_system', $data ) ) {
                    $coding['system'] = $data['physicalType_system'];
                }
                if ( array_key_exists( 'physicalType', $data ) ) {
                    $coding['code'] = $data['physicalType'];
                }
                $top['physicalType']['coding'] = array( $coding );
                if( $data['physicalType'] == 'bu') {
                    $top['physicalType']['text'] = 'Building';
                    $top['meta']['profile'] = array('http://ihe.net/fhir/StructureDefinition/IHE_mCSD_Location','http://ihe.net/fhir/StructureDefinition/IHE_mCSD_FacilityLocation');
                } else {
                    $top['physicalType']['text'] = 'Jurisdiction';
                    $top['meta']['profile'] = array('http://ihe.net/fhir/StructureDefinition/IHE_mCSD_Location');
                }
                
            }

            if ( array_key_exists( 'partOf', $data ) ) {
                $top['partOf'] = array( 'reference' => $data['partOf'] );
            }
         } else {
            $id = $this->doc->createElement("id");
            $id->setAttribute( 'value', $data['uuid'] );
            $top->appendChild($id);

            if ( array_key_exists('lastUpdated', $data ) ) {
                $meta = $this->doc->createElement("meta");
                $lastUpdated = $this->doc->createElement("lastUpdated"); 
                $lastUpdated->setAttribute('value', $data['lastUpdated']);
                $meta->appendChild( $lastUpdated );
                $top->appendChild($meta);
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $ident = $this->doc->createElement("identifier");
                $sys = $this->doc->createElement("system");
                $sys->setAttribute('value', $data['id_system']);
                $ident->appendChild($sys);
                $code = $this->doc->createElement("code");
                $code->setAttribute('value', $data['id_code']);
                $ident->appendChild($code);
                $top->appendChild($ident);
            }

            if ( array_key_exists('identification', $data) ) {
                $ident = $this->doc->createElement("identifier");
                $sys = $this->doc->createElement("system");
                $sys->setAttribute('value', $data['identification']['system']);
                $ident->appendChild($sys);
                $code = $this->doc->createElement("code");
                $code->setAttribute('value', $data['identification']['id_num']);
                $ident->appendChild($code);
                $top->appendChild($ident);
            }

            if ( array_key_exists( 'status', $data ) ) {
                $status = $this->doc->createElement("status");
                $status->setAttribute("value", $data['status']);
                $top->appendChild($status);
            }

            if ( array_key_exists( 'name', $data ) ) {
                $name = $this->doc->createElement("name");
                $name->setAttribute("value", $data['name']);
                $top->appendChild($name);
            }

            if ( array_key_exists( 'alias', $data ) ) {
                $alias = $this->doc->createElement("alias");
                $alias->setAttribute("value", $data['alias']);
                $top->appendChild($alias);
            }

            if ( array_key_exists( 'mode', $data ) ) {
                $mode = $this->doc->createElement("mode");
                $mode->setAttribute("value", $data['mode']);
                $top->appendChild($mode);
            }


            if ( array_key_exists( 'type', $data ) || array_key_exists( 'type_system', $data ) || array_key_exists( 'type_text', $data ) ) {
                $type = $this->doc->createElement("type");
                if ( array_key_exists( 'type', $data ) || array_key_exists( 'type_system', $data ) ) {
                    $coding = $this->doc->createElement("coding");
                    if ( array_key_exists( 'type_system', $data ) ) {
                        $sys = $this->doc->createElement("system");
                        $sys->setAttribute('value', $data['type_system']);
                        $coding->appendChild($sys);
                    }
                    if ( array_key_exists( 'type', $data ) ) {
                        $code = $this->doc->createElement("code");
                        $code->setAttribute('value', $data['type']);
                        $coding->appendChild($code);
                    }
                    $type->appendChild($coding);
                }
                if ( array_key_exists( 'type_text', $data ) ) {
                    $text = $this->doc->createElement("text");
                    $text->setAttribute('value', $data['type_text']);
                    $type->appendChild($text);
                }
                $top->appendChild($type);
            }

            if ( array_key_exists( 'physicalType', $data ) || array_key_exists( 'physicalType_system', $data ) ) {
                $type = $this->doc->createElement("physicalType");
                $coding = $this->doc->createElement("coding");
                if ( array_key_exists( 'physicalType_system', $data ) ) {
                    $sys = $this->doc->createElement("system");
                    $sys->setAttribute('value', $data['physicalType_system']);
                    $coding->appendChild($sys);
                }
                if ( array_key_exists( 'physicalType', $data ) ) {
                    $code = $this->doc->createElement("code");
                    $code->setAttribute('value', $data['physicalType']);
                    $coding->appendChild($code);
                }
                $type->appendChild($coding);
                $top->appendChild($type);
            }

            if ( array_key_exists( 'partOf', $data ) ) {
                $partOf = $this->doc->createElement("partOf");
                $ref = $this->doc->createElement("reference");
                $ref->setAttribute( 'value', $data['partOf'] );
                $partOf->appendChild($ref);
                $top->appendChild($partOf);
            }
        }

    }


    /**
     * Load and set the forms for PractitionerRoles
     * @param string $uuid
     * @param DOMNode $top The node to append data to.
     * @return boolean
     */
    protected function loadData_PractitionerRole( $uuid, &$top) {
        $pers_pos_id = I2CE_FormStorage::search( 'person_position', false, array(
                    'operator' => 'FIELD_LIMIT',
                    'style' => 'equals',
                    'field' => 'csd_uuid',
                    'data' => array( 'value' => $uuid )
                    ), array(), 1 );
        if ( !$pers_pos_id ) {
            return false;
        } else {
            $factory = I2CE_FormFactory::instance();

            $pers_pos = $factory->createContainer( "person_position|$pers_pos_id" );
            $pers_pos->populate();
            
            $data = array();

            $data['uuid'] = $pers_pos->csd_uuid;
            $data['lastUpdated'] = $pers_pos->getField('last_modified')->getValue()->getDateTimeObj()->format('c');
            $data['id_system'] = $this->getSiteBase();
            $data['id_code'] = $pers_pos->getNameId();
            $data['start_date'] = substr( $pers_pos->getField('start_date')->getDBValue(), 0, 10 );
            if ( $pers_pos->end_date->isBlank() ) {
                $data['active'] = 'true';
            } else {
                $data['end_date'] = substr( $pers_pos->getField('end_date')->getDBValue(), 0, 10 );
                $data['active'] = 'false';
            }

            $position = $factory->createContainer( $pers_pos->getField('position')->getDBValue() );
            $position->populate();
            $data['position'] = $position->getId();
            $job = $factory->createContainer( $position->getField('job')->getDBValue() );
            $job->populate();

            $data['code_system'] = $this->getSiteBase();
            $data['code'] = $job->getNameId();
            $data['code_text'] = $job->title;

            $facility = $factory->createContainer( $position->getField('facility')->getDBValue() );
            $facility->populate();

            //$data['location'] = $this->getSiteBase() . "FHIR/Location/" . $facility->csd_uuid;
            $data['location'] = "Location/" . $facility->csd_uuid;
	        $data['location_name'] = $facility->name;

            $person = $factory->createContainer( $pers_pos->getParent() );
            $person->populate();

            //$data['practitioner'] = $this->getSiteBase() . "FHIR/Practitioner/" . $person->csd_uuid;
            $data['practitioner'] = "Practitioner/" . $person->csd_uuid;
            $data['practitioner_name'] = "" . $person->surname . " " . $person->firstname . " " . $person->othername;
            //extract

            $this->create_PractitionerRole( $data, $top );

        }
        return true;
    }

    /**
     * Create the bundle of PractitionerRoles updated since a given time.
     * @param DOMNode $top
     * @return boolean
     */
    protected function getUpdates_PractitionerRole( &$top ) {
    
        if ( $this->useJSON ) {
            $top['type'] = 'history';
            $top['total'] = 0;
        } else {
            $type = $this->doc->createElement("type");
            $type->setAttribute("value", "history");
            $top->appendChild($type);

            $total = $this->doc->createElement("total");
            $top->appendChild($total);
        }


        $required_forms = array( "person_position", "person", "position", "job", "facility","village","parish","county",
            "district" );
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }

        $where = '';
        if ( $this->since ) {
            $where = "WHERE pp.last_modified >= " . $this->since;
        }

        $qry = "SELECT pp.csd_uuid AS uuid, DATE_FORMAT(pp.last_modified, '%Y-%m-%d %T') AS lastupdated, pp.id, pp.start_date, pp.end_date, position.id AS position_code,
        job.id AS code, job.title AS code_text, person.csd_uuid AS practitioner, CONCAT(person.surname,' ',person.firstname,' ',person.othername) AS practitioner_name,
	facility.csd_uuid AS location, facility.name AS location_name
            FROM hippo_person_position AS pp 
            LEFT JOIN hippo_position position ON position.id = pp.position
            LEFT JOIN hippo_job AS job ON job.id = position.job 
            LEFT JOIN hippo_facility AS facility ON facility.id = position.facility 
            LEFT JOIN hippo_person AS person ON person.id = pp.parent $where ORDER BY lastupdated ASC";


        $db = MDB2::singleton();
        $results = $db->query($qry);
        $count = 0;
        if ( $this->useJSON ) {
            $top['entry'] = array();
        }
        if ( !I2CE::pearError( $results, "Failed to get cached data for mCSD Update Supplier." ) ) {
            while ( $row = $results->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
                $data = array();
                $count++;
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/PractitionerRole/" . $row['uuid'];
                    $entry['resource'] = array( 'resourceType' => 'PractitionerRole' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/PractitionerRole/" . $row['uuid'] );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $role = $this->doc->createElement( "PractitionerRole" );
                }


                $data['uuid'] = $row['uuid'];
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row['id'];
		$pCode = explode("|",$row['position_code']);
	        $row['position_code'] =  $pCode[1];
                $data['position'] = $row['position_code'];

                $data['start_date'] = substr($row['start_date'], 0, 10);
                if ( !$row['end_date'] || $row['end_date'] == '0000-00-00 00:00:00' ) {
                    $data['active'] = 'true';
                } else { 
                    $data['end_date'] = substr($row['end_date'], 0, 10);
                    $data['active'] = 'false';
                }
                $data['code_system'] = $this->getSiteBase();
                $data['code'] = $row['code'];
                $data['code_text'] = $row['code_text'];
                    
                //$data['location'] = $this->getSiteBase() . "FHIR/Location/" . $row->location;
                $data['location'] = "Location/" . $row['location'];
	 	        $data['location_name'] = $row['location_name'];
                //$data['practitioner'] = $this->getSiteBase() . "FHIR/Location/" . $row->practitioner;
                $data['practitioner'] = "Practitioner/" . $row['practitioner'];
		        $data['practitioner_name'] = $row['practitioner_name'];

                if ( $this->useJSON ) {
                    $this->create_PractitionerRole( $data, $entry['resource'] );
                    $top['entry'][] = $entry;
                } else {
                    $this->create_PractitionerRole( $data, $role );

                    $resource->appendChild($role);
                    $entry->appendChild( $resource );
                    $top->appendChild( $entry );
                }
            }
            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
            
            return true;
        } else {
            http_response_code(500);
            return false;
        }
    }

    /**
     * Create a PractitionerRole FHIR object based on the given array
     * Valid format is:
     * $data = array( 
     *         'uuid' => VALUE,
     *         'lastUpdated' => 'YYYY-MM-DDTHH:MM:SS-HH:MM'
     *         'id_system' => 'identifier system',
     *         'id_code' => 'identifier code',
     *         'active' => 'true|false',
     *         'code_system' => 'http://hl7.org/fhir/ValueSet/v3-ServiceDeliveryLocationRoleType' || VALUE,
     *         'code' => VALUE,
     *         'code_text' => VALUE,
     *         'location' => 'URL Reference',
     *         'practitioner' => 'URL Reference',
      *         );
     *
     * @param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_PractitionerRole( $data, &$top ) {
        if ( $this->useJSON ) {
            $top['id'] = $data['uuid'];

            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }
            $top['meta']['profile'] = array('http://ihris.org/fhir/StructureDefinition/iHRISPractitionerRole');
           /* if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }
	   */
            $top['extension'] = array( 
                array(
                    'url' => 'http://ihris.org/fhir/StructureDefinition/iHRISPractitionerRoleDetails',
                    'extension' => array( array('url' => 'position', 'valueReference' => array( 'reference' => "Basic/P" . $data['position'])))
                )
            );
           
            $top['period'] = array( 'start' => $data['start_date'] );
            if ( array_key_exists( 'end_date', $data ) ) {
                $top['period']['end'] = $data['end_date'];
            }
            if ( array_key_exists( 'active', $data ) ) {
                $top['active'] = ( $data['active'] == 'true' );
            }
            
            if ( array_key_exists( 'code', $data ) || array_key_exists( 'code_system', $data ) || array_key_exists( 'code_text', $data ) ) {
                $code = array();
                if ( array_key_exists( 'code', $data ) || array_key_exists( 'code_system', $data ) ) {
                    $coding = array();
                    if ( array_key_exists( 'code_system', $data ) ) {
                        $coding['system'] = $data['code_system'];
                    }
                    if ( array_key_exists( 'code', $data ) ) {
                        $coding['code'] = $data['code'];
                    }
                    $code['coding'] = array( $coding );
                }
                if ( array_key_exists( 'code_text', $data ) ) {
                    $code['text'] = $data['code_text'];
                }
                $top['code'] = array( $code );
            }
            
            if ( array_key_exists( 'location', $data ) ) {
                $top['location'] = array( array( 'reference' => $data['location'],'display' => $data['location_name'] ) );
            }

            if ( array_key_exists( 'practitioner', $data ) ) {
                $top['practitioner'] = array( 'reference' => $data['practitioner'], 'display' => $data['practitioner_name'] );
            }
         } else {
            $id = $this->doc->createElement("id");
            $id->setAttribute( 'value', $data['uuid'] );
            $top->appendChild($id);

            if ( array_key_exists('lastUpdated', $data ) ) {
                $meta = $this->doc->createElement("meta");
                $lastUpdated = $this->doc->createElement("lastUpdated"); 
                $lastUpdated->setAttribute('value', $data['lastUpdated']);
                $meta->appendChild( $lastUpdated );
                $top->appendChild($meta);
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $ident = $this->doc->createElement("identifier");
                $sys = $this->doc->createElement("system");
                $sys->setAttribute('value', $data['id_system']);
                $ident->appendChild($sys);
                $code = $this->doc->createElement("code");
                $code->setAttribute('value', $data['id_code']);
                $ident->appendChild($code);
                $top->appendChild($ident);
            }

            $period = $this->doc->createElement('period');
            $start_date = $this->doc->createElement('start');
            $start_date->setAttribute("value", $data['start_date']);
            $period->appendChild($start_date);
            if ( array_key_exists('end-date', $data) ) {
                $end_date = $this->doc->createElement('end');
                $end_date->setAttribute("value", $data['end_date']);
                $period->appendChild($end_date);
            }
            $top->appendChild($period);

            if ( array_key_exists( 'active', $data ) ) {
                $active = $this->doc->createElement("active");
                $active->setAttribute("value", $data['active']);
                $top->appendChild($active);
            }

            if ( array_key_exists( 'code', $data ) || array_key_exists( 'code_system', $data ) || array_key_exists( 'code_text', $data ) ) {
                $code = $this->doc->createElement("code");
                if ( array_key_exists( 'code', $data ) || array_key_exists( 'code_system', $data ) ) {
                    $coding = $this->doc->createElement("coding");
                    if ( array_key_exists( 'code_system', $data ) ) {
                        $sys = $this->doc->createElement("system");
                        $sys->setAttribute('value', $data['code_system']);
                        $coding->appendChild($sys);
                    }
                    if ( array_key_exists( 'code', $data ) ) {
                        $cod = $this->doc->createElement("code");
                        $cod->setAttribute('value', $data['code']);
                        $coding->appendChild($cod);
                    }
                    $code->appendChild($coding);
                }
                if ( array_key_exists( 'code_text', $data ) ) {
                    $text = $this->doc->createElement("text");
                    $text->setAttribute('value', $data['code_text']);
                    $code->appendChild($text);
                }
                $top->appendChild($code);
            }

            if ( array_key_exists( 'location', $data ) ) {
                $location = $this->doc->createElement("location");
                $ref = $this->doc->createElement("reference");
                $ref->setAttribute( 'value', $data['location'] );
                $location->appendChild($ref);
                $top->appendChild($location);
            }

            if ( array_key_exists( 'practitioner', $data ) ) {
                $practitioner = $this->doc->createElement("practitioner");
                $ref = $this->doc->createElement("reference");
                $ref->setAttribute( 'value', $data['practitioner'] );
                $practitioner->appendChild($ref);
                $top->appendChild($practitioner);
            }
        }

    }

    /**
     * Load and set the forms for Basic/Positions
     * @param string $position_id
     * @param DOMNode $top The node to append data to.
     * @return boolean
     */
    protected function loadData_Basic( $id, &$top) {
        $pos_id = I2CE_FormStorage::search( 'position', false, array(
                    'operator' => 'FIELD_LIMIT',
                    'style' => 'equals',
                    'field' => 'id',
                    'data' => array( 'value' => $id )
                    ), array(), 1 );
        if ( !$pos_id ) {
            return false;
        } else {
            $factory = I2CE_FormFactory::instance();
           
            $position = $factory->createContainer( "position|$pos_id" );
            $position->populate();
            $data = array();

            $data['id'] = "P" . $id;
            $data['lastUpdated'] = $position->getField('last_modified')->getValue()->getDateTimeObj()->format('c');
            $data['id_system'] = $this->getSiteBase();
            $data['id_code'] = $position->getId();
            $data['title'] = $position->title;
            $data['department'] = $position->getField('department')->getDisplayValue();
            $data['department_code'] = $position->getField('department')->getDBValue();
            $data['job_code'] = $position->getField('job')->getDBValue();
            $data['job'] = $position->getField('job')->getDisplayValue();
            if($position->getField('status')->getDBValue() == "position_status|closed"){
                $data['active'] = "true"; 
            } else {
                $data['active'] = "false";
            }
            $facility = $factory->createContainer( $position->getField('facility')->getDBValue() );
            $facility->populate();

            //$data['location'] = $this->getSiteBase() . "FHIR/Location/" . $facility->csd_uuid;
            $data['location'] = "Location/" . $facility->csd_uuid;
	        $data['location_name'] = $facility->name;

            $this->create_Basic( $data, $top );

        }
        return true;
    }

    /**
     * Create the bundle of Basic/Positions updated since a given time.
     * @param DOMNode $top
     * @return boolean
     */
    protected function getUpdates_Basic( &$top ) {
    
        if ( $this->useJSON ) {
            $top['type'] = 'history';
            $top['total'] = 0;
        } else {
            $type = $this->doc->createElement("type");
            $type->setAttribute("value", "history");
            $top->appendChild($type);

            $total = $this->doc->createElement("total");
            $top->appendChild($total);
        }


        $required_forms = array( "position", "department", "job", "facility");
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }

        $where = '';
        if ( $this->since ) {
            $where = "WHERE position.last_modified >= " . $this->since;
        }

        $qry = "SELECT position.id AS id, DATE_FORMAT(position.last_modified, '%Y-%m-%d %T') AS lastupdated, position.title AS title , position.status AS status , position.department AS department_code, department.name AS department,
        job.id AS code, job.title AS code_text, facility.csd_uuid AS location, facility.name AS location_name
            FROM hippo_position AS position 
            LEFT JOIN hippo_job AS job ON job.id = position.job
            LEFT JOIN hippo_department AS department ON department.id = position.department  
            LEFT JOIN hippo_facility AS facility ON facility.id = position.facility $where ORDER BY lastupdated ASC";


        $db = I2CE::PDO();
        //$db = MDB2::singleton();
        $results = $db->query($qry)->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;
        if ( $this->useJSON ) {
            $top['entry'] = array();
        }
        if(count($results>0)){
            foreach ($results as $row) {
                $data = array();
                $count++;
                $newid = explode("|",$row['id']);
		$row['id'] = $newid[1];
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Basic/" . $row['id'];
                    $entry['resource'] = array( 'resourceType' => 'Basic' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Basic/" . $row['id'] );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $role = $this->doc->createElement( "Basic" );
                }


                $data['id'] = "P" . $row['id'];
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row['id'];
                $data['title'] = $row['title'];
                $data['department'] = $row['department'];
                $data['department_code'] = $row['department_code'];
                $data['job_code'] = $row['code'];
                $data['job'] = $row['code_text'];
                if($row['status'] == "position_status|closed"){
                    $data['active'] = 'true'; 
                } else {
                    $data['active'] = 'false'; 
                }
                  
                //$data['location'] = $this->getSiteBase() . "FHIR/Location/" . $row->location;
                $data['location'] = "Location/" . $row['location'];
	 	        $data['location_name'] = $row['location_name'];
                

                if ( $this->useJSON ) {
                    $this->create_Basic( $data, $entry['resource'] );
                    $top['entry'][] = $entry;
                } else {
                    $this->create_Basic( $data, $role );

                    $resource->appendChild($role);
                    $entry->appendChild( $resource );
                    $top->appendChild( $entry );
                }
            }
            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
            
            return true;
        } else {
            http_response_code(500);
            return false;
        }
    }

    /**
     * Create a PractitionerRole FHIR object based on the given array
     * Valid format is:
     * $data = array( 
     *         'uuid' => VALUE,
     *         'lastUpdated' => 'YYYY-MM-DDTHH:MM:SS-HH:MM'
     *         'id_system' => 'identifier system',
     *         'id_code' => 'identifier code',
     *         'active' => 'true|false',
     *         'code_system' => 'http://hl7.org/fhir/ValueSet/v3-ServiceDeliveryLocationRoleType' || VALUE,
     *         'code' => VALUE,
     *         'code_text' => VALUE,
     *         'location' => 'URL Reference',
     *         'practitioner' => 'URL Reference',
      *         );
     *
     * @param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_Basic( $data, &$top ) {
        if ( $this->useJSON ) {
            $top['id'] = $data['id'];

            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }
            $top['meta']['profile'] = array('http://ihris.org/fhir/StructureDefinition/iHRISPosition');
            /*if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }*/
            $top['extension'] = array();
            if(array_key_exists('title', $data)){
                $position = array();
                if(array_key_exists('department', $data) || array_key_exists('department_code', $data) ){
                    $department_code = array('system' => "http://ihris.org/fhir/CodeSystem/ihris-department", 'code' => $data['department']);
                    $department = array('url' => 'department', 'valueCoding' => $department_code );
                }
                if(array_key_exists('job', $data) || array_key_exists('job_code', $data)){
                    $job_code = array('system' => "http://ihris.org/fhir/CodeSystem/ihris-job", 'code' => $data['job']);
                    $job = array('url' => 'job', 'valueCoding' => $job_code );
                }
                $title = array('url' => 'title', 'valueString' => $data['title']);
                $active = array('url' => 'active', 'valueBoolean' => $data['active']);

                $location = array('url' => 'location', 'valueReference' => array('reference' => $data['location']));
                
            }
            $position = array($department,$job, $title,$active,$location);

            $top['extension'] = array( 
                array(
                    'url' => 'http://ihris.org/fhir/StructureDefinition/iHIRSPositionDetails',
                    'extension' => $position
                )
            );
            $top['code'] = array('coding' => array( array('system' => 'http://ihris.org/fhir/CodeSystem/ihris-resource',
                    'code' => 'iHRISPosition')), 'text' => 'iHRISPosition');

    
         } else {
            $id = $this->doc->createElement("id");
            $id->setAttribute( 'value', $data['uuid'] );
            $top->appendChild($id);

            if ( array_key_exists('lastUpdated', $data ) ) {
                $meta = $this->doc->createElement("meta");
                $lastUpdated = $this->doc->createElement("lastUpdated"); 
                $lastUpdated->setAttribute('value', $data['lastUpdated']);
                $meta->appendChild( $lastUpdated );
                $top->appendChild($meta);
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $ident = $this->doc->createElement("identifier");
                $sys = $this->doc->createElement("system");
                $sys->setAttribute('value', $data['id_system']);
                $ident->appendChild($sys);
                $code = $this->doc->createElement("code");
                $code->setAttribute('value', $data['id_code']);
                $ident->appendChild($code);
                $top->appendChild($ident);
            }

           
            if ( array_key_exists( 'active', $data ) ) {
                $active = $this->doc->createElement("active");
                $active->setAttribute("value", $data['active']);
                $top->appendChild($active);
            }

            

            if ( array_key_exists( 'location', $data ) ) {
                $location = $this->doc->createElement("location");
                $ref = $this->doc->createElement("reference");
                $ref->setAttribute( 'value', $data['location'] );
                $location->appendChild($ref);
                $top->appendChild($location);
            }

        }

    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
