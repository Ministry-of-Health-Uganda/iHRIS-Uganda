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


class iHRIS_Page_API_Resource extends I2CE_Page{

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

        if ( $this->request('since') ) {
            $this->since = date('Y-m-d H:i:s', strtotime( $this->request('since') ) );
        } else {
            $this->since = null;
        }
        if ( $this->request('name') ) {
            $this->name = $this->request('name');
        } else {
            $this->name = null;
        }
       if ( $this->request('phone') ) {
            $this->phone = $this->request('phone');
        } else {
            $this->phone = null;
        }
        if ( $this->request('orgunit') ) {
            $this->orgunit = $this->request('orgunit');
        } else {
            $this->orgunit = null;
        }

        if ( $this->request('facility') ) {
            $this->facility = $this->request('facility');
        } else {
            $this->facility = null;
        }

        if ( $this->request('district') ) {
            $this->district = $this->request('district');
        } else {
            $this->district = null;
        }

        if ( $this->request('parish') ) {
            $this->parish = $this->request('parish');
        } else {
            $this->parish = null;
        }

        if ( $this->request('village') ) {
            $this->village = $this->request('village');
        } else {
            $this->village = null;
        }

        if ( $this->request('fields') ) {
            $this->fields = $this->request('fields');
        } else {
            $this->fields = null;
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
     * Create the bundle of Persons updated since a given time.
     * @param DOMNode $top
     * @return boolean
     */
    protected function getUpdates_Person( &$top ) {
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

        //Put together  Fields Array
        if ( $this->fields || !empty($this->fields) ) {
            $fields_array = explode(',',($this->fields));
        }

/*
        $required_forms = array( "person","person_position","position","facility","person_village" );
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }
*/
        $where = array();
        if ( $this->since ) {
            $where[] = " GREATEST(last_modified, '0000-00-00 00:00:00') >= '". $this->since ."'";
        } 
        if ($this->district){
            $where[] = " LOWER(`district+name`) LIKE LOWER('%". $this->district ."%')";
        }
        if($this->orgunit){
            $where[] = " `dhis_orgunit+dhis_uid` = '". $this->orgunit . "'";
        }
        if($this->phone){
            $where[] = " `person+mobile_phone` LIKE ('%". $this->phone ."%') OR `person+alt_telephone` LIKE ('%". $this->phone ."%')" ;
        }
        if($this->facility){
            $where[] = " LOWER(`facility+name`) LIKE LOWER('%". $this->facility ."%')";
        }
        if($this->village){
            $where[] = " LOWER(`village+name`) LIKE LOWER('%". $this->village ."%')";
        }
        if($this->parish){
            $where[] = " LOWER(`parish+name`) LIKE LOWER('%". $this->parish ."%')";
        }
        if($this->name){
            $where[] = " LOWER(CONCAT(`person+firstname`,' ',`person+surname`)) LIKE LOWER('%". $this->name ."%')";
        }
        if(empty($where)){
            $where_str = "";
        } else {
            $where_str = " WHERE ".implode(" AND ",$where);
        }
        //$where_ = !(empty($where) ? " WHERE ".implode(" AND ",$where) : "";

        $qry = "SELECT * FROM zebra_staff_list $where_str ORDER BY last_modified ASC";

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
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "view?id=" . $row['person+id'];
                    $entry['resource'] = array( 'resourceType' => 'Person' );
                } 
                  
                $tempid = explode("|",$row['id']);
                $row['id'] = $tempid[1]; 
                $data['id'] = $row['id'];
                $lastUpdated = new DateTime($row['last_modified']);
                $data['lastUpdated'] = $lastUpdated->format('c');
                if(empty($this->fields) || (!empty($this->fields) && (in_array('name.firstname',$fields_array)))){
                    $data['firstname'] = $row['person+firstname'];
                }
                if(empty($this->fields) || (!empty($this->fields) && (in_array('name.surname',$fields_array)))){
                    $data['surname'] = $row['person+surname'];
                }
                if(empty($this->fields) || (!empty($this->fields) && (in_array('name.othername',$fields_array)))){
                    $data['othername'] =  $row['person+othername']; 
                }
                if(empty($this->fields) || (!empty($this->fields) && (in_array('name.text',$fields_array)))){
                    if ( $row['person+othername']) {
                        $data['fullname'] = $row['person+firstname'] . " " . $row['person+othername'] . " " . $row['person+surname'];
                    } else { 
                        $data['fullname'] = $row['person+firstname'] . " " . $row['person+surname']; 
                    }
                }
                if(empty($this->fields) || (!empty($this->fields) && (in_array('gender',$fields_array)))){
                    if ( $row['person+gender'] == 'gender|M' ) {
                        $data['gender'] = 'male';
                    } elseif ( $row['person+'] == 'gender|F' ) {
                        $data['gender'] = 'female';
                    }
                }
                if(empty($this->fields) || (!empty($this->fields) && (in_array('birthdate',$fields_array)))){
                    if ( !$row['person+birth_date'] || $row['person+birth_date'] == '0000-00-00' ) {
                    //  $data['birthDate'] = null;
                    }else {
                        $data['birthDate'] = $row['person+birth_date'];
                    }
                }

                if(empty($this->fields) || (!empty($this->fields) && (in_array('telecom.value',$fields_array)))){
                $data['telecom'] = array( 
                        'work' => array( 'phone' => array()),
                        'mobile' => array( 'phone' => array())
                        );
		    $pfx = "\+256";
       		$regexp = "/^[0]{1}[7]{1}[0,1,5,7-9]{1}[0-9]{7}$/";
                if ( $row['person+alt_telephone'] ) {
		    $val = $row['person+alt_telephone'];
		    if(preg_match($regexp, $val )){
           		 $val = ltrim($val, '0');
           		 $data['telecom']['alt']['phone'][] = "+256".$val;
       		    }else if(preg_match('/^'.$pfx.'/', $val)){
            		 $data['telecom']['alt']['phone'][] = $val;
       		    }else {
            		 $data['telecom']['alt']['phone'][] = 'null';
        	    }
                }
                if ( $row['person+mobile_phone'] ) {
		        $val = $row['person+mobile_phone'];
                    if(preg_match($regexp, $val )){
                         $val = ltrim($val, '0');
                         $data['telecom']['mobile']['phone'][] = "+256".$val;
                    }else if(preg_match('/^'.$pfx.'/', $val)){
                         $data['telecom']['mobile']['phone'][] = $val;
                    }else {
                         $data['telecom']['mobile']['phone'][] = 'null';
                    }

                }
            }
            if(empty($this->fields) || (!empty($this->fields) && (in_array('identifier.value',$fields_array)))){   
                $data['identification'] = array();
                $id_count = 0 ; 
                $data['identification'][$id_count] = array();
                //$data['identification'][$id_count]['id_type'] = "National ID";
                $data['identification'][$id_count]['id_num'] = $row['person+national_id'];
            }

		        if ($row['job+title'] || $row['position+title']) {
                    $data['job'] = $row['job+title'];
                    $data['position'] = $row['position+title'];
                }

                if ( $row['facility+name'] || $row['dhis_orgunit+dhis_uid'] || $row['dhis_orgunit+name'] ) {
                    if(empty($this->fields) || (!empty($this->fields) && (in_array('facility.name',$fields_array)))){
                        $data['facilityName'] = $row['facility+name'];
                    }
                    if(empty($this->fields) || (!empty($this->fields) && (in_array('facility.dhis_name',$fields_array)))){
                        $data['dhis_name'] = $row['dhis_orgunit+name'];
                    }
                    if(empty($this->fields) || (!empty($this->fields) && (in_array('facility.dhis_uid',$fields_array)))){
                        $data['dhis_uid'] = $row['dhis_orgunit+dhis_uid'];
                    }
                }
            
//                if ( $row['district+name'] || $row['sub_county+name'] || $row['parish+name'] || $row['village+name'] ) {
                if(empty($this->fields) || (!empty($this->fields) && (in_array('location.district',$fields_array)))){
                    $data['districtName'] = $row['district+name'];
                }
                if(empty($this->fields) || (!empty($this->fields) && (in_array('location.subcounty',$fields_array)))){
                    $data['subCountyName'] = $row['sub_county+name'];
                }
                if(empty($this->fields) || (!empty($this->fields) && (in_array('location.parish',$fields_array)))){
                    $data['parishName'] = $row['parish+name'];
                }
                if(empty($this->fields) || (!empty($this->fields) && (in_array('location.village',$fields_array)))){
                    $data['villageName'] = $row['village+name'];
                }
  //              }

                if ( $this->useJSON ) {
                    $this->create_Person( $data, $entry['resource'] );
                    $top['entry'][] = $entry;
                }
            }
            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
            return true;
        } 
        
        else {
            http_response_code(500);
            return false;
        }

    }

    /**
     * Create a Person FHIR object based on the given array
     * @param array $data
     * @param DOMNode $top The node to append data to.
     * @return string
     */
    protected function create_Person( $data, &$top ) {

        if ( $this->useJSON ) {    
            $top['id'] = $data['id'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }

            if ( array_key_exists( 'identification', $data ) ) {
		    $top['identifier'] = array();
                foreach( $data['identification'] as $ids => $id) {
    
                    $identifierItem= array( 
                    'value' => $id['id_num']
                    );

                    if(isset($top['identifier']))
                    {
                        array_push($top['identifier'],$identifierItem);
                    }
                }
                
            }

            if ( array_key_exists( 'firstname', $data ) || array_key_exists( 'surname', $data ) || array_key_exists('fullname', $data)) {
                $top['name'] = array();
                //$top['name'][] = array( 'resourceType' => 'HumanName' );
                //$top['name'][0]['use'] = "official";
                if ( array_key_exists('fullname', $data) ) {
                    $top['name'][0]['text'] = $data['fullname'];
                }
                if ( array_key_exists( 'firstname', $data ) ) { 
                    $top['name'][0]['firstname'] = $data['firstname'];
                }
                if ( array_key_exists('surname', $data) ) {
                    $top['name'][0]['surname'] = $data['surname'];
                }
                if ( array_key_exists('othername', $data) ) {
                    $top['name'][0]['othername'] = $data['othername'];
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
                                
                                $top['telecom'][] = array('use' => $use ,'value' => $contact );
                            
                            }
                        }
                    }
                }
            }

            if ( array_key_exists( 'facilityName', $data ) || array_key_exists( 'dhis_name', $data ) || array_key_exists( 'dhis_uid', $data )) {
                $top['facility'] = array();
                if ( array_key_exists( 'facilityName', $data )){
		    $top['facility']['name'] = $data['facilityName'];
                }
                if ( array_key_exists( 'dhis_name', $data )){
                    $top['facility']['dhis_name'] = $data['dhis_name'];
                }
                if ( array_key_exists( 'dhis_uid', $data )){
                    $top['facility']['dhis_uid'] = $data['dhis_uid'];
                }
            }

//            if ( array_key_exists( 'districtName', $data ) || array_key_exists( 'subCountyName', $data ) || array_key_exists( 'parishName', $data ) || array_key_exists( 'villageName', $data )) {
                $top['location'] = array();
                if ( array_key_exists( 'districtName', $data )){
                    $top['location']['district'] = $data['districtName'];
                }
                if ( array_key_exists( 'subCountyName', $data )){
                    $top['location']['subCounty'] = $data['subCountyName'];
                }
                if ( array_key_exists( 'parishName', $data )){
                    $top['location']['parish'] = $data['parishName'];
                }
                if ( array_key_exists( 'villageName', $data )){
                   $top['location']['village'] = $data['villageName'];
                }
                //$top['location'] = array('district' => $data['districtName'], 'subCounty' => $data['subCountyName'], 'parish' => $data['parishName'], 'village' => $data['villageName'] );
  //          }

         } else {
            
        }

    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

