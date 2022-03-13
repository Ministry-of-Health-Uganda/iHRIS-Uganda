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


class iHRIS_Page_FHIR_Resource_new extends I2CE_Page{

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
     * @var string the url of the server where the binary data  are saved
     */
    public $binaryServer;


     /**
     * @var string the file extension of the pratitioner photo
     */
    public $photoFileExtension;
    /**
     * @var string the default nationality for practitioner
     */
    public $defaultNationality;
    /**
     * @var string the default standard education 
     */
    public $defaultEducationQualificationCode;
    /**
     * @var string the default standard education 
     */
    public $defaultRegistrationType;
     /**
     * @var string the default standard education 
     */
    public $defaultNoDiplomaTrainingQualificationType;
    /**
     * Handles creating hte I2CE_TemplateMeister templates and loading any default templates
     * @returns boolean true on success
     */
    public $iHRISPersonBaseForm;
    protected function initializeTemplate() {
        //we don't want any templates for this
        //but we need to make something for post processing calls that
        //may happen
        $this->template = new I2CE_Template();
        $this->template->loadRootText('');
        $this->binaryServer= "http://localhost/ihris_photo/";
        $this->photoFileExtension=".jpg";
        $this->defaultNationality="Guinéenne";
        $this->defaultRegistrationType="Registration";
        $this->defaultNoDiplomaTrainingQualificationType="Other Certification";
        $sourceForm="person";
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
            //if ( call_user_func_array( array( $this, "getUpdates_" . $this->args['resource'] ), array( &$top ) ) ) {
            if ( call_user_func_array( array( $this, "getUpdatesMapping_" . $this->args['resource'] ), array( &$top ) ) ) {
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
            $data['family'] = $person->surname;
            $data['given'] = array( $person->firstname, $person->othername );

            $person->populateChildren('demographic');
            $demo = current($person->getChildren('demographic'));
            if ( $demo ) {
                if ( $demo->getField('gender')->getDBValue() == 'gender|M' ) {
                    $data['gender'] = 'male';
                } elseif ( $demo->getField('gender')->getDBValue() == 'gender|F' ) {
                    $data['gender'] = 'female';
                }
                if ( !$demo->birth_date->isBlank() ) {
                    $birth = $demo->birth_date->dbFormat();
                    $data['birthDate'] = $birth;
                }
                if($demo->getField('marital_status'))
                {
                    $data['marital_status'] = $demo->getField('marital_status')->getDBValue();
                }
                if($demo->dependents)
                {
                    $data['dependents'] = $demo->dependents;
                }
                $data['parent'] =array();
                if($demo->name_father)
                {
                    $data['parent']['fatherNames'] = $demo->name_father;
                }
                if($demo->name_mother)
                {
                    $data['parent']['motherNames'] = $demo->name_mother;
                }
                
            }
            $data['nationality']=$this->defaultNationality;
            $data['telecom'] = array( 
                    'work' => array( 'phone' => array(), 'email' => array() ), 
                    'mobile' => array( 'phone' => array() ), 
                    'home' => array( 'phone' => array(), 'email' => array() ),
                    'emergency' => array( 'phone' => array(), 'email' => array(),'kindship'=> array()) 
                    );
            $data['address'] = array();

            $person->populateChildren('person_contact_work');
            $work = current($person->getChildren('person_contact_work'));
            if ( $work ) {
                $data['telecom']['work'] = array();
                if ( $work->telephone ) {
                    $data['telecom']['work']['phone'][] = $work->telephone;
                }
                if ( $work->mobile_phone ) {
                    $data['telecom']['mobile']['phone'][] = $work->mobile_phone;
                }
                if ( $work->email ) {
                    $data['telecom']['work']['email'][] = $work->email;
                }
                if ( $work->address ) {
                    $data['address']['work'] = $work->address;
                }
            }

            $person->populateChildren('person_contact_personal');
            $personal = current($person->getChildren('person_contact_personal'));
            if ( $personal ) {
                $data['telecom']['home'] = array();
                if ( $work->telephone ) {
                    $data['telecom']['home']['phone'][] = $personal->telephone;
                }
                if ( $work->mobile_phone ) {
                    $data['telecom']['mobile']['phone'][] = $personal->mobile_phone;
                }
                if ( $work->email ) {
                    $data['telecom']['home']['email'][] = $personal->email;
                }
                if ( $personal->address ) {
                    $data['address']['home'] = $personal->address;
                }
            }

            $person->populateChildren('person_contact_emergency');
            $emergency = current($person->getChildren('person_contact_emergency'));
            if ( $emergency ) {

                $data['telecom']['emergency'] = array();
                if ( $emergency->telephone ) {
                    $data['telecom']['emergency']['phone'][] = $emergency->telephone;
                }
                if ( $emergency->mobile_phone ) {
                    $data['telecom']['emergency']['phone'][] = $emergency->mobile_phone;
                }
                if ( $emergency->email ) {
                    $data['telecom']['emergency']['email'][] = $emergency->email;
                }
                if ( $emergency->lien_parent ) {
                    $data['telecom']['emergency']['kindship'] = $emergency->lien_parent;
                }
                if ( $emergency->address ) {
                    $data['address']['emergency'] = $emergency->address;
                }
                
            }
            $person->populateChildren('person_photo_passport');
            $photo = current($person->getChildren('person_photo_passport'));
            $data['photo'] = array('url'=>'','creation'=>null);
            if($photo)
            {
                if($photo->image)
                {
                    $data['photo'] ['url']=$this->binaryServer.explode('|',$person_id)[1].$this->photoFileExtension;
                    $data['photo'] ['creation']=$photo->date->getDateTimeObj()->format('c');

                }

            }

            //get additional identification
            $data['identification'] = array();
            $id_count = 0;
            $person->populateChildren('person_id');
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
            }
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
                $data['education'][$id_count]['degree']= $person_education_form->getField('degree')->getDisplayValue();
                $data['education'][$id_count]['institution']=$person_education_form->institution;
                $data['education'][$id_count]['major']=$person_education_form->major;
                $data['education'][$id_count]['start_year']=$person_education_form->getField('start_year')->getValue()->getDateTimeObj()->format('c');
                $data['education'][$id_count]['end_year']=$person_education_form->getField('year')->getValue()->getDateTimeObj()->format('c');

            }
            
            $this->create_Practitioner( $data, $top );

        }
        return true;
    }
    protected function loadData_Organization( $uuid, &$top ) {
        return true;
    }
    /**
     * Create the bundle of Organization updated since a given time.
     * @param DOMNode $top
     * @return boolean
     */
    protected function getUpdates_Organization( &$top ) {
        //echo "Entered !!!";
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

        $required_forms = array("education","job_dip_training","job_nodip_training","council","deployment_history","person_id");
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }
        $where = '';
        $params = array();
        if ( $this->since ) {
            $where = "WHERE education.last_modified >= ? AND job_dip_training >=?";
            $params = array();
        }
        $qry = "SELECT DISTINCT(LOWER(education.institution)) as institution,education.institution as name,'edu' as org_type,DATE_FORMAT(education.last_modified ,'%Y-%m-%d %T') as lastupdated from hippo_education as education
        UNION SELECT DISTINCT(LOWER(job_dip_training.institution)) as institution,job_dip_training.institution as name,'other' as org_type,DATE_FORMAT(job_dip_training.last_modified ,'%Y-%m-%d %T') as lastupdated from hippo_job_dip_training as job_dip_training
        UNION SELECT DISTINCT(LOWER(job_nodip_training.institution)) as institution,job_nodip_training.institution as name,'other' as org_type,DATE_FORMAT(job_nodip_training.last_modified ,'%Y-%m-%d %T') as lastupdated from hippo_job_nodip_training as job_nodip_training
        UNION SELECT DISTINCT(LOWER(council.name)) as institution,council.name as name,'team' as org_type,DATE_FORMAT(council.last_modified ,'%Y-%m-%d %T') as lastupdated from hippo_council as council
        UNION SELECT DISTINCT(LOWER(deployment.facility)) as institution,deployment.facility as name,'other' as org_type,DATE_FORMAT(deployment.last_modified ,'%Y-%m-%d %T') as lastupdated from hippo_deployment_history as deployment";
         try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            $count=0;
            while ( $row = $stmt->fetch() ) {
                //subquery to extract person ids
                $data = array();
                $organizationId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $row->institution));
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Organization/" . $organizationId;
                    $entry['resource'] = array( 'resourceType' => 'Organization' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Organization/" . $organizationId );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $organization = $this->doc->createElement( "Organization" );
                }
                //$organizationId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $row->institution));
                $data['uuid'] = $organizationId;
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $organizationId;
                $data['active'] = 'true';
                $data['name']= $row->name;
                if($row->org_type)
                {
                    $data['type']=$row->org_type;
                }
                $count++;
                /*
                if($row->telephone)
                {
                    $data['telecom'] = array( 
                        'work' => array( 'phone' => array(), 'email' => array() ),
                        );
                    $data['telecom']['work']['phone'][] = $row->telephone;
                }*/
                if ( $this->useJSON ) {
                    //print_r($data);
                    $this->create_Organization( $data, $entry['resource'] );
                    $top['entry'][] = $entry;
                } else {
                    $this->create_Organization( $data, $organization );
                    $resource->appendChild($organization);
                    $entry->appendChild( $resource );
                    $top->appendChild( $entry );
                }

            }
            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
            $stmt->closeCursor();
            unset( $stmt );
            return true;

        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
            http_response_code(500);
            return false;
        }
        
    }

    /**
     * Create the bundle of Person linked to the practitioer since a given time
     *@param DOMNode $top
     *@return boolean
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

        $required_forms = array("spouse","dependent");
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }
        $params=array();
        //Extract spouse information
        $qry="SELECT DATE_FORMAT(spouse.last_modified ,'%Y-%m-%d %T') as lastupdated,spouse.id as uuid,spouse.firstname as given, 
        spouse.surname as family, DATE_FORMAT(spouse.dob ,'%Y-%m-%d') as birthdate,null as sexe,spouse.profession,spouse.telephone,
        person.csd_uuid as practitioner 
        from hippo_spouse as spouse 
        left join hippo_person person on person.id=spouse.parent ";
        //Extract children information
        $qryChildren="SELECT DATE_FORMAT(dependent.last_modified ,'%Y-%m-%d %T') as lastupdated,dependent.id as uuid,
        dependent.first_name as given, dependent.name as family, DATE_FORMAT(dependent.date_of_birth ,'%Y-%m-%d') as birthdate,
        dependent.gender as gender,dependent.profession,dependent.telephone,person.csd_uuid as practitioner 
        from hippo_dependent as dependent 
        left join hippo_person person on person.id=dependent.parent ";

        try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            $count = 0;
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            $count=0;
            while ( $row = $stmt->fetch() ) {
                $data = array();
                $personId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $row->uuid));
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Person/" . $personId;
                    $entry['resource'] = array( 'resourceType' => 'Person' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Person/" . $personId );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $organization = $this->doc->createElement( "Person" );
                }
                $data['uuid'] = $personId;
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row->uuid;
                $data['active'] = 'true';
                $data['family'] = $row->family;
                $data['given'] = array( $row->given );
                
                if($row->birthdate && $row->birthdate!="0000-00-00")
                {
                    $data['birthDate'] = $row->birthdate;
                }
                
                $extensionCounter=0;
                $data['extension']["iHRISPersonPractitionerRelationship"][$extensionCounter]["code_Relationship"]="spouse";
                if($row->profession)
                {
                    $data['extension']["iHRISPersonQualification"][$extensionCounter]['value_Profession']=$row->profession;
                    
                }
                if($row->telephone)
                {
                    $data['telecom'] = array( 
                        'work' => array( 'phone' => array(), 'email' => array() ),
                        );
                    $data['telecom']['work']['phone'][] = $row->telephone;
                }
                $data['link'] = array(array('target'=>array('reference'=>"Practitioner/" . $row->practitioner)));
                $count++;
                if ( $this->useJSON ) {
                    //print_r($data);
                    $this->create_Person( $data, $entry['resource'] );
                    $top['entry'][] = $entry;
                } else {
                    $this->create_Person( $data, $organization );
                    $resource->appendChild($organization);
                    $entry->appendChild( $resource );
                    $top->appendChild( $entry );
                }

            }
            $stmt->closeCursor();
            unset( $stmt );
            //query for extracting deployment history
            $stmt = $db->prepare( $qryChildren );
            $stmt->execute( $params );
            while ( $row = $stmt->fetch() ) {
                $data = array();
                $personId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $row->uuid));
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Person/" .$personId;
                    $entry['resource'] = array( 'resourceType' => 'Person' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Person/" .$personId);
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $organization = $this->doc->createElement( "Person" );
                }
                $data['uuid'] = $personId;
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row->uuid;
                $data['active'] = 'true';
                $data['family'] = $row->family;
                $data['given'] = array( $row->given );
                if($row->birthdate && $row->birthdate!="0000-00-00")
                {
                    $data['birthDate'] = $row->birthdate;
                }
                $extensionCounter=0;
                $data['extension']["iHRISPersonPractitionerRelationship"][$extensionCounter]["code_Relationship"]="children";
                if ( $row->gender == 'gender|M' ) {
                    $data['gender'] = 'male';
                } elseif ( $row->gender == 'gender|F' ) {
                    $data['gender'] = 'female';
                }
                if($row->profession)
                {
                    $data['extension']["iHRISPersonQualification"][$extensionCounter]['value_Profession']=$row->profession;
                    
                }
                if($row->telephone)
                {
                    $data['telecom'] = array( 
                        'work' => array( 'phone' => array(), 'email' => array() ),
                        );
                    $data['telecom']['work']['phone'][] = $row->telephone;
                }
                $data['link'] = array(array('target'=>"Practitioner/" . $row->practitioner));
                $count++;
                if ( $this->useJSON ) {
                    //print_r($data);
                    $this->create_Person( $data, $entry['resource'] );
                    $top['entry'][] = $entry;
                } else {
                    $this->create_Person( $data, $organization );
                    $resource->appendChild($organization);
                    $entry->appendChild( $resource );
                    $top->appendChild( $entry );
                }

            }


            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
            $stmt->closeCursor();
            unset( $stmt );
            return true;

        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
            http_response_code(500);
            return false;
        }
    }
    /**
     * Create a practitioner FHIR object based on the given array
     * */
    protected function create_Person( $data, &$top ) {
        $top["extension"]=array();
        if ( $this-> useJSON ) { 
            $top['id'] = $data['uuid'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }
            $globalIdSystem=null;
            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $globalIdSystem= $data['id_system'];
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }
            if ( array_key_exists( 'identification', $data ) ) {
                //$top['identification'] = array();
                foreach( $data['identification'] as $ids => $id) {
                    $globalIdSystem=$globalIdSystem!=null?$globalIdSystem:"http://ihris.org/";
                    $identifierItem= array( 'system' =>$globalIdSystem."id_type/".$id['id_type'], 
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
            if ( array_key_exists( 'qualification', $data ) ) {
                $top['qualification'] = array();
                foreach( $data['qualification'] as $ids => $education) {
                    if(array_key_exists( 'extension', $education ))
                    {
                        $qualification=null;
                        if($education['degree'])
                        {
                            $qualification = array('identifier'=>array(array('value'=>$education['major'])),
                            'code'=>array('text'=>$education['degree']),
                            'period'=>array('start'=>$education['start_date']),
                            'issuer'=>array('reference' =>$education['issuer']),
                            'extension'=>array()); 
                        }
                        else
                        {
                            $qualification = array('identifier'=>array(array('value'=>$education['major'])),
                            'period'=>array('start'=>$education['start_date']),
                            'issuer'=>array('reference' =>$education['issuer']),
                            'extension'=>array());
                        }
                        
                        
                        foreach($education['extension'] as $extensionInfo=>$extensionInfoList)
                        {
                            
                            /* echo "\n---- Qualification extension --- \n";
                            print_r($extensionInfoList); */
                            $qualification['extension']=array('url'=>'http://ihris.org/fhir/StructureDefinition/'.$extensionInfo,
                                'extension'=>array());
                            foreach($extensionInfoList as  $keyFieldName => $fieldValue)
                            {
                                //$qualification['extension']['extension'][]=array()
                                if(strpos($keyFieldName,"date_")!==false)
                                {
                                    $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDate'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"datetime_")!==false)
                                {
                                    $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDateTime'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"value_")!==false)
                                {
                                    $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"code_")!==false)
                                {
                                    if(!is_array($fieldValue))
                                    {
                                        $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>array("code"=>$fieldValue));
                                    }
                                    else{
                                        $arrayCode=array();
                                        foreach($fieldValue as $value){
                                            $arrayCode[]=array('code'=>$value);
                                        }
                                        $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>[$arrayCode]);
                                    }
                                
                                }
                                else{
                                    $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                }
                            }
                        }
                        array_push($top['qualification'],$qualification);

                    }
                    else
                    {   
                        $qualification = array('identifier'=>array(array('value'=>$education['major'])),
                        'code'=>array('text'=>$education['degree']),
                        'period'=>array('start'=>$education['start_date']),
                        'issuer'=>array('reference' =>$education['issuer'])
                        );
                        array_push($top['qualification'],$qualification);
                    }
                }
            }
            if(array_key_exists( 'extension', $data ))
            {
                foreach ( $data['extension'] as $keyExtension => $extensionInfoList ) {
                    
                    if(!empty($extensionInfoList))
                    {
                        foreach($extensionInfoList as $counter=>$extensionInfo){
                            //print_r($extensionInfo);
                            $practitionerExtension=array('url'=>"http://ihris.org/fhir/StructureDefinition/".$keyExtension,
                            'extension'=>array());
                            foreach ( $extensionInfo as $keyFieldName => $fieldValue ) {
                                if(strpos($keyFieldName,"date_")!==false)
                                {
                                    $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDate'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"datetime_")!==false)
                                {
                                    $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDateTime'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"value_")!==false)
                                {
                                    $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"code_")!==false)
                                {
                                    //print_r($keyFieldName.":".$fieldValue);
                                    if(!is_array($fieldValue))
                                    {
                                        $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>array("code"=>$fieldValue));
                                    }
                                    else{
                                        $arrayCode=array();
                                        foreach($fieldValue as $value){
                                            $arrayCode[]=array('code'=>$value);
                                        }
                                        $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>[$arrayCode]);
                                    }
                                
                                }
                            }
                        array_push($top["extension"],$practitionerExtension);
                        }
                        
                    }

                }
            }
            $top['link']=$data['link'];
        }//end if use->JSON
        else{
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


        $required_forms = array( "person", "demographic", "person_contact_personal", "person_contact_work","person_id",
        "person_contact_emergency","person_photo_passport","lien_parent","person_language","language","education","job_dip_training","degree",
        "job_nodip_training","engagement","sector","profession","specialisation","degree","type_recrutement","type_acte_engagement","status_admin",
        "hier","classe","echelon" );
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }

        $where = '';
        $params = array();
        if ( $this->since ) {
            $where = "WHERE GREATEST(person.last_modified, IFNULL(demographic.last_modified,'0000-00-00 00:00:00'), IFNULL(personal.last_modified,'0000-00-00 00:00:00'), IFNULL(work.last_modified,'0000-00-00 00:00:00')) >= ?";
            $params = array( $this->since );
        }

        $qry = "SELECT person.csd_uuid AS uuid, DATE_FORMAT(GREATEST(person.last_modified, IFNULL(demographic.last_modified,'0000-00-00 00:00:00'), 
        IFNULL(personal.last_modified,'0000-00-00 00:00:00'), IFNULL(work.last_modified,'0000-00-00 00:00:00')), '%Y-%m-%d %T') AS lastupdated, 
        person.id, person.surname AS family, person.firstname AS given, person.othername AS given2, demographic.gender, demographic.marital_status,
        demographic.dependents,demographic.name_father,demographic.name_mother,
        DATE_FORMAT(demographic.birth_date,'%Y-%m-%d') AS birthdate, personal.telephone AS home_phone, personal.mobile_phone AS home_mobile, 
        personal.email AS home_email, personal.address AS home_address, work.telephone AS work_phone, work.mobile_phone AS work_mobile, work.email AS work_email, 
        work.address AS work_address,emergency.telephone as emergency_phone,emergency.mobile_phone as emergency_mobile,emergency.email as emergency_email,
        photo.date AS photo_date,photo.image,
        kindship.name as emergency_lien_parent,emergency.address as emergency_address   
        FROM hippo_person AS person 
        LEFT JOIN hippo_demographic AS demographic ON demographic.parent = person.id 
        LEFT JOIN hippo_person_contact_personal AS personal ON personal.parent = person.id 
        LEFT JOIN hippo_person_contact_work AS work ON work.parent = person.id 
        LEFT JOIN hippo_person_contact_emergency AS emergency ON emergency.parent = person.id
        LEFT JOIN hippo_person_photo_passport AS photo ON photo.parent = person.id
        LEFT JOIN hippo_lien_parent AS kindship ON kindship.id = emergency.lien_parent
        $where ORDER BY lastupdated ASC";
        
        //Extract Administrative status
        try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            $count = 0;
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            while ( $row = $stmt->fetch() ) {
                //subquery to extract person ids
                $data = array();
                $count++;
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Practitioner/" . $row->uuid;
                    $entry['resource'] = array( 'resourceType' => 'Practitioner' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Practitioner/" . $row->uuid );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $practitioner = $this->doc->createElement( "Practitioner" );
                }
                $subQuery="SELECT person_id.*,id_type.name as id_type 
                from hippo_person_id as person_id 
                LEFT JOIN hippo_id_type as id_type ON person_id.id_type = id_type.id where person_id.parent=? ";
                $paramSub=array($row->id);
                $subStmt = $db->prepare( $subQuery );
                $subStmt->execute($paramSub);
                
                $id_count=0;
                while ( $subRow = $subStmt->fetch() ) {
                        $data['identification'][$id_count] = array();
                        $data['identification'][$id_count]['id_type'] = $subRow->id_type;
                        $data['identification'][$id_count]['id_num'] = $subRow->id_num;
                        if(isset($subRow->place))
                        {
                            $data['identification'][$id_count]['assigner'] =$subRow->place;
                        }
                        else
                        {
                            $data['identification'][$id_count]['assigner'] =null;
                        }
                        if($subRow->expiration_date && $subRow->expiration_date!="0000-00-00 00:00:00")
                        {
                            $expirationDate = new DateTime($subRow->expiration_date);
                            if($expirationDate->format('Y-m-d')!='0000-00-00' ){
                                $data['identification'][$id_count]['expirationDate'] = $expirationDate->format('c');
                            }

                            
                        }
                        /* else{
                            $data['identification'][$id_count]['expirationDate'] =null;
                        } */
                        if($subRow->issue_date && $subRow->issue_date!="0000-00-00 00:00:00")
                        {
                            $issueDate=new DateTime($subRow->issue_date);

                            $data['identification'][$id_count]['issueDate'] = $issueDate->format('c');
                        }
                        /* else{
                            $data['identification'][$id_count]['issueDate']=null;
                        } */
                        $id_count++;
                }
                $subStmt->closeCursor();
                unset( $subStmt);
                //subQuery to extract person languages
                $subQuery="SELECT person_language.id,language.name as language,person_language.reading,person_language.speaking,person_language.writing
                     from hippo_person_language as person_language 
                     LEFT JOIN hippo_language as language on language.id= person_language.language
                     where person_language.parent=? ";
                $paramSub=array($row->id);
                $subStmt = $db->prepare( $subQuery );
                $subStmt->execute($paramSub);
                //$data = array();
                $counter=0;
                while ( $subRow = $subStmt->fetch() ) {
                    $data['communication'][$counter] = array();
                    $data['communication'][$counter]['language']= $subRow->language;
                    if(isset($subRow->reading))
                    {
                        $data['communication'][$counter]['reading']= $subRow->reading;
                    }
                    if(isset($subRow->speaking))
                    {
                        $data['communication'][$counter]['speaking']= $subRow->speaking;
                    }
                    if(isset($subRow->writing))
                    {
                        $data['communication'][$counter]['writing']= $subRow->writing;
                    }
                    $counter++;
                }
                $subStmt->closeCursor();
                unset( $subStmt);
                //subQuery to extract educations
                $subQuery="SELECT degree.name as degree,education.institution,education.major,
                DATE_FORMAT(education.start_year,'%Y') as start_date,DATE_FORMAT(education.year,'%Y') as end_date 
                FROM `hippo_education` AS education 
                LEFT JOIN hippo_degree AS degree ON degree.id = education.degree
                WHERE education.parent=? ";
                $paramSub=array($row->id);
                $subStmt = $db->prepare( $subQuery );
                $subStmt->execute($paramSub);
                //$data = array();
                $counter=0;
                while ( $subRow = $subStmt->fetch() ) {
                    $data['qualification'][$counter] = array();

                    if($subRow->degree){
                        $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['code_TypeTraining']="Education";
                        $data['qualification'][$counter]['degree']= $subRow->degree;
                    }
                    if($subRow->major){
                        $data['qualification'][$counter]['major']=$subRow->major;
                    }
                    if($subRow->start_date && $subRow->start_date!="0000-00-00"){
                        $formatedDate=null;
                        if(strlen($subRow->start_date)<=4)
                        {
                            $formatedDate=new DateTime($subRow->start_date."-01-01");
                        }
                        else{
                            $formatedDate=new DateTime($subRow->start_date);
                        }
                        
                        $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['date_startDate']=$formatedDate->format('c');
                    }
                    if($subRow->end_date && $subRow->end_date!="0000-00-00"){
                        $formatedDate=null;
                        if(strlen($subRow->end_date)<=4)
                        {
                            $formatedDate=new DateTime($subRow->end_date."-01-01");
                        }
                        else{
                            $formatedDate=new DateTime($subRow->end_date);
                        }
                        $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['date_endDate']=$formatedDate->format('c');
                        
                        $data['qualification'][$counter]['start_date']=$formatedDate->format('c');
                    }
                    if($subRow->institution){
                        //$institutionRefId=strtolower(preg_replace('/\s+/', '', $subRow->institution));
                        $institutionRefId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $subRow->institution));
                        $data['qualification'][$counter]['issuer']="Organization/".$institutionRefId;
                    }
                    else{
                        $data['qualification'][$counter]['issuer']=null;
                    }
                    $counter++;
                }
                $subStmt->closeCursor();
                unset( $subStmt);
                //subQuery to extract in training with  certification
                $subQuery="SELECT DATE_FORMAT(training_dip.last_modified ,'%Y-%m-%d %T') as lastupdated,
                DATE_FORMAT(training_dip.completion_date,'%Y-%m-%d') as end_date,DATE_FORMAT(training_dip.start_date,'%Y-%m-%d') as start_date,
                training_dip.course as major,training_dip.diploma as degree,training_dip.institution
                from hippo_job_dip_training as training_dip
                    WHERE training_dip.parent=? ";
                $paramSub=array($row->id);
                $subStmt = $db->prepare( $subQuery );
                $subStmt->execute($paramSub);
                while ( $subRow = $subStmt->fetch() ) {
                    if(!array_key_exists('qualification', $data) )
                    {
                        $data['qualification'][$counter] = array();
                    }
                    $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['code_TypeTraining']="In-job training with certification";
                    if($subRow->degree){
                        
                        $data['qualification'][$counter]['degree']= $subRow->degree;
                    }
                    if($subRow->major){
                        $data['qualification'][$counter]['major']=$subRow->major;
                    }
                    if($subRow->start_date && $subRow->start_date!="0000-00-00"){
                        $formatedDate=new DateTime($subRow->start_date);
                        $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['date_startDate']=$formatedDate->format('c');
                    }
                    if($subRow->end_date && $subRow->end_date!="0000-00-00"){
                        $formatedDate=new DateTime($subRow->end_date);
                        $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['date_endDate']=$formatedDate->format('c');

                        $data['qualification'][$counter]['start_date']=$formatedDate->format('c');
                    }
                    if($subRow->institution){
                        //$institutionRefId=strtolower(preg_replace('/\s+/', '', $subRow->institution));
                        $institutionRefId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $subRow->institution));
                        $data['qualification'][$counter]['issuer']="Organization/".$institutionRefId;
                    }
                    else{
                        $data['qualification'][$counter]['issuer']=null;
                    }
                    
                    $counter++;
                }
                $subStmt->closeCursor();
                unset( $subStmt);
                //subQuery to extract in training with no certification
                $subQuery="SELECT DATE_FORMAT(training_nodip.last_modified ,'%Y-%m-%d %T') as lastupdated,
                DATE_FORMAT(training_nodip.completion_date,'%Y-%m-%d') as end_date,DATE_FORMAT(training_nodip.start_date,'%Y-%m-%d') as start_date,
                training_nodip.course as major,training_nodip.institution as institution,training_nodip.duration as duration
                from hippo_job_nodip_training as training_nodip
                    WHERE training_nodip.parent=? ";
                $paramSub=array($row->id);
                $subStmt = $db->prepare( $subQuery );
                $subStmt->execute($paramSub);
                while ( $subRow = $subStmt->fetch() ) {
                    if(!array_key_exists('qualification', $data) )
                    {
                        $data['qualification'][$counter] = array();
                    }
                    $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['code_TypeTraining']="In-job training with no certification";
                    if($this->defaultNoDiplomaTrainingQualificationType)
                    {
                        $data['qualification'][$counter]['degree']= $this->defaultNoDiplomaTrainingQualificationType;
                    }
                    
                    if($subRow->major){
                        $data['qualification'][$counter]['major']=$subRow->major;
                    }
                    if($subRow->start_date && $subRow->start_date!="0000-00-00"){
                        $formatedDate=new DateTime($subRow->start_date);
                        $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['date_startDate']=$formatedDate->format('c');
                    }
                    if($subRow->end_date && $subRow->end_date !="0000-00-00"){
                        $formatedDate=new DateTime($subRow->end_date);
                        $data['qualification'][$counter]['extension']['iHRISQualificationDetails']['date_endDate']=$formatedDate->format('c');
                        $data['qualification'][$counter]['start_date']=$formatedDate->format('c');
                    }
                    if($subRow->institution){
                        //$institutionRefId=strtolower(preg_replace('/\s+/', '', $subRow->institution));
                        $institutionRefId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $subRow->institution));
                        $data['qualification'][$counter]['issuer']="Organization/".$institutionRefId;
                    }
                    else{
                        $data['qualification'][$counter]['issuer']=null;
                    }
                    $counter++;
                }
                $subStmt->closeCursor();
                unset( $subStmt);
                //Subquery to extract licence information
                $subQuery="SELECT registration.registration_number as major,council.name as institution,DATE_FORMAT(registration.registration_date,'%Y-%m-%d') as start_date, 
                DATE_FORMAT(registration.license_expiration,'%Y-%m-%d') as end_date 
                from hippo_registration as registration 
                left join hippo_council as council on council.id=registration.council 
                WHERE registration.parent=?";
                $paramSub=array($row->id);
                $subStmt = $db->prepare( $subQuery );
                $subStmt->execute($paramSub);
                $counter=0;
                while ( $subRow = $subStmt->fetch() ) {
                    if(!array_key_exists('registration', $data) )
                    {
                        $data['registration'][$counter] = array();
                    }
                    if($this->defaultRegistrationType)
                    {
                        $data['registration'][$counter]['degree']= $this->defaultRegistrationType;
                    }
                    
                    if($subRow->major){
                        $data['registration'][$counter]['major']=$subRow->major;
                    }
                    if($subRow->start_date && $subRow->start_date!="0000-00-00"){
                        $formatedDate=new DateTime($subRow->start_date);
                        $data['registration'][$counter]['start_date']=$formatedDate->format('c');
                    }
                    if($subRow->end_date && $subRow->end_date != '0000-00-00' ){
                        $formatedDate=new DateTime($subRow->end_date);
                        $data['registration'][$counter]['end_date']=$formatedDate->format('c');
                    }
                    else
                    {
                        $data['registration'][$counter]['end_date']=null;  
                    }
                    if($subRow->institution){
                        //$institutionRefId=strtolower(preg_replace('/\s+/', '', $subRow->institution));
                        $institutionRefId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $subRow->institution));
                        $data['registration'][$counter]['issuer']="Organization/".$institutionRefId;
                    }
                    else{
                        $data['registration'][$counter]['issuer']=null;
                    }
                    $counter++;
                }



                //Extract professional situation
                $whereProfessionalSituation=" where engagement.parent=?";
                $qryProfesionalSituation="SELECT  DATE_FORMAT(engagement.last_modified ,'%Y-%m-%d %T') as lastupdated, sector.name as sector,profession.name as profession,
                specialisation.name as medical_specialisation,degree.name as degree,engagement.detail_specialite as detail_specialisation,
                type_recrutement.name as type_recrutement,type_acte.name as type_acte,engagement.act_engage as acte_engagement, 
                DATE_FORMAT(engagement.date_engage ,'%Y-%m-%d') as date_engagement
                FROM hippo_engagement as engagement
                LEFT JOIN hippo_sector as sector on sector.id=engagement.sector
                LEFT JOIN hippo_profession as profession on profession.id=engagement.profession
                LEFT JOIN hippo_specialisation as specialisation on specialisation.id=engagement.specialisation_medical
                LEFT JOIN hippo_degree as degree on degree.id=engagement.degree
                LEFT JOIN hippo_type_recrutement as type_recrutement on type_recrutement.id=engagement.type_recrutement
                LEFT JOIN hippo_type_acte_engagement as type_acte on type_acte.id=engagement.type_acte_engagement
                $whereProfessionalSituation ORDER BY lastupdated ASC ";

                $paramSub=array($row->id);
                $subStmt = $db->prepare( $qryProfesionalSituation );
                $subStmt->execute($paramSub);
                $counter=0;
                while ( $subRow = $subStmt->fetch() ) {
                    $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter] = array();
                    $lastUpdated = new DateTime($subRow->lastupdated);
                    $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]['datetime_lastUpdated']=$lastUpdated->format('c');
                    if($subRow->sector){
                        $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["code_Sector"]=$subRow->sector;
                    }
                    if($subRow->profession){
                        $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["code_Profession"]=$subRow->profession;
                    }
                    if($subRow->medical_specialisation){
                        $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["code_MedicalSpecialisation"]=$subRow->medical_specialisation;
                    }
                    if($subRow->degree){
                        $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["code_Degree"]=$subRow->degree;
                    }
                    if($subRow->detail_specialisation)
                    {
                        $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["value_DetailSpecialisation"]=$subRow->detail_specialisation;
                    }
                    if($subRow->type_recrutement){
                        $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["code_TypeRecrutement"]=$subRow->type_recrutement;
                    }
                    if($subRow->type_acte){
                        $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["code_TypeActe"]=$subRow->type_acte;
                    }
                    if($subRow->acte_engagement){
                        $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["value_ActeEngagement"]=$subRow->acte_engagement;
                    }
                    if($subRow->date_engagement){
                        if($subRow->date_engagement!="0000-00-00")
                        {
                            $data['extension']["iHRISPractitionerProfessionalSituationDetails"][$counter]["date_dateEngagement"]=$subRow->date_engagement;
                        }
                    }
                    $counter++;

                }
                $subStmt->closeCursor();
                unset( $subStmt);
                //Extract admin status
                $whereAdminStatus=" where status_admin.parent=?";
                $queryAdminStatus="SELECT  DATE_FORMAT(status_admin.last_modified ,'%Y-%m-%d %T') as lastupdated,
                hierarchy.name as hierarchy,class.name as classe,echellon.name as echellon,status_admin.indice  
                from hippo_status_admin as status_admin
                left join hippo_hier as hierarchy on hierarchy.id=status_admin.hier
                left join hippo_classe as class on class.id=status_admin.classe
                left join hippo_echelon as echellon on echellon.id=status_admin.echelon
                $whereAdminStatus ORDER BY lastupdated ASC";
                $paramSub=array($row->id);
                $subStmt = $db->prepare( $queryAdminStatus );
                $subStmt->execute($paramSub);
                $counter=0;
                while ( $subRow = $subStmt->fetch() ) {
                    $data['extension']["iHRISPractitionerAdminStatusDetails"][$counter] = array();
                    $lastUpdated = new DateTime($subRow->lastupdated);
                    $data['extension']["iHRISPractitionerAdminStatusDetails"][$counter]['date_lastupdated']=$lastUpdated->format('c');
                    if($subRow->hierarchy){
                        $data['extension']["iHRISPractitionerAdminStatusDetails"][$counter]["code_hierarchie"]=$subRow->hierarchy;
                    }
                    if($subRow->classe){
                        $data['extension']["iHRISPractitionerAdminStatusDetails"][$counter]["code_classe"]=$subRow->classe;
                    }
                    if($subRow->echellon){
                        $data['extension']["iHRISPractitionerAdminStatusDetails"][$counter]["code_echellon"]=$subRow->echellon;
                    }
                    if($subRow->indice){
                        $data['extension']["iHRISPractitionerAdminStatusDetails"][$counter]["value_indice"]=$subRow->indice;
                    }
                    $counter++;
                }
                $subStmt->closeCursor();
                unset( $subStmt);
                $whereBenefit=" where benefit.parent=?";
                $queryBenefit="SELECT DATE_FORMAT(benefit.last_modified, '%Y-%m-%d %T') AS lastupdated,SUBSTRING_INDEX(benefit.amount,'=',-1) as amount
                ,DATE_FORMAT(benefit.start_date,'%Y-%m-%d') as start_date,DATE_FORMAT(benefit.end_date,'%Y-%m-%d') as end_date,
                recurrence.name as recurrence,salary_source.name as salary_source,benefit_type.name as benefit_type 
                from hippo_benefit as benefit 
                LEFT JOIN hippo_currency As currency on currency.id=SUBSTRING_INDEX(benefit.amount,'=',1) 
                left join hippo_benefit_recurrence as recurrence on recurrence.id= benefit.recurrence 
                left join hippo_salary_source as salary_source on salary_source.id=benefit.source 
                left join hippo_benefit_type as benefit_type on benefit_type.id=benefit.type 
                $whereBenefit ORDER BY lastupdated ASC";
                $paramSub=array($row->id);
                $subStmt = $db->prepare( $queryBenefit );
                $subStmt->execute($paramSub);
                $counter=0;
                while ( $subRow = $subStmt->fetch() ) {
                    $data['extension']["iHRISPractitionerBenefitDetails"][$counter]=array();
                    $lastUpdated = new DateTime($subRow->lastupdated);
                    $data['extension']["iHRISPractitionerBenefitDetails"][$counter]["date_lastUpdated"]=$lastUpdated->format('c');
                    if($subRow->amount){
                        $data['extension']["iHRISPractitionerBenefitDetails"][$counter]["value_amount"]=$subRow->amount;
                    }
                    if($subRow->start_date && $subRow->start_date!=='0000-00-00'){
                        $data['extension']["iHRISPractitionerBenefitDetails"][$counter]["date_startDate"]=$subRow->start_date;
                    }
                    if($subRow->end_date && $subRow->end_date!=='0000-00-00')
                    {
                        $data['extension']["iHRISPractitionerBenefitDetails"][$counter]['date_endDate'] = $subRow->end_date;
                    }
                    if($subRow->recurrence){
                        $data['extension']["iHRISPractitionerBenefitDetails"][$counter]["code_recurrence"]=$subRow->recurrence;
                    }
                    if($subRow->salary_source){
                        $data['extension']["iHRISPractitionerBenefitDetails"][$counter]["code_salarySource"]=$subRow->salary_source;
                    }
                    if($subRow->benefit_type){
                        $data['extension']["iHRISPractitionerBenefitDetails"][$counter]["code_benefitType"]=$subRow->benefit_type;
                    }
                    $counter++;
                }
                $subStmt->closeCursor();
                unset( $subStmt);

                $data['uuid'] = $row->uuid;
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row->id;
                $data['active'] = 'true';
                $data['family'] = $row->family;
                $data['given'] = array( $row->given );
                if ( $row->given2 ) {
                    $data['given'][] = $row->given2;
                }
                if ( $row->gender == 'gender|M' ) {
                    $data['gender'] = 'male';
                } elseif ( $row->gender == 'gender|F' ) {
                    $data['gender'] = 'female';
                }
                if($row->marital_status)
                {
                    $data['maritalStatus'] = $row->marital_status;
                }
                if($row->dependents)
                {
                    $data['dependents'] = $row->dependents;
                }
                $data['parent'] =array();
                if($row->name_father){
                    $data['parent'] ['fatherNames']= $row->name_father;
                }
                if($row->name_mother){
                    $data['parent'] ['motherNames']= $row->name_mother;
                }

                $data['nationality']=$this->defaultNationality;
                $data['birthDate'] = $row->birthdate;
                $data['telecom'] = array( 
                        'work' => array( 'phone' => array(), 'email' => array() ),
                        'mobile' => array( 'phone' => array() ),
                        'home' => array( 'phone' => array(), 'email' => array() ),
                        'emergency' => array( 'phone' => array(), 'email' => array(),'kindship'=> array()),
                        );
                $data['address'] = array();
                if ( $row->home_phone ) {
                    $data['telecom']['home']['phone'][] = $row->home_phone;
                }
                if ( $row->home_mobile ) {
                    $data['telecom']['mobile']['phone'][] = $row->home_mobile;
                }
                if ( $row->home_email ) {
                    $data['telecom']['home']['email'] = $row->home_email;
                }
                if ( $row->home_address ) {
                    $data['address']['home'] = $row->home_address;
                }
                if ( $row->work_phone ) {
                    $data['telecom']['work']['phone'][] = $row->work_phone;
                }
                if ( $row->work_mobile ) {
                    $data['telecom']['mobile']['phone'][] = $row->work_mobile;
                }
                if ( $row->work_email ) {
                    $data['telecom']['mobile']['email'][] = $row->work_email;
                }
                if ( $row->work_address ) {
                    $data['address']['work'] = $row->work_address;
                }
                //
                if ( $row->emergency_phone ) {
                    $data['telecom']['emergency']['phone'][] = $row->emergency_phone;
                }
                if ( $row->emergency_mobile ) {
                    $data['telecom']['emergency']['phone'][] = $row->emergency_mobile;
                }
                if ( $row->emergency_email ) {
                    $data['telecom']['emergency']['email'][] = $row->emergency_email;
                }
                if ( $row->emergency_lien_parent ) {
                    $data['telecom']['emergency']['extension']['iHRISContactPointDetails']['code_relationShip'] = $row->emergency_lien_parent;
                    $data['telecom']['emergency']['extension']['iHRISContactPointDetails']['value_contactUse'] = "emergency";
                }
                if ( $row->emergency_address ) {
                    $data['address']['emergency']['mail'] = $row->emergency_address;
                    $data['address']['emergency']['extension']['iHRISAddressDetails']['code_relationShip'] = $row->emergency_lien_parent;
                    $data['address']['emergency']['extension']['iHRISAddressDetails']['value_addressUse'] = "emergency";
                }
                if($row->image)
                {
                    $data['photo']=array();
                    $creation = new DateTime($row->photo_date);
                    $url=$this->binaryServer.explode('|',$row->id)[1].$this->photoFileExtension;
                    $photo=array('url'=>$url,'creation'=>$creation->format('c'));
                    $data['photo'][]=$photo;

                   /*  $data['photo'] =array( array('url'=>'','creation'=>null));
                    $data['photo'] ['url']=$this->binaryServer.explode('|',$row->id)[1].$this->photoFileExtension;
                    $creation = new DateTime($row->photo_date);
                    $data['photo'] ['creation'] = $creation->format('c'); */

                }
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
            $stmt->closeCursor();
            unset( $stmt );
            return true;
        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
            http_response_code(500);
            return false;
        }
    }
    
    protected function getUpdatesMapping_Practitioner( &$top ) {
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
        $mainRequestPractitioner=null;
        $LinkedFormRequest=array();
        $listForms=array();
        $listMappingFields=array();
        $listExtensionMappingFields=array();
        $listConfigs=array();
        $this->buidMainRequest_Practitioner($mainRequestPractitioner,$LinkedFormRequest,$listForms,$listMappingFields,
        $listExtensionMappingFields,$listConfigs);
        
        /* $required_forms = array( "person", "demographic", "person_contact_personal", "person_contact_work","person_id",
        "person_contact_emergency","person_photo_passport","lien_parent","person_language","language","education","job_dip_training","degree",
        "job_nodip_training","engagement","sector","profession","specialisation","degree","type_recrutement","type_acte_engagement","status_admin",
        "hier","classe","echelon" ); */
        foreach( $listForms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }
        $params = array();
        /* $where = '';
       
        if ( $this->since ) {
            $where = "WHERE GREATEST(person.last_modified, IFNULL(demographic.last_modified,'0000-00-00 00:00:00'), IFNULL(personal.last_modified,'0000-00-00 00:00:00'), IFNULL(work.last_modified,'0000-00-00 00:00:00')) >= ?";
            $params = array( $this->since );
        } */
        if($listConfigs["limitLocationForm"]['status'] ==true)
        {
            $mainRequestPractitioner.=" where ".$listConfigs["limitLocationForm"]['form'].".".$listConfigs["limitLocationForm"]['fieldName']."='".$listConfigs["limitLocationForm"]['value']."'";
        }
        
        $qry = $mainRequestPractitioner;
        //echo "$mainRequestPractitioner";
        //print_r($LinkedFormRequest);
        //return array('result'=>"ok");
        
        //Extract Administrative status
        try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            $count = 0;
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            while ( $row = $stmt->fetch() ) {
                //subquery to extract person ids
                $data = array();
                $linkedData=array();
                $count++;
                if ( $this->useJSON ) {
                    $entry = array();
                    //$entry['fullURL'] = $this->getSiteBase() . "index.php/FHIR/Practitioner/" . $row->uuid;
                    $entry['resource'] = array( 'resourceType' => 'Practitioner' );
                    $entry['resource']['meta'] = array( 'profile' => array($listConfigs['profile']) );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    //$fullURL->setAttribute( 'value', $this->getSiteBase() . "index.php/FHIR/Practitioner/" . $row->uuid );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $practitioner = $this->doc->createElement( "Practitioner" );
                }
                
                foreach($row as $key=>$value)
                {
                    if($value && $value!="0000-00-00 00:00:00" && $value!="0000-00-00")
                    {
                        $data[$key]=$value;
                    }
                    
                }
                //Add now Linked forms query
                foreach($LinkedFormRequest as $keyJoinForm=>$joinFormRequest){
                    $subQuery="";
                    $paramSub=array($row->id);
                    //if($keyJoinForm =="person_id"){
                    if($keyJoinForm){
                        $subQuery=$joinFormRequest['mainFieldQuery']. $joinFormRequest['sourceFieldQuery'];
                        /*print_r($subQuery);
                        print_r("-------------------------------------"); */
                        $subStmt = $db->prepare( $subQuery );
                        $subStmt->execute($paramSub);
                        $id_count=0;
                        $linkedData[$keyJoinForm]=array();
                        while ( $subRow = $subStmt->fetch() ) {
                            $linkedData[$keyJoinForm][$id_count]=array();
                            foreach($subRow as $key=>$value)
                            {
                                if($value && $value!="0000-00-00 00:00:00" && $value!="0000-00-00")
                                {
                                    $linkedData[$keyJoinForm][$id_count][$key]=$value;
                                } 
                            }
                            $id_count++;
                        } 
                    }
                }

                if ( $this->useJSON ) {
                    $this->createMapping_Practitioner( $data,$linkedData, $entry['resource'],$listMappingFields,
                    $listExtensionMappingFields,$listConfigs );
                    $top['entry'][] = $entry;
                } else {
                    $this->createMapping_Practitioner( $data,$linkedData,$practitioner,$listMappingFields,
                    $listExtensionMappingFields,$listConfigs );

                    $resource->appendChild($practitioner);
                    $entry->appendChild( $resource );
                    $top->appendChild( $entry );
                }
            //Limit the result to only one record
            //break;
            }
            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
            $stmt->closeCursor();
            unset( $stmt );
            return true;
        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
            http_response_code(500);
            return false;
        }
    }
    protected function buidMainRequest_Practitioner(&$mainRequestPractitioner,&$LinkedFormRequest,&$listMainForms,
    &$listMappingFields,&$listExtensionMappingField,&$listConfigs)
    {
        try{
            $dir = getcwd();
            chdir("../modules/mCSDUpdateSupplier");
            //$strJsonFileContents = file_get_contents("./mapping/practitioner.json");
            $strJsonFileContents = file_get_contents("./mapping/practitionerbylocation.json");
            $array = json_decode($strJsonFileContents, true);
            $mainFieldQuery="";
            $sourceFieldQuery="";
            $listMainForms=array();
            //print_r($array);
            $this->extractMainField($array,$mainFieldQuery,$sourceFieldQuery,$listMainForms);
            if ( array_key_exists( 'fieldsMapping', $array ) ) {
                foreach( $array['fieldsMapping'] as $key => $fieldsMapping) {
                    $this->extractJoinFormFields($fieldsMapping,$mainFieldQuery,$sourceFieldQuery,$listMainForms);
                }
            }
            //$this->extractJoinFormFields($array['fieldsMapping'],$mainFieldQuery,$sourceFieldQuery);
            //print_r($mainFieldQuery.$sourceFieldQuery);
            $mainRequestPractitioner=$mainFieldQuery.$sourceFieldQuery;
            $whereFilter=$this->buildWhereFilter($listMainForms);
            if($whereFilter!=null)
            {
                $mainRequestPractitioner="SELECT DATE_FORMAT(".$whereFilter.",'%Y-%m-%d %T') AS lastupdated ".$mainRequestPractitioner;
                //$mainRequestPractitioner.=" WHERE $whereFilter >= ? ORDER BY lastupdated ASC";
            }
            else
            {
                $mainRequestPractitioner="SELECT $mainRequestPractitioner";
            }
            $linkedFieldQuery=array();
            $this->extractLinkedFields($array,$linkedFieldQuery,$listMainForms);
            $LinkedFormRequest=$linkedFieldQuery;
            $listForms=$listMainForms;
            $this->getAllMappingFields($array,$listMappingFields,$listExtensionMappingField);
            $this->getConfig($array,$listConfigs);
            //print_r($LinkedFormRequest);
            //return "request";
        }
        catch(Exception $e)
        {
            echo "Error :".$e->getMessage();
            //return null;
        }
    }
    protected function buidMainRequest_PractitionerRole(&$mainRequestPractitionerRole,&$LinkedFormRequest,&$listMainForms,
    &$listMappingFields,&$listExtensionMappingField,&$listConfigs)
    {
        try{
            $dir = getcwd();
            chdir("../modules/mCSDUpdateSupplier");
            $strJsonFileContents = file_get_contents("./mapping/practitionerrolebylocation.json");
            $array = json_decode($strJsonFileContents, true);
            $mainFieldQuery=array();
            $sourceFieldQuery=array();
            $listMainForms=array();
            //print_r($array);
            $this->extractMainFieldCollection($array,$mainFieldQuery,$sourceFieldQuery,$listMainForms);
            //print_r($mainFieldQuery);
            if ( array_key_exists( 'fieldsMapping', $array ) ) {
                foreach( $array['fieldsMapping'] as $key => $fieldsMapping) {
                    $this->extractJoinFormFieldsCollection($fieldsMapping,$fieldsMapping['iHRISForm'],$mainFieldQuery,$sourceFieldQuery,$listMainForms);
                }
            }
            foreach($mainFieldQuery as $keyQuery=>$queryText){
                $mainRequestPractitionerRole[]="SELECT DATE_FORMAT($keyQuery.last_modified,'%Y-%m-%d %T') as lastupdated ". $queryText.$sourceFieldQuery[$keyQuery];
            }
            //$mainRequestPractitionerRole=$mainFieldQuery.$sourceFieldQuery;
            //$whereFilter=$this->buildWhereFilter($listMainForms);
            $whereFilter=null;
            $linkedFieldQuery=array();
            $this->extractLinkedFields($array,$linkedFieldQuery,$listMainForms);
            $LinkedFormRequest=$linkedFieldQuery;
            $listForms=$listMainForms;
            $this->getAllMappingFields($array,$listMappingFields,$listExtensionMappingField);
            $this->getConfig($array,$listConfigs);
            //print_r($LinkedFormRequest);
            //return "request";
        }
        catch(Exception $e)
        {
            echo "Error :".$e->getMessage();
            //return null;
        }
    }
    protected function getConfigData(&$listConfigs)
    {
        try
        {
            $dir = getcwd();
            chdir("../modules/mCSDUpdateSupplier");
            $strJsonFileContents = file_get_contents("./mapping/location.json");
            $array = json_decode($strJsonFileContents, true);
            $this->getConfig($array,$listConfigs);
        }
        catch(Exception $e)
        {
            echo "Error :".$e->getMessage();
        }
    }
    protected function buidMainRequest_Resources($resourceType,&$mainRequestPractitionerRole,&$LinkedFormRequest,&$listMainForms,
    &$listMappingFields,&$listExtensionMappingField,&$listConfigs,&$listResourceMeta)
    {
        try{
            $dir = getcwd();
            chdir("../modules/mCSDUpdateSupplier");
            $strJsonFileContents="";
            if($resourceType=="ValueSet")
            {
                $strJsonFileContents = file_get_contents("./mapping/valueset.json");
            }
            else
            {
                return;
            }
            //$strJsonFileContents = file_get_contents("./mapping/practitionerrolebylocation.json");
            $array = json_decode($strJsonFileContents, true);
            $mainFieldQuery=array();
            $sourceFieldQuery=array();
            $listMainForms=array();
            //print_r($array);
            $this->extractMainFieldCollection($array,$mainFieldQuery,$sourceFieldQuery,$listMainForms);
            //print_r($mainFieldQuery);
            if ( array_key_exists( 'fieldsMapping', $array ) ) {
                foreach( $array['fieldsMapping'] as $key => $fieldsMapping) {
                    $this->extractJoinFormFieldsCollection($fieldsMapping,$fieldsMapping['iHRISForm'],$mainFieldQuery,$sourceFieldQuery,$listMainForms);
                    $listResourceMeta[]=$fieldsMapping['meta'];
                }
            }
            foreach($mainFieldQuery as $keyQuery=>$queryText){
                $mainRequestPractitionerRole[]="SELECT DATE_FORMAT($keyQuery.last_modified,'%Y-%m-%d %T') as lastupdated ". $queryText.$sourceFieldQuery[$keyQuery];
            }
            //$mainRequestPractitionerRole=$mainFieldQuery.$sourceFieldQuery;
            //$whereFilter=$this->buildWhereFilter($listMainForms);
            $whereFilter=null;
            $linkedFieldQuery=array();
            $this->extractLinkedFields($array,$linkedFieldQuery,$listMainForms);
            $LinkedFormRequest=$linkedFieldQuery;
            $listForms=$listMainForms;
            $this->getAllMappingFields($array,$listMappingFields,$listExtensionMappingField);
            $this->getConfig($array,$listConfigs);
            //print_r($LinkedFormRequest);
            //return "request";
        }
        catch(Exception $e)
        {
            echo "Error :".$e->getMessage();
            //return null;
        }
    }
    protected function buidMainRequest_Organization(&$mainRequestOrganization,&$LinkedFormRequest,&$listMainForms,
    &$listMappingFields,&$listExtensionMappingField,&$listConfigs)
    {
        try{
            $dir = getcwd();
            chdir("../modules/mCSDUpdateSupplier");
            $strJsonFileContents = file_get_contents("./mapping/organization.json");
            $array = json_decode($strJsonFileContents, true);
            $mainFieldQuery=array();
            $sourceFieldQuery=array();
            $listMainForms=array();
            $this->getConfig($array,$listConfigs);
            //print_r($array);
            $this->extractDistinctMainFieldCollection($array,$listConfigs,$mainFieldQuery,$sourceFieldQuery,$listMainForms);
            //print_r($mainFieldQuery);
            if ( array_key_exists( 'fieldsMapping', $array ) ) {
                foreach( $array['fieldsMapping'] as $key => $fieldsMapping) {
                    $this->extractJoinFormFieldsCollection($fieldsMapping,$fieldsMapping['iHRISForm'],$mainFieldQuery,$sourceFieldQuery,$listMainForms);
                }
            }
            foreach($mainFieldQuery as $keyQuery=>$queryText){
                $mainRequestOrganization[]="SELECT  ". $queryText.$sourceFieldQuery[$keyQuery];
            }
            //$mainRequestPractitionerRole=$mainFieldQuery.$sourceFieldQuery;
            //$whereFilter=$this->buildWhereFilter($listMainForms);
            $whereFilter=null;
            $linkedFieldQuery=array();
            $this->extractLinkedFields($array,$linkedFieldQuery,$listMainForms);
            $LinkedFormRequest=$linkedFieldQuery;
            $listForms=$listMainForms;
            $this->getAllMappingFields($array,$listMappingFields,$listExtensionMappingField);
            //print_r($LinkedFormRequest);
            //return "request";
        }
        catch(Exception $e)
        {
            echo "Error :".$e->getMessage();
            //return null;
        }
    }
    protected function getAllMappingFields($array,&$listMappingFields,&$listExtensionMappingField)
    {
        try{
            
            $this->extractStructureDefinitionFields($array,$listMappingFields,$listExtensionMappingField);
            if ( array_key_exists( 'fieldsMapping', $array ) ) {
                foreach( $array['fieldsMapping'] as $key => $fieldsMapping) {
                    $this->extractStructureDefinitionJoinFormFields($fieldsMapping,$listMappingFields,$listExtensionMappingField);
                }
            }
            $this->extractStructureDefinitionLinkedFields($array,$listMappingFields,$listExtensionMappingField);
        }
        catch(Exception $e)
        {
            echo "Error :".$e->getMessage();
            //return null;
        }

    }
    protected function getConfig($array,&$configs)
    {
        try{
            
            if(array_key_exists( 'configs', $array ) ) {
                 $configs=$array['configs'];
            }
        }
        catch(Exception $e)
        {
            echo "Error :".$e->getMessage();
            //return null;
        }
    }
    protected function extractMainField($data,&$mainFieldQuery,&$sourceFieldQuery,&$listMainForms)
    {
        if ( array_key_exists( 'fieldsMapping', $data ) ) {
            //echo "\ncontains fieldsMapping\n";
            foreach( $data['fieldsMapping'] as $key => $fieldsMapping) {
                if(array_key_exists('iHRISForm',$fieldsMapping)){
                    $sourceForm=$fieldsMapping['iHRISForm'];
                    array_push($listMainForms,$sourceForm);
                    if(!empty($fieldsMapping['iHRISField']))
                    {
                        $indexCount=0;
                        foreach($fieldsMapping['iHRISField'] as $keyIndex=>$mappingElement)
                        {
                            if($mappingElement['active']){
                                //if($indexCount==0)
                                if(strpos($mainFieldQuery,',') || $mainFieldQuery!="SELECT ")
                                {
                                    
                                    $mainFieldQuery.=",".$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                }
                                else{
                                    $mainFieldQuery.=$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                }
                                $indexCount++;
                            }
                        }
                        $sourceFieldQuery.=" from hippo_".$sourceForm." as ".$sourceForm;
                    }
                    $indexCount=0;
                    if(array_key_exists('extension',$fieldsMapping))
                    {
                        foreach($fieldsMapping['extension'] as $indexExtention=> $extensionElement)
                        {
                            foreach($extensionElement['iHRISField'] as $keyIndex=>$mappingElement)
                            {
                                if($mappingElement['active']){
                                    //if($indexCount==0)
                                    if(strpos($mainFieldQuery,',') || $mainFieldQuery!="SELECT ")
                                    {
                                        $mainFieldQuery.=",".$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                    }
                                    else{
                                        $mainFieldQuery.=$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                    }
                                    $indexCount++;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    protected function extractMainFieldCollection($data,&$mainFieldQuery,&$sourceFieldQuery,&$listMainForms)
    {
        if ( array_key_exists( 'fieldsMapping', $data ) ) {
            //echo "\ncontains fieldsMapping\n";
            foreach( $data['fieldsMapping'] as $key => $fieldsMapping) {
                if(array_key_exists('iHRISForm',$fieldsMapping)){
                    $sourceForm=$fieldsMapping['iHRISForm'];
                    array_push($listMainForms,$sourceForm);
                    if(!empty($fieldsMapping['iHRISField']))
                    {
                        $indexCount=0;
                        foreach($fieldsMapping['iHRISField'] as $keyIndex=>$mappingElement)
                        {
                            if($mappingElement['active']){
                                //if($indexCount==0)
                                if(strpos($mainFieldQuery[$fieldsMapping['iHRISForm']],',') || $mainFieldQuery[$fieldsMapping['iHRISForm']]!="SELECT ")
                                {
                                    
                                    $mainFieldQuery[$fieldsMapping['iHRISForm']].=",".$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                }
                                else{
                                    $mainFieldQuery[$fieldsMapping['iHRISForm']].=$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                }
                                $indexCount++;
                            }
                        }
                        $sourceFieldQuery[$fieldsMapping['iHRISForm']].=" from hippo_".$sourceForm." as ".$sourceForm;
                    }
                    $indexCount=0;
                    if(array_key_exists('extension',$fieldsMapping))
                    {
                        foreach($fieldsMapping['extension'] as $indexExtention=> $extensionElement)
                        {
                            foreach($extensionElement['iHRISField'] as $keyIndex=>$mappingElement)
                            {
                                if($mappingElement['active']){
                                    //if($indexCount==0)
                                    if(strpos($mainFieldQuery[$fieldsMapping['iHRISForm']],',') || $mainFieldQuery[$fieldsMapping['iHRISForm']]!="SELECT ")
                                    {
                                        $mainFieldQuery[$fieldsMapping['iHRISForm']].=",".$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                    }
                                    else{
                                        $mainFieldQuery[$fieldsMapping['iHRISForm']].=$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                    }
                                    $indexCount++;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    protected function extractDistinctMainFieldCollection($data,$configs,&$mainFieldQuery,&$sourceFieldQuery,&$listMainForms)
    {
        if ( array_key_exists( 'fieldsMapping', $data ) ) {
            //echo "\ncontains fieldsMapping\n";
            foreach( $data['fieldsMapping'] as $key => $fieldsMapping) {
                if(array_key_exists('iHRISForm',$fieldsMapping)){
                    $sourceForm=$fieldsMapping['iHRISForm'];
                    array_push($listMainForms,$sourceForm);
                    if(!empty($fieldsMapping['iHRISField']))
                    {
                        $indexCount=0;
                        foreach($fieldsMapping['iHRISField'] as $keyIndex=>$mappingElement)
                        {
                            if($mappingElement['active']){
                                //if($indexCount==0)
                                if(in_array($mappingElement['iHRISFieldAlias'],
                                    explode(',',$configs['setDistinctSelectionFields']['iHRISFieldAlias'])))
                                {
                                    $mainFieldQuery[$fieldsMapping['iHRISForm']] ="DISTINCT (LOWER(".$sourceForm.".".$mappingElement['fieldId'].")) AS ".$mappingElement['iHRISFieldAlias'].",".$mainFieldQuery[$fieldsMapping['iHRISForm']];
                                }
                                else
                                {
                                    
                                    $mainFieldQuery[$fieldsMapping['iHRISForm']].="".$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'].",";
                                
                                }
                                
                                $indexCount++;
                            }
                        }
                        $requestComponents=explode(',',$mainFieldQuery[$fieldsMapping['iHRISForm']]);
                        $reqCounter=0;
                        $reqText="";
                        foreach($requestComponents as $keyReq=>$component)
                        {
                            if($reqCounter==0 && $component!="")
                            {
                                $reqText.=$component;
                            }
                            elseif($reqCounter>0 && $component!=""){
                                $reqText.=",".$component;
                            }
                            $reqCounter++;
                        }
                        $mainFieldQuery[$fieldsMapping['iHRISForm']]=$reqText;
                    
                        $sourceFieldQuery[$fieldsMapping['iHRISForm']].=" from hippo_".$sourceForm." as ".$sourceForm;
                    }
                    $indexCount=0;
                    if(array_key_exists('extension',$fieldsMapping))
                    {
                        foreach($fieldsMapping['extension'] as $indexExtention=> $extensionElement)
                        {
                            foreach($extensionElement['iHRISField'] as $keyIndex=>$mappingElement)
                            {
                                if($mappingElement['active']){
                                    //if($indexCount==0)
                                    if(strpos($mainFieldQuery[$fieldsMapping['iHRISForm']],',') || $mainFieldQuery[$fieldsMapping['iHRISForm']]!="SELECT ")
                                    {
                                        $mainFieldQuery[$fieldsMapping['iHRISForm']].=",".$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                    }
                                    else{
                                        $mainFieldQuery[$fieldsMapping['iHRISForm']].=$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                    }
                                    $indexCount++;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    protected function extractStructureDefinitionFields($data,&$listFieldDefinition,&$listExtensionField)
    {
        if ( array_key_exists( 'fieldsMapping', $data ) ) {
            foreach( $data['fieldsMapping'] as $key => $fieldsMapping) {
                if(!empty($fieldsMapping['iHRISField']))
                {
                    foreach($fieldsMapping['iHRISField'] as $keyIndex=>$fieldElement)
                    {
                        if($fieldElement['active'])
                        {
                            array_push($listFieldDefinition,$fieldElement);
                        }
                        
                    }
                }
                if(array_key_exists('extension',$fieldsMapping))
                {
                    foreach($fieldsMapping['extension'] as $indexExtention=> $extensionElement)
                    {
                        /* echo "\nInitial extension \n";
                        print_r($listExtensionField); */
                        $keyFoundExtension=-1;
                        $this->getExtensionIndexByName($listExtensionField,$extensionElement['extensionName'],$keyFoundExtension);
                        if($keyFoundExtension!=-1)
                        {
                            foreach($extensionElement['iHRISField'] as $keyIndex=>$keyElement){
                                if($keyElement['active']){
                                    array_push($listExtensionField[$keyFoundExtension]['fieldList'],$keyElement);
                                }
                                
                            }
                        }
                        else{
                            $extensionField=array('extensionName'=>$extensionElement['extensionName'],
                            'fhirBaseElement'=>$extensionElement['fhirBaseElement'],'type'=>$extensionElement['type'],
                            'appliedFields'=>$extensionElement['appliedFields'],'origin'=>"main",'fieldList'=>array(),
                            'appliedType'=>$extensionElement['appliedType']);
                            foreach($extensionElement['iHRISField'] as $keyIndex=>$fieldElement)
                            {
                                //array_push($listFieldDefinition,$fieldElement);
                                if($fieldElement['active'])
                                {
                                    array_push($extensionField['fieldList'],$fieldElement);
                                }
                            }
                            //if()
                            array_push($listExtensionField,$extensionField);
                            }
                        
                    }
                }
            }
        }
    }
    protected function getExtensionIndexByName($listExtensionField,$name,&$keyExtension)
    {
        foreach($listExtensionField as $key=>$extensionField)
        {
            
            if($extensionField['extensionName']==$name)
            {
                $keyExtension=$key;
                /* echo "\n----------------- extension found on  $keyExtension----------------------------\n";
                print_r($extensionField['extensionName']);
                echo "\n------end Explored extension -------\n"; */
                
            break;
            }
        }
    }
    protected function extractJoinFormFields($data,&$mainFieldQuery,&$sourceFieldQuery,&$listMainForms)
    {
        //echo "#######################################";
        //print_r($data);
        if(array_key_exists('iHRISJoinedForms',$data))
        {
            
            foreach($data['iHRISJoinedForms'] as $keyIndex=>$mappingElement)
            {
                $joinedForm=$mappingElement['iHRISForm'];
                $joinFormAlias=$mappingElement['iHRISFormAlias'];
                $joiningConstraint=$mappingElement['joiningConstraint'];
                array_push($listMainForms,$joinedForm);
                $parentForm=$data['iHRISForm'];
                $joinedFieldSource=$mappingElement['iHRISFormJoinFieldSource'];
                $joinedFieldDestination=$mappingElement['iHRISFormJoinFieldDestination'];
                if(array_key_exists('iHRISField',$mappingElement)){
                    foreach($mappingElement['iHRISField'] as $keyField=>$keyElement){
                        if($keyElement['active'])
                        {
                            
                            if(strpos($mainFieldQuery,',') || $mainFieldQuery!="SELECT ")
                            {
                                $mainFieldQuery.=",".$joinFormAlias.".".$keyElement['fieldId']." AS ".$keyElement['iHRISFieldAlias'];
                            }
                            else
                            {
                                $mainFieldQuery.=$joinFormAlias.".".$keyElement['fieldId']." AS ".$keyElement['iHRISFieldAlias'];
                            }
                        }
                    }
                }
                if(array_key_exists('extension',$mappingElement)){
                    foreach($mappingElement['extension'] as  $indexExtention=> $extensionElement)
                    {
                        foreach($extensionElement['iHRISField'] as $keyField=>$keyElement){
                            if($keyElement['active']){
                                if(strpos($mainFieldQuery,',') || $mainFieldQuery!="SELECT ")
                                {
                                    $mainFieldQuery.=",".$joinFormAlias.".".$keyElement['fieldId']." AS ".$keyElement['iHRISFieldAlias'];
                                }
                                else{
                                    $mainFieldQuery.=$joinFormAlias.".".$keyElement['fieldId']." AS ".$keyElement['iHRISFieldAlias'];
                                }
                            }
                        }
                    }
                }
                if($joiningConstraint)
                {
                    $sourceFieldQuery.=" JOIN hippo_".$joinedForm." AS ".$joinFormAlias." ON $joinFormAlias.$joinedFieldDestination = $parentForm.$joinedFieldSource";
                }
                else{
                    $sourceFieldQuery.=" LEFT JOIN hippo_".$joinedForm." AS ".$joinFormAlias." ON $joinFormAlias.$joinedFieldDestination = $parentForm.$joinedFieldSource";
                }
                
                
                if(array_key_exists('iHRISJoinedForms',$mappingElement)){
                    $this->extractJoinFormFields($mappingElement,$mainFieldQuery,$sourceFieldQuery,$listMainForms);
                }
                
            }
        }
       
    }
    protected function extractJoinFormFieldsCollection($data,$rootForm,&$mainFieldQuery,&$sourceFieldQuery,&$listMainForms)
    {
        //echo "#######################################";
        //print_r($data);
        if(array_key_exists('iHRISJoinedForms',$data))
        {
            
            foreach($data['iHRISJoinedForms'] as $keyIndex=>$mappingElement)
            {
                $joinedForm=$mappingElement['iHRISForm'];
                $joinFormAlias=$mappingElement['iHRISFormAlias'];
                $joiningConstraint=$mappingElement['joiningConstraint'];
                array_push($listMainForms,$joinedForm);
                $parentForm=$data['iHRISForm'];
                $joinedFieldSource=$mappingElement['iHRISFormJoinFieldSource'];
                $joinedFieldDestination=$mappingElement['iHRISFormJoinFieldDestination'];
                if(array_key_exists('iHRISField',$mappingElement)){
                    foreach($mappingElement['iHRISField'] as $keyField=>$keyElement){
                        if($keyElement['active'])
                        {
                            
                            if(strpos($mainFieldQuery[$rootForm],',') || $mainFieldQuery[$rootForm]!="SELECT ")
                            {
                                $mainFieldQuery[$rootForm].=",".$joinFormAlias.".".$keyElement['fieldId']." AS ".$keyElement['iHRISFieldAlias'];
                            }
                            else
                            {
                                $mainFieldQuery[$rootForm].=$joinFormAlias.".".$keyElement['fieldId']." AS ".$keyElement['iHRISFieldAlias'];
                            }
                        }
                    }
                }
                if(array_key_exists('extension',$mappingElement)){
                    foreach($mappingElement['extension'] as  $indexExtention=> $extensionElement)
                    {
                        foreach($extensionElement['iHRISField'] as $keyField=>$keyElement){
                            if($keyElement['active']){
                                if(strpos($mainFieldQuery[$rootForm],',') || $mainFieldQuery[$rootForm]!="SELECT ")
                                {
                                    $mainFieldQuery[$rootForm].=",".$joinFormAlias.".".$keyElement['fieldId']." AS ".$keyElement['iHRISFieldAlias'];
                                }
                                else{
                                    $mainFieldQuery[$rootForm].=$joinFormAlias.".".$keyElement['fieldId']." AS ".$keyElement['iHRISFieldAlias'];
                                }
                            }
                        }
                    }
                }
                if($joiningConstraint)
                {
                    $sourceFieldQuery[$rootForm].=" JOIN hippo_".$joinedForm." AS ".$joinFormAlias." ON $joinFormAlias.$joinedFieldDestination = $parentForm.$joinedFieldSource";
                }
                else{
                    $sourceFieldQuery[$rootForm].=" LEFT JOIN hippo_".$joinedForm." AS ".$joinFormAlias." ON $joinFormAlias.$joinedFieldDestination = $parentForm.$joinedFieldSource";
                }
                //$sourceFieldQuery[$rootForm].=" LEFT JOIN hippo_".$joinedForm." AS ".$joinFormAlias." ON $joinFormAlias.$joinedFieldDestination = $parentForm.$joinedFieldSource";
                
                if(array_key_exists('iHRISJoinedForms',$mappingElement)){
                    $this->extractJoinFormFieldsCollection($mappingElement,$rootForm,$mainFieldQuery,$sourceFieldQuery,$listMainForms);
                }
                
            }
        }
       
    }
    protected function extractStructureDefinitionJoinFormFields($data,&$listFieldDefinition,&$listExtensionField)
    {
        if(array_key_exists('iHRISJoinedForms',$data))
        {
            foreach($data['iHRISJoinedForms'] as $keyIndex=>$mappingElement)
            {
                if(array_key_exists('iHRISField',$mappingElement)){
                    foreach($mappingElement['iHRISField'] as $keyField=>$keyElement){
                        if($keyElement['active']){
                            array_push($listFieldDefinition,$keyElement);
                        } 
                    }
                }
                if(array_key_exists('extension',$mappingElement)){
                    foreach($mappingElement['extension'] as  $indexExtention=> $extensionElement)
                    {
                        $keyFoundExtension=-1;
                        $this->getExtensionIndexByName($listExtensionField,$extensionElement['extensionName'],$keyFoundExtension);
                        
                        if($keyFoundExtension!=-1)
                        {
                            foreach($extensionElement['iHRISField'] as $keyField=>$keyElement){
                                if($keyElement['active']){
                                    array_push($listExtensionField[$keyFoundExtension]['fieldList'],$keyElement);
                                }
                                
                            }
                        }
                        else{
                            $extensionField=array('extensionName'=>$extensionElement['extensionName'],
                            'fhirBaseElement'=>$extensionElement['fhirBaseElement'],'type'=>$extensionElement['type'],
                            'appliedFields'=>$extensionElement['appliedFields'],'origin'=>"main",'fieldList'=>array(),
                            'appliedType'=>$extensionElement['appliedType']);
                            foreach($extensionElement['iHRISField'] as $keyField=>$keyElement){
                                //array_push($listFieldDefinition,$keyElement);
                                if($keyElement['active']){
                                    array_push($extensionField['fieldList'],$keyElement);
                                }
                                
                            }
                            array_push($listExtensionField,$extensionField);
                                
                        }
                        
                        
                    }
                }
                if(array_key_exists('iHRISJoinedForms',$mappingElement)){
                    $this->extractStructureDefinitionJoinFormFields($mappingElement,$listFieldDefinition,$listExtensionField);
                }
            }
        }
    }
    protected function buildWhereFilter($listMainForms)
    {
        $whereFilter=null;
        $count=0;
        foreach($listMainForms as $key=>$formName)
        {
            if($count==0)
            {
                $whereFilter=" GREATEST( $formName.last_modified";
            }
            else{
                $whereFilter.=",IFNULL($formName.last_modified,'0000-00-00 00:00:00')";
            }
            $count++;
        }
        if( $whereFilter!=null)
        {
            $whereFilter.=")";
        }
        return $whereFilter;
    }
    protected function extractLinkedFields($data,&$linkedFiedsQuery,&$listMainForms)
    {
        if ( array_key_exists( 'fieldsLinked', $data ) ) {
            foreach( $data['fieldsLinked'] as $key => $fieldsMapping) {
                $sourceForm="";
                $mainFieldQuery="SELECT ";
                $sourceFieldQuery="";
                if(array_key_exists('iHRISForm',$fieldsMapping)){
                    $sourceForm=$fieldsMapping['iHRISForm'];
                    array_push($listMainForms,$sourceForm);
                    $linkedFiedsQuery[$sourceForm]=array('mainFieldQuery'=>"",'sourceFieldQuery'=>"");
                   
                    if(!empty($fieldsMapping['iHRISField']))
                    {
                        $indexCount=0;
                        foreach($fieldsMapping['iHRISField'] as $keyIndex=>$mappingElement)
                        {
                            if($mappingElement['active']){
                                if($indexCount==0)
                                {
                                    $mainFieldQuery.=$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                }
                                else{
                                    $mainFieldQuery.=",".$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                }
                                $indexCount++;
                            }
                            
                            
                        }
                        //$sourceFieldQuery.=" from hippo_".$sourceForm." as ".$sourceForm;
                    }
                    $sourceFieldQuery.=" from hippo_".$sourceForm." as ".$sourceForm;
                    $indexCount=0;
                    if(array_key_exists('extension',$fieldsMapping))
                    {
                        foreach($fieldsMapping['extension'] as $indexExtention=> $extensionElement)
                        {
                            foreach($extensionElement['iHRISField'] as $keyIndex=>$mappingElement)
                            {
                                if($mappingElement['active']){
                                    if($indexCount==0)
                                    {
                                        $mainFieldQuery.=$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                    }
                                    else{
                                        $mainFieldQuery.=",".$sourceForm.".".$mappingElement['fieldId']." AS ".$mappingElement['iHRISFieldAlias'];
                                    }
                                    $indexCount++;
                                }
                                
                            }
                        }
                    }
                }
                $this->extractJoinFormFields($fieldsMapping,$mainFieldQuery,$sourceFieldQuery,$listMainForms);
                $linkedFiedsQuery[$sourceForm]['mainFieldQuery']= $mainFieldQuery;
                $linkedFiedsQuery[$sourceForm]['sourceFieldQuery']=$sourceFieldQuery."  where $sourceForm".".parent=?";
            }
        }
    }

    protected function extractStructureDefinitionLinkedFields($data,&$listFieldDefinition,&$listExtensionField)
    {
        if ( array_key_exists( 'fieldsLinked', $data ) ) {
            foreach( $data['fieldsLinked'] as $key => $fieldsMapping) {
                if(array_key_exists('iHRISForm',$fieldsMapping)){
                    if(!empty($fieldsMapping['iHRISField']))
                    {
                        foreach($fieldsMapping['iHRISField'] as $keyIndex=>$mappingElement)
                        {
                            array_push($listFieldDefinition,$mappingElement);
                        }
                    }
                }
                if(array_key_exists('extension',$fieldsMapping))
                {
                    foreach($fieldsMapping['extension'] as $indexExtention=> $extensionElement)
                    {
                        $extensionField=array('extensionName'=>$extensionElement['extensionName'],
                        'fhirBaseElement'=>$extensionElement['fhirBaseElement'],'type'=>$extensionElement['type'],
                        'appliedFields'=>$extensionElement['appliedFields'],'origin'=>"linked",'fieldList'=>array());
                        foreach($extensionElement['iHRISField'] as $keyIndex=>$mappingElement)
                        {
                            //array_push($listFieldDefinition,$mappingElement);
                            array_push($extensionField['fieldList'],$mappingElement);
                        }
                        array_push($listExtensionField,$extensionField);
                    }
                }
                $this->extractStructureDefinitionJoinFormFields($fieldsMapping,$listFieldDefinition,$listExtensionField);
            }
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
        //echo "\nTracing: Enter create practitioner!\n";
        //var_dump($data['identification']);
        $top["extension"]=array();
        $maritalStatusDict=array('marital_status|1'=>'Never Married','marital_status|2'=>'Married','marital_status|3'=>'Divorced',
            'marital_status|4'=>'Widowed');
        $languageProficiencyDict=array('language_proficiency|elementary'=>'Elémentaire',
        'language_proficiency|limited'=>'Moyen','language_proficiency|professional'=>'Courant');
        if ( $this-> useJSON ) {    
            $top['id'] = $data['uuid'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }
            $globalIdSystem=null;
            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $globalIdSystem= $data['id_system'];
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }
            //$element = array();
            if ( array_key_exists( 'identification', $data ) ) {
                //$top['identification'] = array();
                foreach( $data['identification'] as $ids => $id) {
                    $globalIdSystem=$globalIdSystem!=null?$globalIdSystem:"http://ihris.org/";
                    $identifierItem= array( 'system' =>$globalIdSystem."id_type/".$id['id_type'], 
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
            if( array_key_exists( 'maritalStatus', $data ) ) {
                //echo "--------Practition extension ----\n";
                //var_dump($top["extension"]);
                if($top["extension"]==null)
                {
                    $top["extension"]=array();
                }
                foreach( $maritalStatusDict as $iHRISKey => $code) {
                    if($iHRISKey == $data['maritalStatus'] ){
                        //echo "matched $iHRISKey : ".$code."\n";
                        array_push( $top["extension"],array('url'=>"http://ihris.org/fhir/StructureDefinition/iHRISPractitionerMaritalStatus",
                        "valueCoding"=>array("code"=>$code)));
                    }
                }
            }
            if( array_key_exists( 'dependents', $data ) ) {
                if($top["extension"]==null)
                {
                    $top["extension"]=[];
                }
                array_push( $top["extension"],array('url'=>"http://ihris.org/fhir/StructureDefinition/iHRISPractitionerDependents",'valueInteger'=>$data['dependents']));
            }
            if( array_key_exists( 'nationality', $data ) ) {
                if($top["extension"]==null)
                {
                    $top["extension"]=[];
                }
                array_push( $top["extension"],array('url'=>"http://ihris.org/fhir/StructureDefinition/iHRISPractitionerNationality",
                'valueCoding'=>array('code'=>$data['nationality'])));
            }
            if( array_key_exists( 'parent', $data ) ) {
                if(!empty($data['parent']))
                {
                    $parentNamesExtension=array('url'=>"http://ihris.org/fhir/StructureDefinition/iHRISPractitionerIdentificationDetails",
                    'extension'=>array());
                    foreach ( $data['parent'] as $key => $parentNames ) {
                        $parentNamesExtension['extension'][]=array('url'=>$key,'valueString'=>$parentNames);
                    }
                    array_push($top["extension"],$parentNamesExtension);
                }
            }
            if ( array_key_exists( 'birthDate', $data ) ) {
                $top['birthDate'] = $data['birthDate'];
            }
            if ( array_key_exists( 'photo', $data ) ) {
                $top['photo']=array();
                foreach($data['photo'] as $key=>$photoElement){
                    $photo=array('url'=>'','creation'=>null);
                    if($photoElement['url'])
                    {
                        $photo['url']=$photoElement['url'];
                    }
                    if($photoElement['creation'])
                    {
                        $photo['creation']=$photoElement['creation'];
                    }
                    $top['photo'][]=$photo;
                }
                /* if($data['photo']['url'])
                {
                    $top['photo']['url'] =$data['photo']['url'];
                }
                if($data['photo']['creation'])
                {
                    $top['photo']['creation'] =$data['photo']['creation'];
                } */
            }

            if ( array_key_exists( 'telecom', $data ) ) {
                $top['telecom'] = array();
                foreach ( $data['telecom'] as $use => $telecom ) {
                    
                    foreach ( $telecom as $system => $contacts ) {
                        if(is_array( $contacts)){
                            foreach ( $contacts as $key=>$contact ) {
                                if($use=="emergency")
                                {
                                    /* $top['telecom'][] = array( 'resourceType' => 'ContactPoint', 'system' => $system, 'use' => 'temp','value' => $contact,
                                        'extension'=>array(array('url'=>'http://ihris.org/fhir/StructureDefinition/iHRISContactPointDetails',
                                        'extension'=>array(array('url'=>"contactUse",'valueString'=>$use), array('url'=>"kindShip",'valueString'=>$telecom['kindship'])
                                        )))
                                        ); */
                                       
                                        if(array_key_exists( 'extension', $telecom ) && $system !=="extension" )
                                        {
                                            $contactPointElement=array('resourceType' => 'ContactPoint', 'system' => $system, 'use' => 'temp','value' => $contact,
                                            'extension'=>array());
                                            foreach ( $telecom['extension'] as $extensionType => $extensionElements ) {
                                                $extensionDetails=array('url'=>'http://ihris.org/fhir/StructureDefinition/'.$extensionType,
                                                    'extension'=>array());
                                                foreach($extensionElements as $keyUrl=>$fieldValue)
                                                {
                                                    if(strpos($keyUrl,"date_")!==false)
                                                    {
                                                        $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],'valueDate'=>$fieldValue);
                                                    }
                                                    elseif(strpos($keyUrl,"datetime_")!==false)
                                                    {
                                                        $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],'valueDateTime'=>$fieldValue);
                                                    }
                                                    elseif(strpos($keyUrl,"value_")!==false)
                                                    {
                                                        $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],'valueString'=>$fieldValue);
                                                    }
                                                    elseif(strpos($keyUrl,"code_")!==false)
                                                    {
                                                        if(!is_array($fieldValue))
                                                        {
                                                            $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],"valueCoding"=>array("code"=>$fieldValue));
                                                        }
                                                        else{
                                                            $arrayCode=array();
                                                            foreach($fieldValue as $value){
                                                                $arrayCode[]=array('code'=>$value);
                                                            }
                                                            $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],"valueCoding"=>[$arrayCode]);
                                                        }
                                                        
                                                    }
                                                    else
                                                    {
                                                        $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],'valueString'=>$fieldValue);
                                                    }
                                                    
                                                }
                                                array_push($contactPointElement['extension'],$extensionDetails);
                                                //print_r()
                                            }
                                            array_push($top['telecom'],$contactPointElement);
                                        }
                                        elseif($system !=="extension")
                                        {
                                            $top['telecom'][] = array( 'resourceType' => 'ContactPoint', 'system' => $system, 'use' => 'home','value' => $contact);
                                        }
                                }
                                else{
                                    $top['telecom'][] = array( 'resourceType' => 'ContactPoint', 'system' => $system, 'use' => $use, 'value' => $contact );
                            
                                }
                            }
                        }
                    }
                }
            }

            if ( array_key_exists( 'address', $data ) ) {
                $top['address'] = array();
                //echo "\n------------------- Address info ------ \n";
                //print_r($data['address']);
                foreach( $data['address'] as $use => $address ) {
                    if($use == "emergency")
                    {
                        /* $top['address'][] = array( 'resourceType' => 'Address', 'use' => 'temp','text' => $address
                        ,'extension'=>array(array('url'=>'http://ihris.org/fhir/StructureDefinition/iHRISAddressDetails',
                        'extension'=>array(array('url'=>"addressUse",'valueString'=>$use)
                        )))); */

                        if(array_key_exists( 'extension', $address ))
                        {
                            $addressElement=array('use' => 'home','text' => $address['mail'],
                            'extension'=>array());
                            foreach ( $address['extension'] as $extensionType => $extensionElements ) {
                                $extensionDetails=array('url'=>'http://ihris.org/fhir/StructureDefinition/'.$extensionType,
                                                    'extension'=>array());
                                foreach($extensionElements as $keyUrl=>$fieldValue)
                                {
                                    if(strpos($keyUrl,"date_")!==false)
                                    {
                                        $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],'valueDateTime'=>$fieldValue);
                                    }
                                    elseif(strpos($keyUrl,"value_")!==false)
                                    {
                                        $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],'valueString'=>$fieldValue);
                                    }
                                    elseif(strpos($keyUrl,"code_")!==false)
                                    {
                                        if(!is_array($fieldValue))
                                        {
                                            $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],"valueCoding"=>array("code"=>$fieldValue));
                                        }
                                        else{
                                            $arrayCode=array();
                                            foreach($fieldValue as $value){
                                                $arrayCode[]=array('code'=>$value);
                                            }
                                            $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],"valueCoding"=>[$arrayCode]);
                                        }
                                        
                                    }
                                    else
                                    {
                                        $extensionDetails['extension'][]=array('url'=>explode('_',$keyUrl)[1],'valueString'=>$fieldValue);
                                    }
                                }
                            
                            array_push($addressElement['extension'],$extensionDetails);
                            }
                            array_push($top['address'],$addressElement);
                        }
                        else
                        {
                            $top['address'][] = array('use' => 'home', 'text' => $address['mail'] );
                        }

                    }
                    else{
                        $top['address'][] = array( 'resourceType' => 'Address', 'use' => $use, 'text' => $address );
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
                    if(array_key_exists( 'extension', $education ))
                    {
                        $qualification=null;
                        if($education['degree'])
                        {
                            $qualification = array('identifier'=>array(array('value'=>$education['major'])),
                            'code'=>array('text'=>$education['degree']),
                            'period'=>array('start'=>$education['start_date']),
                            'issuer'=>array('reference' =>$education['issuer']),
                            'extension'=>array()); 
                        }
                        else
                        {
                            $qualification = array('identifier'=>array(array('value'=>$education['major'])),
                            'period'=>array('start'=>$education['start_date']),
                            'issuer'=>array('reference' =>$education['issuer']),
                            'extension'=>array());
                        }
                        
                        
                        foreach($education['extension'] as $extensionInfo=>$extensionInfoList)
                        {
                            
                            /* echo "\n---- Qualification extension --- \n";
                            print_r($extensionInfoList); */
                            //$qualification['extension'];
                            $oQualification=array('url'=>'http://ihris.org/fhir/StructureDefinition/'.$extensionInfo,
                                'extension'=>array());
                            foreach($extensionInfoList as  $keyFieldName => $fieldValue)
                            {
                                //$qualification['extension']['extension'][]=array()
                                if(strpos($keyFieldName,"date_")!==false)
                                {
                                    $oQualification['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDate'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"datetime_")!==false)
                                {
                                    $oQualification['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDateTime'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"value_")!==false)
                                {
                                    $oQualification['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"code_")!==false)
                                {
                                    if(!is_array($fieldValue))
                                    {
                                        $oQualification['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>array("code"=>$fieldValue));
                                    }
                                    else{
                                        $arrayCode=array();
                                        foreach($fieldValue as $value){
                                            $arrayCode[]=array('code'=>$value);
                                        }
                                        $oQualification['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>[$arrayCode]);
                                    }
                                
                                }
                                else{
                                    $oQualification['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                }
                            }
                            $qualification['extension'][]=$oQualification;
                        }
                        array_push($top['qualification'],$qualification);

                    }
                    else
                    {   
                        $qualification = array('identifier'=>array(array('value'=>$education['major'])),
                        'code'=>array('text'=>$education['degree']),
                        'period'=>array('start'=>$education['start_date']),
                        'issuer'=>$education['issuer']
                        );
                        array_push($top['qualification'],$qualification);
                    }
                    /* $qualification = array('identifier'=>array(array('value'=>$education['major'])),
                    'code'=>array('text'=>$education['degree']),
                    'period'=>array('start'=>$education['end_year']),
                    'extension'=>array(array('url'=>'http://ihris.org/fhir/StructureDefinition/iHRISQualificationDetails',
                    'extension'=>array(array('url'=>'institution','valueString'=>$education['institution']),
                                array('startYear'=>$education['start_year']),
                                array('endYear'=>$education['end_year']))))
                    );
                    array_push($top['qualification'],$qualification); */
                    

                }
            }
            if ( array_key_exists( 'registration', $data ) ) {
                if(!array_key_exists( 'qualification', $top )){
                    $top['qualification'] = array();
                }
                foreach( $data['registration'] as $ids => $registration) {
                    if(array_key_exists( 'extension', $registration ))
                    {
                        $qualification=null;
                        $qualification = array('identifier'=>array(array('value'=>$registration['major'])),
                            'code'=>array('text'=>$registration['degree']),
                            'period'=>array('start'=>$registration['start_date'],"end"=>$registration['end_date']),
                            'issuer'=>array('reference' =>$registration['issuer']),
                            'extension'=>array());
                        foreach($registration['extension'] as $extensionInfo=>$extensionInfoList)
                        {
                            
                            /* echo "\n---- Qualification extension --- \n";
                            print_r($extensionInfoList); */
                            $qualification['extension']=array('url'=>'http://ihris.org/fhir/StructureDefinition/'.$extensionInfo,
                                'extension'=>array());
                            foreach($extensionInfoList as  $keyFieldName => $fieldValue)
                            {
                                //$qualification['extension']['extension'][]=array()
                                if(strpos($keyFieldName,"date_")!==false)
                                {
                                    $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDate'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"datetime_")!==false)
                                {
                                    $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDateTime'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"value_")!==false)
                                {
                                    $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"code_")!==false)
                                {
                                    if(!is_array($fieldValue))
                                    {
                                        $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>array("code"=>$fieldValue));
                                    }
                                    else{
                                        $arrayCode=array();
                                        foreach($fieldValue as $value){
                                            $arrayCode[]=array('code'=>$value);
                                        }
                                        $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>[$arrayCode]);
                                    }
                                
                                }
                                else{
                                    $qualification['extension']['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                }
                            }
                        }
                        array_push($top['qualification'],$qualification);
                    }
                    else{
                        $qualification = array('identifier'=>array(array('value'=>$registration['major'])),
                        'code'=>array('text'=>$registration['degree']),
                        'period'=>array('start'=>$registration['start_date'],"end"=>$registration['end_date']),
                        'issuer'=>array('reference' =>$registration['issuer'])
                        );
                        array_push($top['qualification'],$qualification);
                    }
                }
            }
            if(array_key_exists( 'extension', $data ))
            {
                
                foreach ( $data['extension'] as $keyExtension => $extensionInfoList ) {
                    //if($keyExtension == "iHRISPractitionerProfessionalSituationDetails")
                    if(true)
                    {   
                        if(!empty($extensionInfoList))
                        {
                            foreach($extensionInfoList as $counter=>$extensionInfo){
                                /*echo "\n ----------------------- Extenstion -------------------\n";
                                print_r($extensionInfo);*/

                                $practitionerExtension=array('url'=>"http://ihris.org/fhir/StructureDefinition/".$keyExtension,
                                'extension'=>array());
                                foreach ( $extensionInfo as $keyFieldName => $fieldValue ) {
                                    if(strpos($keyFieldName,"date_")!==false)
                                    {
                                        $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDate'=>$fieldValue);
                                    }
                                    elseif(strpos($keyFieldName,"datetime_")!==false)
                                    {
                                        $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDateTime'=>$fieldValue);
                                    }
                                    elseif(strpos($keyFieldName,"value_")!==false)
                                    {
                                        $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                    }
                                    elseif(strpos($keyFieldName,"code_")!==false)
                                    {
                                        if(!is_array($fieldValue))
                                        {
                                            $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>array("code"=>$fieldValue));
                                        }
                                        else{
                                            $arrayCode=array();
                                            foreach($fieldValue as $value){
                                                $arrayCode[]=array('code'=>$value);
                                            }
                                            $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>[$arrayCode]);
                                        }
                                    
                                    }
                                }
                            array_push($top["extension"],$practitionerExtension);
                            }
                            
                        }

                    }
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
    protected function createMapping_Practitioner($data,$linkedData,&$top,$listMappingFields,$listExtensionMappingFields,$configs)
    {
        $top['identifier'] = array();
        $top['extension']=array();
        $top['qualification']=array();
        $top['photo']=array();
        $top['communication']=array();
        $telecom=array();
        $address=array();
        $communication=array();
        if($configs['profileName']!=''){
            $top['meta']=array('profile'=>[$configs['profileName']]);
        }
        

        //print_r($data);
        /*echo "\n---------------------extension metadata----------------------\n"; */ 
        //print_r($listExtensionMappingFields);
        /*echo "\n--------------------------------------------------------------\n";  */

        $oIdentifier=array();
        //$oIdentifier['system']=$configs['identifierSystemUrl'];
        //$oIdentifier['type']=array('coding'=>array(array('system'=>'http://terminology.hl7.org/CodeSystem/v2-0203','code'=>'EN','display'=>'Matricule')));
        $oHumanName['use']=$configs['nameUse'];
        $oPhoto = array();
        $oQualification=array();
        foreach($listMappingFields as $keyIndex=>$mappingFields)
        {
            foreach($data as $key=>$value)
            {
                $fieldExtension=array();
                $fieldHasExtension=false;
                if($key==$mappingFields['iHRISFieldAlias'])
                {
                   $this->extractFieldExtension($listExtensionMappingFields,$mappingFields,$configs,$data,$fieldExtension,
                    $fieldHasExtension); 
                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="Practitioner")
                {
                    if(array_key_exists('extension',$mappingFields)){
                        $dicValue=null;
                        switch($mappingFields['extension']['fieldType'])
                        {
                            case"integer":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueInteger'=>$value);
                            break;
                            case"string":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueString'=>$value);
                            break;
                            case"date":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueDate'=> (new DateTime($value))->format("Y-m-d"));
                            break;
                            case"dateTime":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueDateTime'=>(new DateTime($value))->format("c"));
                            break;
                            case "code":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueCoding'=>array("code"=>$value));
                            break;
                            default:
                            $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueString'=>$value);
                        }
                        //$oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],$dicValue);
                        $top['extension'][]=$oExtension;
                    }
                    else
                    {
                        if($mappingFields['fhirField']=="birthDate")
                        {
                            $top[$mappingFields['fhirField']]= (new DateTime($value))->format("Y-m-d");
                        }
                        elseif($mappingFields['fhirField']=="gender"){
                            $top[$mappingFields['fhirField']]=$configs["genderCode"][$value];
                        }
                        else
                        {
                            $top[$mappingFields['fhirField']]=$value;
                        }
                        
                    }
                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="identifier")
                {
                    if($mappingFields['fhirField']=="system")
                    {
                        $oIdentifier['system']=$configs['identifierSystemUrl'].$value;
                    }
                    elseif($mappingFields['fhirField']=="type_display")
                    {
                        if(!array_key_exists('type',$oIdentifier))
                        {
                            $oIdentifier['type']=array('coding'=>array());
                        }
                        //$oIdentifier['type']['coding'][0]=array('display'=>$value);
                        $oIdentifier['type']['coding'][0]['display']=$value;
                    }
                    elseif($mappingFields['fhirField']=="type_code")
                    {
                        if(!array_key_exists('type',$oIdentifier))
                        {
                            $oIdentifier['type']=array('coding'=>array());
                        }
                        //$oIdentifier['type']['coding'][0]=array('code'=>$value);
                        if($configs['removeiHRISPrefixForCodeableConcept'])
                        {
                            $temp=explode("|",$value)[1];
                            $oIdentifier['type']['coding'][0]['code']=$$temp;
                        }
                        else{
                            $oIdentifier['type']['coding'][0]['code']=$value;
                        }
                        
                    }
                    elseif($mappingFields['fhirField']=="value"){
                        $oIdentifier['value']=$value;
                    }
                    elseif($mappingFields['fhirField']=="period.start"){
                        $oIdentifier['period']['start']= (new DateTime($value))->format('c');
                    }
                    elseif($mappingFields['fhirField']=="period.end"){
                        $oIdentifier['period']['end']=(new DateTime($value))->format('c');;
                    }
                    elseif($mappingFields['fhirField']=="assigner"){
                        $oIdentifier['assigner']=$value;
                    }
                    if($fieldHasExtension){
                        if(!array_key_exists('extension',$oIdentifier))
                        {
                            $oIdentifier['extension']=array();
                        }
                        $oIdentifier['extension'][]=$fieldExtension;
                    }
                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="name")
                {
                    if($mappingFields['fhirField']=="family")
                    {
                       $oHumanName[$mappingFields['fhirField']]=$value;
                    }
                    if($mappingFields['fhirField']=="given")
                    {
                       $oHumanName[$mappingFields['fhirField']][]=$value;
                    }
                    if($fieldHasExtension){
                        if(!array_key_exists('extension',$oHumanName))
                        {
                            $oHumanName['extension']=array();
                        }
                        $oHumanName['extension'][]=$fieldExtension;
                    }

                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="photo")
                {
                    if($mappingFields['fhirField']=="url")
                    {
                        $oPhoto[$mappingFields['fhirField']]=$configs['binaryServerUrl'].$value.$configs['photoFileExtension'];
                    }
                    elseif($mappingFields['fhirField']=="creation")
                    {
                        $oPhoto[$mappingFields['fhirField']]=(new DateTime($value))->format('c');
                    }
                    
                    if($fieldHasExtension){
                        if(!array_key_exists('extension',$oPhoto))
                        {
                            $oPhoto['extension']=array();
                        }
                        $oPhoto['extension'][]=$fieldExtension;
                    }

                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="telecom")
                {
                    $telecomKey=explode('_',$mappingFields['iHRISFieldAlias']);
                    if(sizeof($telecomKey)<=1)
                    {
                        if(!array_key_exists('default',$telecom)){
                            $telecom['default']=array();
                        }
                        $telecom['default']['use']='home';
                        if(in_array($telecomKey[0],array('phone','fax','email','pager','url','sms')) ){
                            $telecom['default']['system']=$telecomKey[0];
                        }
                        else
                        {
                            $telecom['default']['system']="other";
                        }
                        if($telecomKey[0]=="phone")
                        {
                            echo "-----phone to clean-------------\n";
                            //$telecom['default']['value']=$configs['appendCountryCodeToPhoneNumber']['value'].$value;
                            $telecom['default']['value']=$this->cleanPhoneNumber($value,$configs['appendCountryCodeToPhoneNumber']['value']);
                        }
                        else
                        {
                            $telecom['default']['value']=$value;
                        }
                        
                        if($fieldHasExtension){
                            if(!array_key_exists('extension',$telecom['default']))
                            {
                                $telecom['default']['extension']=array();
                            }
                            $telecom['default']['extension'][]=$fieldExtension;
                        }
                        
                    }
                    else
                    {
                        if(!array_key_exists($telecomKey[0],$telecom)){
                            //$telecom[$telecomKey[0]]=array();
                            $telecom[$mappingFields['iHRISFieldAlias']]=array();
                        }
                        if(in_array($telecomKey[0],array('home','work','mobile')) )
                        {
                            //$telecom[$telecomKey[0]]['use']=$telecomKey[0];
                            $telecom[$mappingFields['iHRISFieldAlias']]['use']=$telecomKey[0];
                        }
                        else{
                            //$telecom[$telecomKey[0]]['use']='temp';
                            $telecom[$mappingFields['iHRISFieldAlias']]['use']='temp';
                        }
                        if(in_array($telecomKey[1],array('phone','fax','email','pager','url','sms')) ){
                            //$telecom[$telecomKey[0]]['system']=$telecomKey[1];
                            $telecom[$mappingFields['iHRISFieldAlias']]['system']=$telecomKey[1];
                        }
                        else
                        {
                            //$telecom[$telecomKey[0]]['system']="other";
                            $telecom[$mappingFields['iHRISFieldAlias']]['system']="other";
                        }
                        if($telecomKey[1]=="phone")
                        {
                            //echo "\n -------- $telecomKey[1]---------------\n";
                            //$telecom[$telecomKey[0]]['value']=$configs['appendCountryCodeToPhoneNumber']['value'].$value;
                            $telecom[$mappingFields['iHRISFieldAlias']]['value']=$this->cleanPhoneNumber($value,$configs['appendCountryCodeToPhoneNumber']['value']);
                        }
                        else
                        {
                            //$telecom[$telecomKey[0]]['value']=$value;
                            $telecom[$mappingFields['iHRISFieldAlias']]['value']=$value;
                        }
                        
                        if($fieldHasExtension){
                            if(!array_key_exists('extension',$telecom[$telecomKey[0]]))
                            {
                                //$telecom[$telecomKey[0]]['extension']=array();
                                $telecom[$mappingFields['iHRISFieldAlias']]['extension']=array();
                            }
                            //$telecom[$telecomKey[0]]['extension'][]=$fieldExtension;
                            $telecom[$mappingFields['iHRISFieldAlias']]['extension'][]=$fieldExtension;
                        }
                        
                    }
                    
                    
                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="address")
                {
                    $addressKey=explode('_',$mappingFields['iHRISFieldAlias']);
                    if(sizeof($addressKey)<=1)
                    {
                        if(!array_key_exists('default',$address)){
                            $address['default']=array();
                            $address['default']['use']='home';
                        }
                        
                        if($mappingFields['fhirField']=="type")
                        {
                           $address['default'][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="text")
                        {
                           $address['default'][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="line")
                        {
                           $address['default'][$mappingFields['fhirField']][]=$value;
                        }
                        elseif($mappingFields['fhirField']=="city")
                        {
                           $address['default'][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="district")
                        {
                           $address['default'][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="state")
                        {
                           $address['default'][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="postalCode")
                        {
                           $address['default'][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="period.start"){
                            $address['default']['period']['start']= (new DateTime($value))->format('c');
                        }
                        elseif($mappingFields['fhirField']=="period.end"){
                            $address['default']['period']['end']= (new DateTime($value))->format('c');
                        }
                        if($fieldHasExtension){
                            if(!array_key_exists('extension',$address['default']))
                            {
                                $address['default']['extension']=array();
                            }
                            $address['default']['extension'][]=$fieldExtension;
                        }
                    }
                    else{
                        if(!array_key_exists($addressKey[0],$address)){
                            $address[$addressKey[0]]=array();
                            if(in_array($addressKey[0],array('home','work','temp','old','billing')) )
                            {
                                $address[$addressKey[0]]['use']=$addressKey[0];
                            }
                        }
                        
                        if($mappingFields['fhirField']=="type")
                        {
                            $address[$addressKey[0]][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="text")
                        {
                            $address[$addressKey[0]][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="line")
                        {
                            $address[$addressKey[0]][$mappingFields['fhirField']][]=$value;
                        }
                        elseif($mappingFields['fhirField']=="city")
                        {
                            $address[$addressKey[0]][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="district")
                        {
                            $address[$addressKey[0]][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="state")
                        {
                            $address[$addressKey[0]][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="postalCode")
                        {
                            $address[$addressKey[0]][$mappingFields['fhirField']]=$value;
                        }
                        elseif($mappingFields['fhirField']=="period.start"){
                            $address[$addressKey[0]]['period']['start']= (new DateTime($value))->format('c');
                        }
                        elseif($mappingFields['fhirField']=="period.end"){
                            $address[$addressKey[0]]['period']['end']= (new DateTime($value))->format('c');
                        }
                        if($fieldHasExtension){
                            if(!array_key_exists('extension',$address[$addressKey[0]]))
                            {
                                $address[$addressKey[0]]['extension']=array();
                            }
                            $address[$addressKey[0]]['extension'][]=$fieldExtension;
                        }

                    }
                    
                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="qualification"){
                    if(in_array($mappingFields['iHRISFieldAlias'],explode(',',
                        $configs['transformQualificationInstitutionNameToId']['iHRISFieldAlias'])))
                        {
                            $value=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $value));
                        }
                    if($mappingFields['fhirField']=="issuer")
                    {
                       $oQualification[$mappingFields['fhirField']]=array('reference' =>"Organization/".$value);
                    }
                    if($mappingFields['fhirField']=="identifier.value")
                    {
                        $oQualification['identifier'][0]=array('value'=>$value);
                    }
                    if($mappingFields['fhirField']=="period.start")
                    {   
                        $oQualification['period']['start']=(new DateTime($value))->format('c');
                    }
                    if($mappingFields['fhirField']=="period.end")
                    {   
                        $oQualification['period']['end']=(new DateTime($value))->format('c');
                    }
                    if($mappingFields['fhirField']=="code")
                    {   
                        $oQualification[$mappingFields['fhirField']]=array('text'=>$value);
                    }
                    if($fieldHasExtension){
                        if(!array_key_exists('extension',$oQualification))
                        {
                            $oQualification['extension']=array();
                        }
                        $oQualification['extension'][]=$fieldExtension;
                    }

                }


            }
            
        }
       

        if($oIdentifier['value']!=null)
        {
            if(!array_key_exists('type',$oIdentifier))
            {
                $oIdentifier['type']=array('coding'=>array(array(
                'code'=>$configs['defaultIdentifierCodeableConcept']['code'],
                'display'=>$configs['defaultIdentifierCodeableConcept']['display'])),
                'text'=>$configs['defaultIdentifierCodeableConcept']['display']
            );
            }
            array_push($top['identifier'],$oIdentifier);
        }
        $top['name'][]=$oHumanName;
        if(!empty($oPhoto)){
            $top['photo'][]=$oPhoto;
        }
        if(!empty($oQualification)){
            $top['qualification'][]=$oQualification;
        }
        
        foreach($telecom as $key=>$contactPoint)
        {
            $top['telecom'][]=$contactPoint;
        }
        foreach($address as $key=>$oAddress)
        {
            $top['address'][]=$oAddress;
        }
         //Add now linked fields
         $identifierLinked=array();
         $communicationLinked=array();
         $contactPointLinked=array();
         $addressLinked=array();
         $qualificationLinked=array();
         //$indexLinked=0;
        $this->extractPractitionerLinkedData($listMappingFields,$listExtensionMappingFields,$configs,$linkedData,
            $identifierLinked,$contactPointLinked,$addressLinked,$qualificationLinked,$communicationLinked);
        
         
         //print_r($communicationLinked);
         if(!empty($identifierLinked))
         {
            foreach($identifierLinked as $key=>$identifier)
            {
                if(!array_key_exists('type',$identifier))
                {
                    $identifier['type']=array('coding'=>array(array(
                    'code'=>$configs['defaultIdentifierCodeableConcept']['code'],
                    'display'=>$configs['defaultIdentifierCodeableConcept']['display'])),
                    'text'=>$configs['defaultIdentifierCodeableConcept']['display']
                );
                }
                $top['identifier'][]=$identifier;
            }
         } 
         /* if(!empty($communicationLinked))
         {
            foreach($communicationLinked as $key=>$oCommunication)
            {
                array_push($top['communication'],$oCommunication);
            }
         } */
         if(!empty($contactPointLinked))
         {
            foreach($contactPointLinked as $key=>$oTelecom)
            {
                array_push($top['telecom'],$oTelecom);
            }
         }
         if(!empty($addressLinked))
         {
            foreach($addressLinked as $key=>$oAddress)
            {
                array_push($top['address'],$oAddress);
            }
         }
         if(!empty($qualificationLinked))
         {
            foreach($qualificationLinked as $key=>$qualificationLnk)
            {
                array_push($top['qualification'],$qualificationLnk);
            }
         }
         //Now add group extension
        $practitionerGroupExtension=array();
        $communicationExtensionLinked=array();
        $this->extractPractitionerGroupExtensions($listExtensionMappingFields,$listMappingFields,$configs,$data,$linkedData,
            $practitionerGroupExtension,$communicationExtensionLinked);
        //print_r($communicationLinked);
        if(!empty($practitionerGroupExtension)){
            foreach($practitionerGroupExtension as $key=>$oExtension)
            {
                if($oExtension){
                    $top['extension'][]=$oExtension;
                }
                
            }
        }
        if(!empty($communicationExtensionLinked)){
            foreach($communicationExtensionLinked as $key=>$oCommunication)
            {
                $top['communication'][]=$oCommunication;
            }
        }
        else
        {
            foreach($communicationLinked as $key=>$oCommunication)
            {
                array_push($top['communication'],$oCommunication);
            }
        }
        //$top['telecom']=$telecom;
        //print_r($top);
    }
    protected function cleanPhoneNumber($value,$countryCode)
    {
        if($countryCode=="")
        {
            return $value;
        }
        else//remove the first char if it is 0,then append the country code
        {
            if(preg_match('#^0#',$value)===1)
            {
                $len=strlen($value);
                $newValue=substr($value,1,$len);
                $newValue=$countryCode.$newValue;
                return $newValue;

            }
            else{

                return $countryCode.$value;
            }
        }
    }
    protected function extractFieldExtension($listExtensionMappingFields,$mappingFields,$configs,$data,
    &$fieldExtension,&$fieldHasExtension)
    {
        
        foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
        {
            if($extensionMapping['type']=="byFields")
            {
                $listAppliedFields=explode(',',$extensionMapping['appliedFields']);
                if(in_array($mappingFields['iHRISFieldAlias'],$listAppliedFields))
                {
                    
                    
                    foreach($extensionMapping['fieldList'] as $keyField=>$extensionField)
                    {
                        foreach($data as $keyAppliedField=>$valueAppliedField)
                        {
                            if($keyAppliedField==$extensionField['iHRISFieldAlias'] && $extensionField['active'])
                            {
                                if(!array_key_exists('url',$fieldExtension))
                                {
                                    $fieldHasExtension=true;
                                    $fieldExtension['extension']=array();
                                    $fieldExtension['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                                }
                                switch($extensionField['fieldType']){
                                    case"integer":
                                        $fieldExtension['extension'][]=array('url'=>$extensionField['fhirField'],'valueInteger'=>$valueAppliedField);
                                    break;
                                    case"string":
                                        $fieldExtension['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$valueAppliedField);
                                    break;
                                    case"date":
                                        $fieldExtension['extension'][]=array('url'=>$extensionField['fhirField'],'valueDate'=>(new DateTime($valueAppliedField))->format("Y-m-d"));
                                    break;
                                    case"dateTime":
                                        $fieldExtension['extension'][]=array('url'=>$extensionField['fhirField'],'valueDateTime'=> (new DateTime($valueAppliedField))->format("c"));
                                    break;
                                    case "code":
                                        $fieldExtension['extension'][]=array('url'=>$extensionField['fhirField'],'valueCoding'=>array('code'=>$valueAppliedField));
                                    break;
                                    default:
                                    $fieldExtension['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$valueAppliedField);
                                }
                            }
                        }
                    }
                }

            }
        }
        
    }
    protected function getExtensionFieldFhirLabel($nameOfTheField,&$fhirField,$listMappingFields)
    {
        foreach($listMappingFields as $keyIndex=>$mappingFields)
        {
            if($nameOfTheField==$mappingFields['iHRISFieldAlias'])
            {
                $fhirField=$mappingFields['fhirField'];
            break;
            }
            
        }
    }
    protected function extractPractitionerGroupExtensions($listExtensionMappingFields,$listMappingFields,$configs,$mainData,
        $linkedData,&$builtPractitionerExtension,&$builtCommunicationExtension)
    {
        //$practitionerGroupExtension=array();
        //$communicationLinked=array();
        $indexGroupExtension=0;
        $indexCommunicationExtension=0;
       
        foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
        {
            if($extensionMapping['type']=="inGroup")
            {
                $appliedField=explode(',',$extensionMapping['appliedFields'])[0];
                $appliedFhirField="";
                $appliedFieldValue=null;
                if($extensionMapping['fhirBaseElement']=="Practitioner")
                {
                   
                    $indexGroupExtension=$keyExtIndex;
                    $hasMainFieldData=false;
                    /* $builtPractitionerExtension[$indexGroupExtension]=array();
                    $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                     */
                    foreach($extensionMapping['fieldList'] as $keyField=>$extensionField)
                    {
                        foreach($mainData as $keyData=>$value){

                            if($keyData==$extensionField['iHRISFieldAlias'])
                            {
                                if(!array_key_exists($indexGroupExtension,$builtPractitionerExtension))
                                {
                                    $builtPractitionerExtension[$indexGroupExtension]=array();
                                    $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                                    
                                }

                                if(in_array($extensionField['iHRISFieldAlias'],explode(',',$configs['concatenateDocServerUrl']['iHRISFieldAlias'])))
                                {
                                    $value=$configs['concatenateDocServerUrl']['value'].$value;
                                }
                                $hasMainFieldData=true;
                                switch($extensionField['fieldType']){
                                    case"integer":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueInteger'=>$value);
                                    break;
                                    case"string":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                    break;
                                    case"date":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDate'=>(new DateTime($value))->format("Y-m-d"));
                                    break;
                                    case"dateTime":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDateTime'=> (new DateTime($value))->format("c"));
                                    break;
                                    case "code":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueCoding'=>array('code'=>$value));
                                    break;
                                    default:
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                }
                            }
                        }
                            
                    }
                    if(!$hasMainFieldData){
                        $builtPractitionerExtension[$indexGroupExtension]=array();
                    }
                    //$indexGroupExtension++;
                }
            }
        }
        $indexGroupExtension=0;
        foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
        {
            //print_r($extensionMapping);
            if($extensionMapping['type']=="byType")
            {
                
                if($extensionMapping['fhirBaseElement']=="Practitioner")
                {
                   
                    $indexGroupExtension=$keyExtIndex;
                    $hasMainFieldData=false;
                    $builtPractitionerExtension[$indexGroupExtension]=array();
                    $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                    $currentValue= array();
                    foreach($extensionMapping['fieldList'] as $keyField=>$extensionField)
                    {
                        //print_r($extensionField);
                        foreach($mainData as $keyData=>$value){

                            if($keyData==$extensionField['iHRISFieldAlias'])
                            {
                                if(array_key_exists('defaultConfigValue',$extensionField)){
                                    $value=$configs[$extensionField['defaultConfigValue']].$value;
                                }
                                $hasMainFieldData=true;
                                if($extensionMapping['appliedType']=="valueCoding")
                                {
                                    switch($extensionField['fhirField']){
                                        case"code":
                                            if($configs['removeiHRISPrefixForCodeableConcept'])
                                            {

                                                $temp=explode("|",$value)[1];
                                                $currentValue['code']=$temp;
                                            }
                                            else{
                                                $currentValue['code']=$value;
                                            }
                                        break;
                                        case"display":
                                            $currentValue['display']=$value;
                                        break;
                                    }
                                    
                                }
                            }
                        }
                            
                    }
                    //print_r($currentValue);
                    if(!$hasMainFieldData){
                        $builtPractitionerExtension[$indexGroupExtension]=array();
                    }
                    //$indexGroupExtension++;
                    //array_push( $builtPractitionerExtension[$indexGroupExtension],$currentValue);
                    $builtPractitionerExtension[$indexGroupExtension]["valueCoding"]=$currentValue;
                }
                
            }
        }
        foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
        {
            if($extensionMapping['type']=="inGroup")
            {
                $appliedField=explode(',',$extensionMapping['appliedFields'])[0];
                $appliedFhirField="";
                $appliedFieldValue=null;
                if($extensionMapping['fhirBaseElement']=="communication"){
                    foreach($linkedData as $keyForm=>$dataForms)
                    {
                        foreach($dataForms as $indexLinked=>$dataValue){
                            $indexCommunicationExtension=$keyForm.$indexLinked;
                            $extensionCounter=0;
                            foreach($dataValue as $keyData=>$value)
                            {
                                foreach($extensionMapping['fieldList'] as $keyField=>$extensionField){
                                    if($keyData==$extensionField['iHRISFieldAlias'] || $keyData==$appliedField)
                                    {
                                        if(!array_key_exists($indexCommunicationExtension,$builtCommunicationExtension))
                                        {
                                            $builtCommunicationExtension[$indexCommunicationExtension]=array();
                                            $builtCommunicationExtension[$indexCommunicationExtension]=array('extension'=>array(array('url'=>$configs['extensionUrl'].$extensionMapping['extensionName'],'extension'=>array())));
                                            
                                        }
                                        if($keyData==$appliedField){
                                            $this->getExtensionFieldFhirLabel($appliedField,$appliedFhirField,
                                            $listMappingFields);
                                            if($appliedFhirField!="")
                                            {
                                                if($appliedFhirField=="text")
                                                {
                                                    $builtCommunicationExtension[$indexCommunicationExtension]['text']=$value;
                                                }
                                            }
                                        }
                                        else
                                        {
                                            switch($extensionField['fieldType']){
                                                case"integer":
                                                    $builtCommunicationExtension[$indexCommunicationExtension]['extension'][0]['extension'][]=array('url'=>$extensionField['fhirField'],'valueInteger'=>$value);
                                                break;
                                                case"string":
                                                    $builtCommunicationExtension[$indexCommunicationExtension]['extension'][0]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                                break;
                                                case"date":
                                                    $builtCommunicationExtension[$indexCommunicationExtension]['extension'][0]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDate'=>(new DateTime($value))->format("Y-m-d"));
                                                break;
                                                case"dateTime":
                                                    $builtCommunicationExtension[$indexCommunicationExtension]['extension'][0]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDateTime'=> (new DateTime($value))->format("c"));
                                                break;
                                                case "code":
                                                    $builtCommunicationExtension[$indexCommunicationExtension]['extension'][0]['extension'][]=array('url'=>$extensionField['fhirField'],'valueCoding'=>array('code'=>$value));
                                                break;
                                                break;
                                                $builtCommunicationExtension[$indexCommunicationExtension]['extension'][0]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                            
                                            }
                                        }
                                        $extensionCounter++;
                                    }
                                }
                            }
                            //$indexCommunicationExtension++;
                        }
                    }
                }
                if($extensionMapping['fhirBaseElement']=="Practitioner"){
                    foreach($linkedData as $keyForm=>$dataForms)
                    {
                        foreach($dataForms as $indexLinked=>$dataValue){
                            $indexGroupExtension=$keyForm.$indexLinked;
                            $extensionCounter=0;
                            foreach($dataValue as $keyData=>$value)
                            {
                                
                                foreach($extensionMapping['fieldList'] as $keyField=>$extensionField){
                                    if(array_key_exists('defaultConfigValue',$extensionField) && strpos($keyData,'url')){
                                        $value=$configs[$extensionField['defaultConfigValue']].$value;
                                    }
                                    if($keyData==$extensionField['iHRISFieldAlias'] ){
                                        if(!array_key_exists($indexGroupExtension,$builtPractitionerExtension))
                                        {
                                            $builtPractitionerExtension[$indexGroupExtension]=array();
                                            $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                                            /* $builtPractitionerExtension[$indexGroupExtension]=array('extension'=>array(array('url'=>$configs['extensionUrl'].$extensionMapping['extensionName'],
                                            'extension'=>array()))); */
                                        }
                                        switch($extensionField['fieldType']){
                                            case"integer":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueInteger'=>$value);
                                            break;
                                            case"string":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                            break;
                                            case"date":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDate'=>(new DateTime($value))->format("Y-m-d"));
                                            break;
                                            case"dateTime":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDateTime'=> (new DateTime($value))->format("c"));
                                            break;
                                            case "code":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueCoding'=>array('code'=>$value));
                                            break;
                                            default:
                                            $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);

                                        }
                                    }
                                }
                            }
                            $extensionCounter;
                        }
                    }
                }

            
            }
        }
                        
    }
    protected function extractPractitionerRoleGroupExtensions($listExtensionMappingFields,$listMappingFields,$configs,$mainData,
        $linkedData,&$builtPractitionerExtension)
    {
        //$practitionerGroupExtension=array();
        //$communicationLinked=array();
        //print_r($mainData);
        $indexGroupExtension=0;
       
        foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
        {
            if($extensionMapping['type']=="inGroup")
            {
                
                if($extensionMapping['fhirBaseElement']=="PractitionerRole")
                {
                   
                    $indexGroupExtension=$keyExtIndex;
                    $hasMainFieldData=false;
                    $builtPractitionerExtension[$indexGroupExtension]=array();
                    $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                    foreach($extensionMapping['fieldList'] as $keyField=>$extensionField)
                    {
                        foreach($mainData as $keyData=>$value){

                            if($keyData==$extensionField['iHRISFieldAlias'])
                            {
                                if(array_key_exists('defaultConfigValue',$extensionField)){
                                    $value=$configs[$extensionField['defaultConfigValue']].$value;
                                }
                                $hasMainFieldData=true;
                                switch($extensionField['fieldType']){
                                    case"integer":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueInteger'=>$value);
                                    break;
                                    case"string":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                    break;
                                    case"date":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDate'=>(new DateTime($value))->format("Y-m-d"));
                                    break;
                                    case"dateTime":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDateTime'=> (new DateTime($value))->format("c"));
                                    break;
                                    case "valueCoding":
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueCoding'=>array('code'=>$value));
                                    break;
                                    default:
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                }
                            }
                        }
                            
                    }
                    if(!$hasMainFieldData){
                        $builtPractitionerExtension[$indexGroupExtension]=array();
                    }
                    //$indexGroupExtension++;
                }
            }
        }
        $indexGroupExtension=0;
        foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
        {
            //print_r($extensionMapping);
            if($extensionMapping['type']=="byType")
            {
                
                if($extensionMapping['fhirBaseElement']=="PractitionerRole")
                {
                   
                    $indexGroupExtension=$keyExtIndex;
                    $hasMainFieldData=false;
                    $builtPractitionerExtension[$indexGroupExtension]=array();
                    $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                    $currentValue= array();
                    foreach($extensionMapping['fieldList'] as $keyField=>$extensionField)
                    {
                        //print_r($extensionField);
                        foreach($mainData as $keyData=>$value){

                            if($keyData==$extensionField['iHRISFieldAlias'])
                            {
                                if(array_key_exists('defaultConfigValue',$extensionField)){
                                    $value=$configs[$extensionField['defaultConfigValue']].$value;
                                }
                                $hasMainFieldData=true;
                                if($extensionMapping['appliedType']=="valueCoding")
                                {
                                    switch($extensionField['fhirField']){
                                        case"code":
                                            if($configs['removeiHRISPrefixForCodeableConcept'])
                                            {

                                                $temp=explode("|",$value)[1];
                                                $currentValue['code']=$temp;
                                            }
                                            else{
                                                $currentValue['code']=$value;
                                            }
                                        break;
                                        case"display":
                                            $currentValue['display']=$value;
                                        break;
                                    }
                                    
                                }
                            }
                        }
                            
                    }
                    //print_r($currentValue);
                    if(!$hasMainFieldData){
                        $builtPractitionerExtension[$indexGroupExtension]=array();
                    }
                    //$indexGroupExtension++;
                    //array_push( $builtPractitionerExtension[$indexGroupExtension],$currentValue);
                    $builtPractitionerExtension[$indexGroupExtension]["valueCoding"]=$currentValue;
                }
                
            }
        }
       /*  echo "\n------------------result for main data --------------------\n";
        print_r($builtPractitionerExtension); */

        foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
        {
            if($extensionMapping['type']=="inGroup")
            {
               
                if($extensionMapping['fhirBaseElement']=="PractitionerRole"){
                    foreach($linkedData as $keyForm=>$dataForms)
                    {
                        foreach($dataForms as $indexLinked=>$dataValue){
                            $indexGroupExtension=$keyForm.$indexLinked;
                            $extensionCounter=0;
                            foreach($dataValue as $keyData=>$value)
                            {
                                
                                foreach($extensionMapping['fieldList'] as $keyField=>$extensionField){
                                    if(array_key_exists('defaultConfigValue',$extensionField) && strpos($keyData,'url')){
                                        $value=$configs[$extensionField['defaultConfigValue']].$value;
                                    }
                                    if($keyData==$extensionField['iHRISFieldAlias'] ){
                                        if(!array_key_exists($indexGroupExtension,$builtPractitionerExtension))
                                        {
                                            $builtPractitionerExtension[$indexGroupExtension]=array();
                                            $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                                            /* $builtPractitionerExtension[$indexGroupExtension]=array('extension'=>array(array('url'=>$configs['extensionUrl'].$extensionMapping['extensionName'],
                                            'extension'=>array()))); */
                                        }
                                        switch($extensionField['fieldType']){
                                            case"integer":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueInteger'=>$value);
                                            break;
                                            case"string":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                            break;
                                            case"date":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDate'=>(new DateTime($value))->format("Y-m-d"));
                                            break;
                                            case"dateTime":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDateTime'=> (new DateTime($value))->format("c"));
                                            break;
                                            case "code":
                                                $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueCoding'=>array('code'=>$value));
                                            break;
                                            default:
                                            $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);

                                        }
                                    }
                                }
                            }
                            $extensionCounter;
                        }
                    }
                }

            
            }
        }
                        
    }
    protected function extractOrganizationGroupExtensions($listExtensionMappingFields,$listMappingFields,$configs,$mainData,
    $linkedData,&$builtPractitionerExtension)
{
    //$practitionerGroupExtension=array();
    //$communicationLinked=array();
    $indexGroupExtension=0;
   
    foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
    {
        if($extensionMapping['type']=="inGroup")
        {
            
            if($extensionMapping['fhirBaseElement']=="Organization")
            {
               
                $indexGroupExtension=$keyExtIndex;
                $hasMainFieldData=false;
                $builtPractitionerExtension[$indexGroupExtension]=array();
                $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                foreach($extensionMapping['fieldList'] as $keyField=>$extensionField)
                {
                    foreach($mainData as $keyData=>$value){

                        if($keyData==$extensionField['iHRISFieldAlias'])
                        {
                            if(!array_key_exists($indexGroupExtension,$builtPractitionerExtension))
                            {
                                $builtPractitionerExtension[$indexGroupExtension]=array();
                                $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                            }
                            $hasMainFieldData=true;
                            switch($extensionField['fieldType']){
                                case"integer":
                                    $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueInteger'=>$value);
                                break;
                                case"string":
                                    $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                break;
                                case"date":
                                    $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDate'=>(new DateTime($value))->format("Y-m-d"));
                                break;
                                case"dateTime":
                                    $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDateTime'=> (new DateTime($value))->format("c"));
                                break;
                                case "code":
                                    $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueCoding'=>array('code'=>$value));
                                break;
                                default:
                                    $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                            }
                        }
                    }
                        
                }
                if(!$hasMainFieldData){
                    $builtPractitionerExtension[$indexGroupExtension]=array();
                }
                //$indexGroupExtension++;
            }
        }
    }
   /*  echo "\n------------------result for main data --------------------\n";
    print_r($builtPractitionerExtension); */

    foreach($listExtensionMappingFields as $keyExtIndex=>$extensionMapping)
    {
        if($extensionMapping['type']=="inGroup")
        {
           
            if($extensionMapping['fhirBaseElement']=="Organization"){
                foreach($linkedData as $keyForm=>$dataForms)
                {
                    foreach($dataForms as $indexLinked=>$dataValue){
                        $indexGroupExtension=$keyForm.$indexLinked;
                        $extensionCounter=0;
                        foreach($dataValue as $keyData=>$value)
                        {
                            
                            foreach($extensionMapping['fieldList'] as $keyField=>$extensionField){
                                if(array_key_exists('defaultConfigValue',$extensionField) && strpos($keyData,'url')){
                                    $value=$configs[$extensionField['defaultConfigValue']].$value;
                                }
                                if($keyData==$extensionField['iHRISFieldAlias'] ){
                                    if(!array_key_exists($indexGroupExtension,$builtPractitionerExtension))
                                    {
                                        $builtPractitionerExtension[$indexGroupExtension]=array();
                                        $builtPractitionerExtension[$indexGroupExtension]['url']=$configs['extensionUrl'].$extensionMapping['extensionName'];
                                        /* $builtPractitionerExtension[$indexGroupExtension]=array('extension'=>array(array('url'=>$configs['extensionUrl'].$extensionMapping['extensionName'],
                                        'extension'=>array()))); */
                                    }
                                    switch($extensionField['fieldType']){
                                        case"integer":
                                            $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueInteger'=>$value);
                                        break;
                                        case"string":
                                            $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);
                                        break;
                                        case"date":
                                            $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDate'=>(new DateTime($value))->format("Y-m-d"));
                                        break;
                                        case"dateTime":
                                            $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueDateTime'=> (new DateTime($value))->format("c"));
                                        break;
                                        case "code":
                                            $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueCoding'=>array('code'=>$value));
                                        break;
                                        default:
                                        $builtPractitionerExtension[$indexGroupExtension]['extension'][]=array('url'=>$extensionField['fhirField'],'valueString'=>$value);

                                    }
                                }
                            }
                        }
                        $extensionCounter;
                    }
                }
            }

        
        }
    }
                    
}
    protected function extractPractitionerLinkedData($listMappingFields,$listExtensionMappingFields,$configs,$linkedData,
        &$builtInIdentifier,&$builtInContactPoint,&$buildInAddress,&$builtInQualification,&$builtInCommunication)
    {
        $indexId="";
        $indexCPt="";
        $indexAdd="";
        $indexQual="";
        foreach($linkedData as $keyForm=>$dataForms)
        {
            foreach($dataForms as $indexLinked=>$dataValue){
                $indexId=$keyForm.$indexLinked;
                $indexCPt=$keyForm.$indexLinked;
                $indexAdd=$keyForm.$indexLinked;
                $indexQual=$keyForm.$indexLinked;
                foreach($dataValue as $key=>$value)
                {
                    $fieldExtension=array();
                    $fieldHasExtension=false;
                    foreach($listMappingFields as $keyIndex=>$mappingFields)
                    {
                        if($key==$mappingFields['iHRISFieldAlias'])
                        {
                            $this->extractFieldExtension($listExtensionMappingFields,$mappingFields,$configs,$dataValue,$fieldExtension,
                                $fieldHasExtension); 
                        }
                        if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="identifier"){
                            if(!array_key_exists($indexId,$builtInIdentifier))
                            {
                                $builtInIdentifier[$indexId]=array();
                            }
                            if($mappingFields['fhirField']=="system")
                            {
                                $builtInIdentifier[$indexId]['system']=$configs['identifierSystemUrl'].$value;
                            }
                            elseif($mappingFields['fhirField']=="type_display")
                            {
                                if(!array_key_exists('type',$builtInIdentifier[$indexId]))
                                {
                                    $builtInIdentifier[$indexId]['type']=array('coding'=>array(),'text'=>'');
                                    
                                }
                                ///$builtInIdentifier[$indexId]['type']['coding'][0]=array('display'=>$value);
                                $builtInIdentifier[$indexId]['type']['coding'][0]['display']=$value;
                                $builtInIdentifier[$indexId]['type']['text']=$value;

                            }
                            elseif($mappingFields['fhirField']=="type_code")
                            {
                                if(!array_key_exists('type',$builtInIdentifier[$indexId]))
                                {
                                    $builtInIdentifier[$indexId]['type']=array('coding'=>array(),'text'=>'');
                                }
                                //$builtInIdentifier[$indexId]['type']['coding'][0]=array('code'=>$value);
                                if($configs['removeiHRISPrefixForCodeableConcept'])
                                {
                                    $temp=explode("|",$value)[1];
                                    $builtInIdentifier[$indexId]['type']['coding'][0]['code']=$temp;
                                }
                                else
                                {
                                    $builtInIdentifier[$indexId]['type']['coding'][0]['code']=$value;
                                }
                                
                            }
                            elseif($mappingFields['fhirField']=="value"){
                                $builtInIdentifier[$indexId]['value']=$value;
                            }
                            elseif($mappingFields['fhirField']=="period.start"){
                                $builtInIdentifier[$indexId]['period']['start']= (new DateTime($value))->format('c');
                            }
                            elseif($mappingFields['fhirField']=="period.end"){
                                $builtInIdentifier[$indexId]['period']['end']=(new DateTime($value))->format('c');;
                            }
                            elseif($mappingFields['fhirField']=="assigner"){
                                $builtInIdentifier[$indexId]['assigner']=$value;
                            } 
                            if($fieldHasExtension){
                                if(!array_key_exists('extension',$builtInIdentifier[$indexId]))
                                {
                                    $builtInIdentifier[$indexId]['extension']=array();
                                    $builtInIdentifier[$indexId]['extension'][]=$fieldExtension;
                                }
                                
                            }

                        }
                        if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="telecom"){
                            if(!array_key_exists($indexCPt,$builtInContactPoint))
                            {
                                $builtInContactPoint[$indexCPt]=array();
                            }
                            $telecomKey=explode('_',$mappingFields['iHRISFieldAlias']);
                            if(sizeof($telecomKey)<=1)
                            {
                                if(!array_key_exists('default',$builtInContactPoint[$indexCPt])){
                                    $builtInContactPoint[$indexCPt]['default']=array();
                                }
                                $builtInContactPoint[$indexCPt]['default']['use']='home';
                                if(in_array($telecomKey[0],array('phone','fax','email','pager','url','sms')) ){
                                    $builtInContactPoint[$indexCPt]['default']['system']=$telecomKey[0];
                                }
                                else
                                {
                                    $builtInContactPoint[$indexCPt]['default']['system']="other";
                                }
                                
                                $builtInContactPoint[$indexCPt]['default']['value']=$value;
                                if($fieldHasExtension){
                                    if(!array_key_exists('extension',$builtInContactPoint[$indexCPt]['default']))
                                    {
                                        $builtInContactPoint[$indexCPt]['default']['extension']=array();
                                        $builtInContactPoint[$indexCPt]['default']['extension'][]=$fieldExtension;
                                    }
                                }
                            }
                            else{
                                if(!array_key_exists($telecomKey[0],$builtInContactPoint[$indexCPt])){
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]=array();
                                }
                                if(in_array($telecomKey[0],array('home','work','mobile')) )
                                {
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]['use']=$telecomKey[0];
                                }
                                else{
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]['use']='temp';
                                }
                                if(in_array($telecomKey[1],array('phone','fax','email','pager','url','sms')) ){
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]['system']=$telecomKey[1];
                                }
                                else
                                {
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]['system']="other";
                                }
                                
                                $builtInContactPoint[$indexCPt][$telecomKey[0]]['value']=$value;
                                if($fieldHasExtension){
                                    if(!array_key_exists('extension',$builtInContactPoint[$indexCPt][$telecomKey[0]]))
                                    {
                                        $builtInContactPoint[$indexCPt][$telecomKey[0]]['extension']=array();
                                        $builtInContactPoint[$indexCPt][$telecomKey[0]]['extension'][]=$fieldExtension;
                                    }
                                }
                            }
                        }
                        if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="address"){
                            if(!array_key_exists($indexAdd,$buildInAddress))
                            {
                                $buildInAddress[$indexAdd]=array();
                            }
                            $addressKey=explode('_',$mappingFields['iHRISFieldAlias']);
                            if(sizeof($addressKey)<=1)
                            {
                                if(!array_key_exists('default',$buildInAddress[$indexAdd])){
                                    $buildInAddress[$indexAdd]['default']=array();
                                    $buildInAddress[$indexAdd]['default']['use']='home';
                                }
                                
                                if($mappingFields['fhirField']=="type")
                                {
                                    $buildInAddress[$indexAdd]['default'][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="text")
                                {
                                    $buildInAddress[$indexAdd]['default'][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="line")
                                {
                                    $buildInAddress[$indexAdd]['default'][$mappingFields['fhirField']][]=$value;
                                }
                                elseif($mappingFields['fhirField']=="city")
                                {
                                    $buildInAddress[$indexAdd]['default'][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="district")
                                {
                                    $buildInAddress[$indexAdd]['default'][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="state")
                                {
                                    $buildInAddress[$indexAdd]['default'][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="postalCode")
                                {
                                    $buildInAddress[$indexAdd]['default'][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="period.start"){
                                    $buildInAddress[$indexAdd]['default']['period']['start']= (new DateTime($value))->format('c');
                                }
                                elseif($mappingFields['fhirField']=="period.end"){
                                    $buildInAddress[$indexAdd]['default']['period']['end']= (new DateTime($value))->format('c');
                                }
                                if($fieldHasExtension){
                                    if(!array_key_exists('extension',$buildInAddress[$indexAdd]['default']))
                                    {
                                        $buildInAddress[$indexAdd]['default']['extension']=array();
                                    }
                                    $buildInAddress[$indexAdd]['default']['extension'][]=$fieldExtension;
                                }
                            }
                            else{
                                if(!array_key_exists($addressKey[0],$$buildInAddress[$indexAdd])){
                                    $buildInAddress[$indexAdd][$addressKey[0]]=array();
                                    if(in_array($addressKey[0],array('home','work','temp','old','billing')) )
                                    {
                                        $buildInAddress[$indexAdd][$addressKey[0]]['use']=$addressKey[0];
                                    }
                                }
                                
                                if($mappingFields['fhirField']=="type")
                                {
                                    $buildInAddress[$indexAdd][$addressKey[0]][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="text")
                                {
                                    $buildInAddress[$indexAdd][$addressKey[0]][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="line")
                                {
                                    $buildInAddress[$indexAdd][$addressKey[0]][$mappingFields['fhirField']][]=$value;
                                }
                                elseif($mappingFields['fhirField']=="city")
                                {
                                    $buildInAddress[$indexAdd][$addressKey[0]][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="district")
                                {
                                    $buildInAddress[$indexAdd][$addressKey[0]][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="state")
                                {
                                    $buildInAddress[$indexAdd][$addressKey[0]][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="postalCode")
                                {
                                    $buildInAddress[$indexAdd][$addressKey[0]][$mappingFields['fhirField']]=$value;
                                }
                                elseif($mappingFields['fhirField']=="period.start"){
                                    $buildInAddress[$indexAdd][$addressKey[0]]['period']['start']= (new DateTime($value))->format('c');
                                }
                                elseif($mappingFields['fhirField']=="period.end"){
                                    $buildInAddress[$indexAdd][$addressKey[0]]['period']['end']= (new DateTime($value))->format('c');
                                }
                                if($fieldHasExtension){
                                    if(!array_key_exists('extension',$address[$addressKey[0]]))
                                    {
                                        $buildInAddress[$indexAdd][$addressKey[0]]['extension']=array();
                                    }
                                    $buildInAddress[$indexAdd][$addressKey[0]]['extension'][]=$fieldExtension;
                                }

                            }
                        }
                        if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="qualification"){
                            if(!array_key_exists($indexQual,$builtInQualification))
                            {
                                $builtInQualification[$indexQual]=array();
                            }
                            if($mappingFields['fhirField']=="issuer")
                            {
                                $institutionRefId="Organization/";
                                if($configs['transformQualificationInstitutionNameToId']){
                                    $institutionRefId.=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $value));
                                }
                                else{
                                    $institutionRefId.=$value;
                                }
                                $builtInQualification[$indexQual][$mappingFields['fhirField']]=array('reference' =>$institutionRefId);
                            }
                            if($mappingFields['fhirField']=="identifier.value")
                            {
                                $builtInQualification[$indexQual]['identifier'][0]=array('value'=>$value);
                            }
                            if($mappingFields['fhirField']=="period.start")
                            {   
                                $builtInQualification[$indexQual]['period']['start']=(new DateTime($value))->format('c');
                            }
                            if($mappingFields['fhirField']=="period.end")
                            {   
                                $builtInQualification[$indexQual]['period']['end']=(new DateTime($value))->format('c');
                            }
                            if($mappingFields['fhirField']=="code")
                            {   
                                $builtInQualification[$indexQual][$mappingFields['fhirField']]=array('text'=>$value);
                            }
                            if($fieldHasExtension){
                                if(!array_key_exists('extension',$builtInQualification[$indexQual]))
                                {
                                    $builtInQualification[$indexQual]['extension']=array();
                                    $builtInQualification[$indexQual]['extension'][]=$fieldExtension;
                                }
                                
                            }
                        }
                        if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="communication")
                        {
                            if(!array_key_exists($indexQual,$builtInCommunication))
                            {
                                $builtInCommunication[$indexQual]=array();
                            }
                            if($mappingFields['fhirField']=="text")
                            {   
                                $builtInCommunication[$indexQual][$mappingFields['fhirField']]=$value;
                            }
                        }
                    }
                }
            }
        }
        
    }
    protected function extractPractitionerRoleLinkedData($listMappingFields,$listExtensionMappingFields,$configs,$linkedData,
        &$builtInIdentifier,&$builtInContactPoint)
    {
        $indexId="";
        $indexCPt="";
        $indexAdd="";
        $indexQual="";
        foreach($linkedData as $keyForm=>$dataForms)
        {
            foreach($dataForms as $indexLinked=>$dataValue){
                $indexId=$keyForm.$indexLinked;
                $indexCPt=$keyForm.$indexLinked;
                $indexAdd=$keyForm.$indexLinked;
                $indexQual=$keyForm.$indexLinked;
                foreach($dataValue as $key=>$value)
                {
                    $fieldExtension=array();
                    $fieldHasExtension=false;
                    foreach($listMappingFields as $keyIndex=>$mappingFields)
                    {
                        if($key==$mappingFields['iHRISFieldAlias'])
                        {
                            $this->extractFieldExtension($listExtensionMappingFields,$mappingFields,$configs,$dataValue,$fieldExtension,
                                $fieldHasExtension); 
                        }
                        if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="identifier"){
                            if(!array_key_exists($indexId,$builtInIdentifier))
                            {
                                $builtInIdentifier[$indexId]=array();
                            }
                            if($mappingFields['fhirField']=="system")
                            {
                                $builtInIdentifier[$indexId]['system']=$configs['identifierSystemUrl'].$value;
                            }
                            elseif($mappingFields['fhirField']=="value"){
                                $builtInIdentifier[$indexId]['value']=$value;
                            }
                            elseif($mappingFields['fhirField']=="period.start"){
                                $builtInIdentifier[$indexId]['period']['start']= (new DateTime($value))->format('c');
                            }
                            elseif($mappingFields['fhirField']=="period.end"){
                                $builtInIdentifier[$indexId]['period']['end']=(new DateTime($value))->format('c');;
                            }
                            elseif($mappingFields['fhirField']=="assigner"){
                                $builtInIdentifier[$indexId]['assigner']=$value;
                            } 
                            if($fieldHasExtension){
                                if(!array_key_exists('extension',$builtInIdentifier[$indexId]))
                                {
                                    $builtInIdentifier[$indexId]['extension']=array();
                                    $builtInIdentifier[$indexId]['extension'][]=$fieldExtension;
                                }
                                
                            }

                        }
                        if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="telecom"){
                            if(!array_key_exists($indexCPt,$builtInContactPoint))
                            {
                                $builtInContactPoint[$indexCPt]=array();
                            }
                            $telecomKey=explode('_',$mappingFields['iHRISFieldAlias']);
                            if(sizeof($telecomKey)<=1)
                            {
                                if(!array_key_exists('default',$builtInContactPoint[$indexCPt])){
                                    $builtInContactPoint[$indexCPt]['default']=array();
                                }
                                $builtInContactPoint[$indexCPt]['default']['use']='home';
                                if(in_array($telecomKey[0],array('phone','fax','email','pager','url','sms')) ){
                                    $builtInContactPoint[$indexCPt]['default']['system']=$telecomKey[0];
                                }
                                else
                                {
                                    $builtInContactPoint[$indexCPt]['default']['system']="other";
                                }
                                
                                $builtInContactPoint[$indexCPt]['default']['value']=$value;
                                if($fieldHasExtension){
                                    if(!array_key_exists('extension',$builtInContactPoint[$indexCPt]['default']))
                                    {
                                        $builtInContactPoint[$indexCPt]['default']['extension']=array();
                                        $builtInContactPoint[$indexCPt]['default']['extension'][]=$fieldExtension;
                                    }
                                }
                            }
                            else{
                                if(!array_key_exists($telecomKey[0],$builtInContactPoint[$indexCPt])){
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]=array();
                                }
                                if(in_array($telecomKey[0],array('home','work','mobile')) )
                                {
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]['use']=$telecomKey[0];
                                }
                                else{
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]['use']='temp';
                                }
                                if(in_array($telecomKey[1],array('phone','fax','email','pager','url','sms')) ){
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]['system']=$telecomKey[1];
                                }
                                else
                                {
                                    $builtInContactPoint[$indexCPt][$telecomKey[0]]['system']="other";
                                }
                                
                                $builtInContactPoint[$indexCPt][$telecomKey[0]]['value']=$value;
                                if($fieldHasExtension){
                                    if(!array_key_exists('extension',$builtInContactPoint[$indexCPt][$telecomKey[0]]))
                                    {
                                        $builtInContactPoint[$indexCPt][$telecomKey[0]]['extension']=array();
                                        $builtInContactPoint[$indexCPt][$telecomKey[0]]['extension'][]=$fieldExtension;
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }
        
    }
    /**
     * create organization given an array
     * 
     */
    protected function create_Organization( $data, &$top ) {
        if ( $this-> useJSON ) {
            
            $top['id'] = $data['uuid'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }
            $globalIdSystem=null;
            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $globalIdSystem= $data['id_system'];
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }
            if ( array_key_exists( 'active', $data ) ) {
                $top['active'] = ( $data['active'] == 'true' );
            }
            if( array_key_exists( 'name', $data ) ) {
                $top['name'] = $data['name'];
            }
            if(array_key_exists( 'type', $data )){
                $top['type']=array(array('text'=>$data['type']));
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

        }
        else {
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
        }
    }
    protected function createMapping_Organization( $data,$linkedData,&$top,$listMappingFields,$listExtensionMappingFields,$configs ) {
    $top['identifier'] = array();
    $top['type'] = array();
    $top['extension']=array();
    $telecom=array();
    $oIdentifier=array();
    $type=array();
    $specialty=array();
    /* echo "\n--------------------data -------------------------\n";
    print_r($data);
    echo "\n--------------------metadata -------------------------\n"; */
    //print_r($listMappingFields);
    $oIdentifier['system']=$configs['identifierSystemUrl'];
    foreach($listMappingFields as $keyIndex=>$mappingFields)
    {
        foreach($data as $key=>$value)
        {
            $fieldExtension=array();
            $fieldHasExtension=false;
            if($key==$mappingFields['iHRISFieldAlias'])
            {
                $this->extractFieldExtension($listExtensionMappingFields,$mappingFields,$configs,$data,$fieldExtension,
                $fieldHasExtension); 
            }
            if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="Organization")
            {
                if(in_array($key,explode(',',$configs['transformFieldToResourceID']['iHRISFieldAlias']))){
                    $value=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $value));
                }
                
                if(array_key_exists('extension',$mappingFields)){
                    switch($mappingFields['extension']['fieldType'])
                    {
                        case"integer":
                            $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueInteger'=>$value);
                        break;
                        case"string":
                            $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueString'=>$value);
                        break;
                        case"date":
                            $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueDate'=> (new DateTime($value))->format("Y-m-d"));
                        break;
                        case"dateTime":
                            $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueDateTime'=>(new DateTime($value))->format("c"));
                        break;
                        case "code":
                            $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueCoding'=>array("code"=>$value));
                        break;
                        default:
                        $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueString'=>$value);
                    }
                    //$oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],$dicValue);
                    $top['extension'][]=$oExtension;
                }
                else
                {
                   
                    if($mappingFields['fhirField']=="type.coding")
                    {
                        $type['coding']=array();
                        $type['coding'][]=array('system'=>$configs['identifierSystemUrl'],'code'=>$value);
                    }
                    elseif($mappingFields['fhirField']=="type.text")
                    {
                        $type['text']=$value;
                    }
                    elseif($mappingFields['fhirField']=="id"){
                        $top['fullURL']=$this->getSiteBase() . "Organization/" .$value;
                        $top[$mappingFields['fhirField']]=$value;
                        
                    }
                    else
                    {
                        $top[$mappingFields['fhirField']]=$value;
                    }
                    
                }
            }
            if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="identifier")
            {
                if($mappingFields['fhirField']=="system")
                {
                    $oIdentifier['system']=$configs['identifierSystemUrl'].$value;
                }
                elseif($mappingFields['fhirField']=="value"){
                    $oIdentifier['value']=$value;
                }
                elseif($mappingFields['fhirField']=="period.start"){
                    $oIdentifier['period']['start']= (new DateTime($value))->format('c');
                }
                elseif($mappingFields['fhirField']=="period.end"){
                    $oIdentifier['period']['end']=(new DateTime($value))->format('c');;
                }
                elseif($mappingFields['fhirField']=="assigner"){
                    $oIdentifier['assigner']=$value;
                }
                if($fieldHasExtension){
                    if(!array_key_exists('extension',$oIdentifier))
                    {
                        $oIdentifier['extension']=array();
                    }
                    $oIdentifier['extension'][]=$fieldExtension;
                }
            }
            if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="telecom")
            {
                $telecomKey=explode('_',$mappingFields['iHRISFieldAlias']);
                if(sizeof($telecomKey)<=1)
                {
                    if(!array_key_exists('default',$telecom)){
                        $telecom['default']=array();
                    }
                    $telecom['default']['use']='home';
                    if(in_array($telecomKey[0],array('phone','fax','email','pager','url','sms')) ){
                        $telecom['default']['system']=$telecomKey[0];
                    }
                    else
                    {
                        $telecom['default']['system']="other";
                    }
                    
                    $telecom['default']['value']=$value;
                    if($fieldHasExtension){
                        if(!array_key_exists('extension',$telecom['default']))
                        {
                            $telecom['default']['extension']=array();
                        }
                        $telecom['default']['extension'][]=$fieldExtension;
                    }
                    
                }
                else
                {
                    if(!array_key_exists($telecomKey[0],$telecom)){
                        $telecom[$telecomKey[0]]=array();
                    }
                    if(in_array($telecomKey[0],array('home','work','mobile')) )
                    {
                        $telecom[$telecomKey[0]]['use']=$telecomKey[0];
                    }
                    else{
                        $telecom[$telecomKey[0]]['use']='temp';
                    }
                    if(in_array($telecomKey[1],array('phone','fax','email','pager','url','sms')) ){
                        $telecom[$telecomKey[0]]['system']=$telecomKey[1];
                    }
                    else
                    {
                        $telecom[$telecomKey[0]]['system']="other";
                    }
                    
                    $telecom[$telecomKey[0]]['value']=$value;
                    
                    if($fieldHasExtension){
                        if(!array_key_exists('extension',$telecom[$telecomKey[0]]))
                        {
                            $telecom[$telecomKey[0]]['extension']=array();
                        }
                        $telecom[$telecomKey[0]]['extension'][]=$fieldExtension;
                    }
                    
                }
                
                
            }

        }
        
    }
    if($oIdentifier['value'])
    {
        array_push($top['identifier'],$oIdentifier);
    }
    if(!empty($job))
    {
        $top['type'][]=$type;
    }
    foreach($telecom as $key=>$contactPoint)
    {
        $top['telecom'][]=$contactPoint;
    }
    $identifierLinked=array();
    $contactPointLinked=array();
    //$indexLinked=0;
   $this->extractPractitionerRoleLinkedData($listMappingFields,$listExtensionMappingFields,$configs,$linkedData,
       $identifierLinked,$contactPointLinked);
    if(!empty($identifierLinked))
    {
        foreach($identifierLinked as $key=>$identifier)
        {
            //array_push($top['identifier'],$identifier);
            $top['identifier'][]=$identifier;
        }
    } 
    if(!empty($contactPointLinked))
     {
        foreach($contactPointLinked as $key=>$oTelecom)
        {
            array_push($top['telecom'],$oTelecom);
        }
     }
    $practitionerRoleGroupExtension=array();
    $this->extractOrganizationGroupExtensions($listExtensionMappingFields,$listMappingFields,$configs,$data,$linkedData,
        $practitionerRoleGroupExtension);
    if(!empty($practitionerRoleGroupExtension)){
        foreach($practitionerRoleGroupExtension as $key=>$oExtension)
        {
            if($oExtension){
                $top['extension'][]=$oExtension;
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
            $geos = array( 'county' => 'district', 'district' => 'region', 'region' => 'country', 'country' => null);
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
            $data['mode'] = 'instance';
            $data['id_system'] = $this->getSiteBase();
            $data['id_code'] = $facility->getNameId();
            $data['type_system'] = $this->getSiteBase();
            $data['type'] = $facility->getField('facility_type')->getDBValue();
            $data['type_text'] = $facility->getField('facility_type')->getDisplayValue();

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


        $required_forms = array( "facility", "facility_type", "county", "district", "region", "country" );
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }

        $queries = array( 
                'facility' => "SELECT facility.csd_uuid as uuid, DATE_FORMAT(facility.last_modified, '%Y-%m-%d %T') AS lastupdated, facility.id, facility.name, facility.facility_type, ft.name AS facility_type_name, IFNULL(county.csd_uuid, IFNULL(district.csd_uuid, IFNULL(region.csd_uuid, country.csd_uuid))) AS partof FROM hippo_facility AS facility LEFT JOIN hippo_county AS county ON county.id = facility.location LEFT JOIN hippo_district AS district ON district.id = facility.location LEFT JOIN hippo_region AS region ON region.id = facility.location LEFT JOIN hippo_country AS country ON country.id = facility.location LEFT JOIN hippo_facility_type ft ON ft.id = facility.facility_type",
                'county' => "SELECT county.csd_uuid as uuid, DATE_FORMAT(county.last_modified, '%Y-%m-%d %T') AS lastupdated, county.id, county.name, 'na' AS facility_type, 'na' AS facility_type_name, district.csd_uuid AS partof FROM hippo_county AS county LEFT JOIN hippo_district AS district ON district.id = county.district",
                'district' => "SELECT district.csd_uuid as uuid, DATE_FORMAT(district.last_modified, '%Y-%m-%d %T') AS lastupdated, district.id, district.name, 'na' AS facility_type, 'na' AS facility_type_name, region.csd_uuid AS partof FROM hippo_district AS district LEFT JOIN hippo_region AS region ON region.id = district.region",
                'region' => "SELECT region.csd_uuid as uuid, DATE_FORMAT(region.last_modified, '%Y-%m-%d %T') AS lastupdated, region.id, region.name, 'na' AS facility_type, 'na' AS facility_type_name, country.csd_uuid AS partof FROM hippo_region AS region LEFT JOIN hippo_country AS country ON country.id = region.country",
                'country' => "SELECT country.csd_uuid as uuid, DATE_FORMAT(country.last_modified, '%Y-%m-%d %T') AS lastupdated, country.id, country.name, 'na' AS facility_type, 'na' AS facility_type_name, null AS partof FROM hippo_country AS country",
                );

        $qry = '';
        $params = array();
        foreach ($queries as $main => $query) {
            $where = '';
            if ( $this->since ) {
                $where = "WHERE $main.last_modified >= ?";
                $params[] = $this->since;
            }
            $query .= " $where";
            $finals[] = $query;
        }
        $qry = implode( ' UNION ', $finals );
        $qry .= " ORDER BY lastupdated ASC";

        try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            $count = 0;
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            while ( $row = $stmt->fetch() ) {
                
                $data = array();
                $locationUuid=null;
                if(!$row->uuid)
                {
                    
                    //$locationUuid=$row->uuid;
                    continue;
                }
                /* else{
                    $locationUuid=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $row->id));;
                } */
                $count++;
                $entry = $this->doc->createElement("entry");
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Location/" .$row->uuid;
                    $entry['resource'] = array( 'resourceType' => 'Location' );
                } else {
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Location/" . $row->uuid );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $location = $this->doc->createElement( "Location" );
                }
                $data['uuid'] = $row->uuid;
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row->id;
                $data['status'] = 'active';
                $data['mode'] = 'instance';
                $data['name'] = $row->name;

                if ( $row->facility_type == 'na' ) {
                    $data['physicalType_system'] = 'http://hl7.org/fhir/location-physical-type';
                    $data['physicalType'] = 'jdn';
                    $data['type_text'] = 'Geographic Jurisdiction';
                } else {
                    $data['type_system'] = $this->getSiteBase();
                    $data['type'] = $row->facility_type;
                    $data['type_text'] = $row->facility_type_name;

                    $data['physicalType_system'] = 'http://hl7.org/fhir/location-physical-type';
                    $data['physicalType'] = 'bu';
                }

                if ( $row->partof ) {
                    //$data['partOf'] = $this->getSiteBase() . "Location/" . $row->partof;
                    $data['partOf'] = "Location/" . $row->partof;
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
            $stmt->closeCursor();
            unset( $stmt );
            return true;
        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
            http_response_code(500);
            return false;
        }
    }
    protected function getUpdatesMapping_Location( &$top ) {
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


        $required_forms = array( "facility", "facility_type", "county", "district", "region", "country",'health_area' );
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }
        $listConfigs=array();
        $this->getConfigData($listConfigs);
        $limitFormRequest='';

        if($listConfigs["limitLocationForm"]['status'] ==true)
        {
            $limitFormRequest.=$listConfigs["limitLocationForm"]['form'].".".$listConfigs["limitLocationForm"]['fieldName']."='".$listConfigs["limitLocationForm"]['value']."'";
        }

        /* $queries = array( 
                'facility' => "SELECT facility.csd_uuid as uuid, DATE_FORMAT(facility.last_modified, '%Y-%m-%d %T') AS lastupdated, facility.id, facility.name, facility.facility_type, ft.name AS facility_type_name, IFNULL(county.csd_uuid, IFNULL(district.csd_uuid, IFNULL(region.csd_uuid, country.csd_uuid))) AS partof FROM hippo_facility AS facility LEFT JOIN hippo_county AS county ON county.id = facility.location LEFT JOIN hippo_district AS district ON district.id = facility.location LEFT JOIN hippo_region AS region ON region.id = facility.location LEFT JOIN hippo_country AS country ON country.id = facility.location LEFT JOIN hippo_facility_type ft ON ft.id = facility.facility_type",
                'county' => "SELECT county.csd_uuid as uuid, DATE_FORMAT(county.last_modified, '%Y-%m-%d %T') AS lastupdated, county.id, county.name, 'na' AS facility_type, 'na' AS facility_type_name, district.csd_uuid AS partof FROM hippo_county AS county LEFT JOIN hippo_district AS district ON district.id = county.district",
                'district' => "SELECT district.csd_uuid as uuid, DATE_FORMAT(district.last_modified, '%Y-%m-%d %T') AS lastupdated, district.id, district.name, 'na' AS facility_type, 'na' AS facility_type_name, region.csd_uuid AS partof FROM hippo_district AS district LEFT JOIN hippo_region AS region ON region.id = district.region",
                'region' => "SELECT region.csd_uuid as uuid, DATE_FORMAT(region.last_modified, '%Y-%m-%d %T') AS lastupdated, region.id, region.name, 'na' AS facility_type, 'na' AS facility_type_name, country.csd_uuid AS partof FROM hippo_region AS region LEFT JOIN hippo_country AS country ON country.id = region.country",
                'country' => "SELECT country.csd_uuid as uuid, DATE_FORMAT(country.last_modified, '%Y-%m-%d %T') AS lastupdated, country.id, country.name, 'na' AS facility_type, 'na' AS facility_type_name, null AS partof FROM hippo_country AS country",
                ); */
        /** Return facility by geographic area. specification for DRC facilities list structure */
        $queries = array( 
            'facility_healtharea' => "SELECT facility.csd_uuid as uuid, DATE_FORMAT(facility.last_modified, '%Y-%m-%d %T') AS lastupdated, facility.id, facility.name,facility.facility_type, ft.name AS facility_type_name, replace(replace(health_area.id,'|','-'),'_','-') AS partof FROM hippo_facility AS facility JOIN hippo_health_area as health_area on health_area.id=facility.location JOIN hippo_county AS county ON county.id = health_area.county JOIN hippo_district AS district ON district.id = county.district JOIN hippo_region AS region ON region.id = district.region JOIN hippo_country AS country ON country.id = region.country LEFT JOIN hippo_facility_type ft ON ft.id = facility.facility_type ",
            'facility_county'=>"SELECT facility.csd_uuid as uuid, DATE_FORMAT(facility.last_modified, '%Y-%m-%d %T') AS lastupdated, facility.id, facility.name, facility.facility_type, ft.name AS facility_type_name, IF(length(county.csd_uuid)=0,replace(replace(county.id,'|','-'),'_','-'),county.csd_uuid) AS partof FROM hippo_facility AS facility JOIN hippo_county AS county ON county.id = facility.location JOIN hippo_district AS district ON district.id = county.district JOIN hippo_region AS region ON region.id = district.region JOIN hippo_country AS country ON country.id = region.country LEFT JOIN hippo_facility_type ft ON ft.id = facility.facility_type ",
            'facility_district'=>"SELECT facility.csd_uuid as uuid, DATE_FORMAT(facility.last_modified, '%Y-%m-%d %T') AS lastupdated, facility.id, facility.name, facility.facility_type, ft.name AS facility_type_name, district.csd_uuid AS partof FROM hippo_facility AS facility JOIN hippo_district AS district ON district.id = facility.location JOIN hippo_region AS region ON region.id = district.region JOIN hippo_country AS country ON country.id = region.country LEFT JOIN hippo_facility_type ft ON ft.id = facility.facility_type ",
            'health_area'=>"SELECT replace(replace(health_area.id,'|','-'),'_','-')  as uuid, DATE_FORMAT(health_area.last_modified, '%Y-%m-%d %T') AS lastupdated, county.id, health_area.name, 'na' AS facility_type, 'na' AS facility_type_name,IF(length(county.csd_uuid)=0,replace(replace(county.id,'|','-'),'_','-'),county.csd_uuid) AS partof FROM hippo_health_area AS health_area LEFT JOIN hippo_county AS county ON county.id = health_area.county",
            'county' => "SELECT IF(length(county.csd_uuid)=0,replace(replace(county.id,'|','-'),'_','-'),county.csd_uuid) as uuid, DATE_FORMAT(county.last_modified, '%Y-%m-%d %T') AS lastupdated, county.id, county.name, 'na' AS facility_type, 'na' AS facility_type_name, district.csd_uuid AS partof FROM hippo_county AS county LEFT JOIN hippo_district AS district ON district.id = county.district",
            'district' => "SELECT district.csd_uuid as uuid, DATE_FORMAT(district.last_modified, '%Y-%m-%d %T') AS lastupdated, district.id, district.name, 'na' AS facility_type, 'na' AS facility_type_name, region.csd_uuid AS partof FROM hippo_district AS district LEFT JOIN hippo_region AS region ON region.id = district.region",
            'region' => "SELECT region.csd_uuid as uuid, DATE_FORMAT(region.last_modified, '%Y-%m-%d %T') AS lastupdated, region.id, region.name, 'na' AS facility_type, 'na' AS facility_type_name, country.csd_uuid AS partof FROM hippo_region AS region LEFT JOIN hippo_country AS country ON country.id = region.country",
            'country' => "SELECT country.csd_uuid as uuid, DATE_FORMAT(country.last_modified, '%Y-%m-%d %T') AS lastupdated, country.id, country.name, 'na' AS facility_type, 'na' AS facility_type_name, null AS partof FROM hippo_country AS country",
            );
        $qry = '';
        $params = array();
        foreach ($queries as $main => $query) {
            $where = '';
            if ( $this->since ) {
                $mainForm=explode("_",$main);
                if($main=='facility_healtharea')
                {
                    $where = "WHERE ".$mainForm[0].".last_modified >= ? AND ".$limitFormRequest;
                }
                else
                {
                    $where = "WHERE ".$mainForm[0].".last_modified >= ?";
                }
                
                $params[] = $this->since;
            }
            else
            {
                if($main=='facility_healtharea')
                {
                    $where = "WHERE ".$limitFormRequest;
                }
            }
            
            $query .= " $where";
            $finals[] = $query;
        }
        $qry = implode( ' UNION ', $finals );
        $qry .= " ORDER BY lastupdated ASC";
        //print_r($finals);
        //return array('result'=>"ok"); 
        try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            $count = 0;
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            while ( $row = $stmt->fetch() ) {
                
                $data = array();
                $locationUuid=null;
                if(!$row->uuid)
                {
                    
                    //$locationUuid=$row->uuid;
                    continue;
                }
                /* else{
                    $locationUuid=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $row->id));;
                } */
                $count++;
                $entry = $this->doc->createElement("entry");
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Location/" .$row->uuid;
                    $entry['resource'] = array( 'resourceType' => 'Location' );
                } else {
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "FHIR/Location/" . $row->uuid );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $location = $this->doc->createElement( "Location" );
                }
                $data['uuid'] = $row->uuid;
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row->id;
                $data['status'] = 'active';
                $data['mode'] = 'instance';
                $data['name'] = $row->name;

                if ( $row->facility_type == 'na' ) {
                    $data['physicalType_system'] = 'http://hl7.org/fhir/location-physical-type';
                    $data['physicalType'] = 'jdn';
                    $data['type_text'] = 'Geographic Jurisdiction';
                } else {
                    $data['type_system'] = $this->getSiteBase();
                    $data['type'] = $row->facility_type;
                    $data['type_text'] = $row->facility_type_name;

                    $data['physicalType_system'] = 'http://hl7.org/fhir/location-physical-type';
                    $data['physicalType'] = 'bu';
                }

                if ( $row->partof ) {
                    //$data['partOf'] = $this->getSiteBase() . "Location/" . $row->partof;
                    $data['partOf'] = "Location/" . $row->partof;
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
            $stmt->closeCursor();
            unset( $stmt );
            return true;
        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
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

            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }

            if ( array_key_exists( 'status', $data ) ) {
                $top['status'] = $data['status'];
            }

            if ( array_key_exists( 'name', $data ) ) {
                $top['name'] = $data['name'];
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

            $data['uuid'] = $uuid;
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

            //add additional informations on the positions
            $data['affectation']=array();
            if($pers_pos->date_affectation)
            {
                $data['affectation']['dateAffectation']=$pers_pos->getField('date_affectation')->getValue()->getDateTimeObj()->format('c');
            }
            if($pers_pos->num_acte)
            {
                $data['affectation']['numeroActe']= $pers_pos->num_acte;
            }
            if($pers_pos->type_acte){
                $data['affectation']['typeActe']= $pers_pos->getField('type_acte')->getDisplayValue();
            }

            //extract salary information
            $pers_pos->populateChildren('salary');
            $salary = current($person->getChildren('salary'));
            if($salary)
            {
                $data['salary']=array();
                if($salary->start_date)
                {
                    $data['salary']['startDate'] = $salary->start_date;
                }
                if($salary->end_date)
                {
                    $data['salary']['endDate'] = $salary->end_date;
                }
                $currency= explode('=',$salary->salary)[0];
                $amount= explode('=',$salary->salary)[1];
                $currency_form=$factory->createContainer($currency);
                $currency_form->populate();
                if($currency_form)
                {
                    if($currency_form->code){
                        $data['salary']['currencyCode']=$currency_form->code;
                        $data['salary']['amount']=$amount;
                    }
                }
            }
            $position = $factory->createContainer( $pers_pos->getField('position')->getDBValue() );
            $position->populate();

            $job = $factory->createContainer( $position->getField('job')->getDBValue() );
            $job->populate();

            $data['code_system'] = $this->getSiteBase();
            $data['code'] = $job->getNameId();
            $data['code_text'] = $job->title;

            $facility = $factory->createContainer( $position->getField('facility')->getDBValue() );
            $facility->populate();

            //$data['location'] = $this->getSiteBase() . "FHIR/Location/" . $facility->csd_uuid;
            $data['location'] = "Location/" . $facility->csd_uuid;

            $person = $factory->createContainer( $pers_pos->getParent() );
            $person->populate();

            //$data['practitioner'] = $this->getSiteBase() . "FHIR/Practitioner/" . $person->csd_uuid;
            $data['practitioner'] = "Practitioner/" . $person->csd_uuid;
            
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


        $required_forms = array( "person_position", "person", "position", "job", "facility","deployment_history","county",
            "district","salary","currency","engagement","salary_source" );
        foreach( $required_forms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }

        $where = '';
        $whereEmpHistory = '';
        $params = array();
        if ( $this->since ) {
            $where = "WHERE pp.last_modified >= ?";
            $whereEmpHistory = "WHERE deployment.last_modified >= ?";
            $params = array( $this->since );

        }

        $qry = "SELECT pp.csd_uuid AS uuid, DATE_FORMAT(pp.last_modified, '%Y-%m-%d %T') AS lastupdated, pp.id,  
        DATE_FORMAT(pp.start_date, '%Y-%m-%d') as start_date, 
        position.source as salary_source,position_type.name as position_type,
        DATE_FORMAT(pp.date_affectation, '%Y-%m-%d') AS date_affectation,pp.num_acte,affectation.name as affectation,
        DATE_FORMAT(pp.end_date, '%Y-%m-%d') as end_date, job.id AS code, job.title AS code_text, person.csd_uuid AS practitioner, facility.csd_uuid AS location,
        DATE_FORMAT(salary.start_date,'%Y-%m-%d') as salary_start_date,DATE_FORMAT(salary.end_date,'%Y-%m-%d') as salary_end_date,SUBSTRING_INDEX(salary.salary,'=',-1) as salary,currency.code as currency_code
            FROM hippo_person_position AS pp 
            LEFT JOIN hippo_position position ON position.id = pp.position
            LEFT JOIN hippo_position_type as position_type ON position_type.id = position.pos_type
            LEFT JOIN hippo_type_affect AS affectation ON affectation.id = pp.type_acte 
            LEFT JOIN hippo_job AS job ON job.id = position.job 
            LEFT JOIN hippo_facility AS facility ON facility.id = position.facility 
            LEFT JOIN hippo_salary AS salary ON salary.parent = pp.id
            LEFT JOIN hippo_currency As currency on currency.id=SUBSTRING_INDEX(salary.salary,'=',1)
            LEFT JOIN hippo_person AS person ON person.id = pp.parent $where ORDER BY lastupdated ASC";

        //query for extracting deployment history
        $qryEmploymentHistory = "SELECT deployment.id as uuid,DATE_FORMAT(deployment.last_modified, '%Y-%m-%d %T') AS lastupdated, 
        DATE_FORMAT(deployment.start_date, '%Y-%m-%d') AS start_date, DATE_FORMAT(deployment.end_date, '%Y-%m-%d') AS end_date,
        deployment.facility,deployment.post as position,county.csd_uuid as county_location,district.csd_uuid as district_location,
        person.csd_uuid as practitioner
        from hippo_deployment_history AS deployment
        LEFT JOIN hippo_county  AS county ON county.id = deployment.location
        LEFT JOIN hippo_district  AS district ON district.id = deployment.location 
        LEFT JOIN hippo_person AS person ON person.id = deployment.parent
        $whereEmpHistory ORDER BY lastupdated ASC";

        try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            $count = 0;
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            
            while ( $row = $stmt->fetch() ) {
                $data = array();
                $count++;
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "PractitionerRole/" . $row->uuid;
                    $entry['resource'] = array( 'resourceType' => 'PractitionerRole' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "PractitionerRole/" . $row->uuid );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $role = $this->doc->createElement( "PractitionerRole" );
                }


                $data['uuid'] = $row->uuid;
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row->id;
                if($row->start_date && $row->start_date!="0000-00-00")
                {
                    $data['start_date'] = $row->start_date;
                }
                //$data['start_date'] = substr($row->start_date, 0, 10);
                if ( !$row->end_date || $row->end_date == '0000-00-00' ) {
                    $data['active'] = 'true';
                } else { 
                    $data['end_date'] = $row->end_date;
                    $data['active'] = 'false';
                }
                $data['code_system'] = $this->getSiteBase();
                $data['code'] = $row->code;
                $data['code_text'] = $row->code_text;
                
                //add additional informations on the positions
                $data['extension']=array();
                //add salary information
                if($row->salary)
                {
                    $counter=0;
                    $data['extension']['iHRISPractitionerRolesSalary'][ $counter]=array();
                    if($row->salary_start_date && $row->salary_start_date!="0000-00-00")
                    {
                        $data['extension']['iHRISPractitionerRolesSalary'][$counter]['date_startDate'] = $row->salary_start_date;
                    }
                    if($row->salary_end_date && $row->salary_end_date!=='0000-00-00')
                    {
                        $data['extension']['iHRISPractitionerRolesSalary'][$counter]['date_endDate'] = $row->salary_end_date;
                    }
                    if($row->currency_code){
                        $data['extension']['iHRISPractitionerRolesSalary'][$counter]['code_currencyCode'] = $row->currency_code;
                    }
                    $data['extension']['iHRISPractitionerRolesSalary'][$counter]['value_amount'] = $row->salary;
                    if($row->salary_source)
                    {
                        $paramsSub = array();
                        $paramsSub =explode(',',$row->salary_source);
                        $queryParam="(";
                        $iterator=0;
                        foreach($paramsSub as $sourceId)
                        {
                            if($iterator==0)
                            {
                                //$queryParam.=$sourceId;
                                $queryParam.='?';
                            }
                            else{
                                //$queryParam.=",".$sourceId;
                                $queryParam.=',?';
                            }
                            $iterator++;
                            
                        }
                        $queryParam.=")";
                        $subQuery="SELECT  salary_source.name FROM hippo_salary_source as salary_source where id IN ";
                        $subQuery.= $queryParam;
                        //print_r($subQuery);
                        $subStmt = $db->prepare( $subQuery );
                        $subStmt->execute($paramsSub);
                        while ( $subRow = $subStmt->fetch() ) {
                            //$data['extension']['codeSalarySource'][]=$subRow->name;
                            $data['extension']['iHRISPractitionerRolesSalary'][$counter]['code_salarySource'][] = $subRow->name;
                        }
                        $subStmt->closeCursor();
                        unset( $subStmt);
                    }
                }
                $counter=0;
                $data['extension']['iHRISPractitionerRolesDetails'][ $counter]=array();
                if($row->date_affectation && $row->date_affectation !='0000-00-00')
                {
                    //$tempDateTime = new DateTime($row->date_affectation);
                    $data['extension']['iHRISPractitionerRolesDetails'][$counter]['date_dateAffectation'] =$row->date_affectation;
                }
                if($row->num_acte)
                {
                    $data['extension']['iHRISPractitionerRolesDetails'][$counter]['value_valueActe'] =$row->num_acte;
                }
                if($row->affectation)
                {
                    //$data['extension']['codeActe']=$row->affectation;
                    $data['extension']['iHRISPractitionerRolesDetails'][$counter]['code_Acte'] =$row->affectation;
                }
                if($row->position_type)
                {
                    //$data['extension']['codePositionType']=$row->position_type;
                    $data['extension']['iHRISPractitionerRolesDetails'][$counter]['code_typePosition'] =$row->position_type;
                }
                
                
                //$data['location'] = $this->getSiteBase() . "FHIR/Location/" . $row->location;
                $data['location'] = "Location/" . $row->location;
                //$data['practitioner'] = $this->getSiteBase() . "FHIR/Location/" . $row->practitioner;
                $data['practitioner'] = "Practitioner/" . $row->practitioner;

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
            $stmt->closeCursor();
            unset( $stmt );
            //query for extracting deployment history
            $stmt = $db->prepare( $qryEmploymentHistory );
            $stmt->execute( $params );
            while ( $row = $stmt->fetch() ) {
                $data=array();
                $count++;
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "PractitionerRole/" . $row->uuid;
                    $entry['resource'] = array( 'resourceType' => 'PractitionerRole' );
                } else {
                    $entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    $fullURL->setAttribute( 'value', $this->getSiteBase() . "PractitionerRole/" . $row->uuid );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $role = $this->doc->createElement( "PractitionerRole" );
                }


                $data['uuid'] = $row->uuid;
                $lastUpdated = new DateTime($row->lastupdated);
                $data['lastUpdated'] = $lastUpdated->format('c');
                $data['id_system'] = $this->getSiteBase();
                $data['id_code'] = $row->uuid;
                $data['start_date'] = substr($row->start_date, 0, 10);
                $data['end_date'] = substr($row->end_date, 0, 10);
                $data['active'] = 'false';
                $data['code_system'] = $this->getSiteBase();
                $data['code'] = $row->position;
                $data['code_text'] = $row->position;
                $data['extension']=array();
                if($row->facility)
                {
                    //$data['extension']['OrganizationName']=$row->facility;
                    $organizationRefId=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $row->facility));
                    $data['organization']="Organization/".$organizationRefId;
                }
                //$data['location'] = $this->getSiteBase() . "FHIR/Location/" . $row->location;
                if(isset($row->county_location))
                {
                    $data['location'] = "Location/" . $row->county_location;
                }
                elseif(isset($row->district_location))
                {
                    $data['location'] = "Location/" . $row->district_location;
                }
                //$data['practitioner'] = $this->getSiteBase() . "FHIR/Location/" . $row->practitioner;
                $data['practitioner'] = "Practitioner/" . $row->practitioner;

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
            $stmt->closeCursor();
            unset( $stmt );
            if ( $this->useJSON ) {
                $top['total'] = $count;
            } else {
                $total->setAttribute('value', $count);
            }
           
            return true;
        } catch( PDOException $e ) {
            I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
            http_response_code(500);
            return false;
        }
    }
    protected function getUpdatesMapping_PractitionerRole( &$top ) {
    
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
        
        $mainRequestPractitionerRole=array();
        $LinkedFormRequest=array();
        $listForms=array();
        $listMappingFields=array();
        $listExtensionMappingFields=array();
        $listConfigs=array();
        $this->buidMainRequest_PractitionerRole($mainRequestPractitionerRole,$LinkedFormRequest,$listForms,$listMappingFields,
        $listExtensionMappingFields,$listConfigs);
        foreach( $listForms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }
        //print_r($mainRequestPractitionerRole);
        if ( $this->useJSON ) {
            $top['entry'] = array();
        }
        $count = 0;
        /* print_r($mainRequestPractitionerRole[0]);
        return array('result'=>"ok"); */
        foreach($mainRequestPractitionerRole as $indexReq=>$mainRequest)
        {
            $params = array();
            
            if($listConfigs["limitLocationForm"]['status'] ==true && 
                $listConfigs["selectOnlyClosedPosition"]['status'] ==true)
            {
                $mainRequest.=" WHERE ".$listConfigs["limitLocationForm"]['form'].".".$listConfigs["limitLocationForm"]['fieldName']."='".$listConfigs["limitLocationForm"]['value']."'";
                $operator=$listConfigs["selectOnlyClosedPosition"]['operator'];
                switch ($operator) {
                    case "eq":
                        $mainRequest.=" AND ".$listConfigs["selectOnlyClosedPosition"]['form'].".".$listConfigs["selectOnlyClosedPosition"]['fieldName']."='".$listConfigs["selectOnlyClosedPosition"]['value']."'";
                    break;
                    case "ne":
                        $mainRequest.=" AND ".$listConfigs["selectOnlyClosedPosition"]['form'].".".$listConfigs["selectOnlyClosedPosition"]['fieldName']."!='".$listConfigs["selectOnlyClosedPosition"]['value']."'";
                    break;
                }
            }
            elseif($listConfigs["limitLocationForm"]['status'] ==true)
            {
                $mainRequest.=" WHERE ".$listConfigs["limitLocationForm"]['form'].".".$listConfigs["limitLocationForm"]['fieldName']."='".$listConfigs["limitLocationForm"]['value']."'";
            }
            elseif($listConfigs["selectOnlyClosedPosition"]['status'] ==true)
            {
                $operator=$listConfigs["selectOnlyClosedPosition"]['operator'];
                switch ($operator) {
                    case "eq":
                        $mainRequest.=" WHERE ".$listConfigs["selectOnlyClosedPosition"]['form'].".".$listConfigs["selectOnlyClosedPosition"]['fieldName']."='".$listConfigs["selectOnlyClosedPosition"]['value']."'";
                    break;
                    case "ne":
                        $mainRequest.=" WHERE ".$listConfigs["selectOnlyClosedPosition"]['form'].".".$listConfigs["selectOnlyClosedPosition"]['fieldName']."!='".$listConfigs["selectOnlyClosedPosition"]['value']."'";
                    break;
                }
            }

            $qry = $mainRequest;
            /*
            print_r($qry);
            return array('result'=>"ok");*/
            try {
                $db = I2CE::PDO();
                $stmt = $db->prepare( $qry );
                $stmt->execute( $params );

                /* if ( $this->useJSON ) {
                    $top['entry'] = array();
                } */
                while ( $row = $stmt->fetch() ) {
                    $data=array();
                    $linkedData=array();
                    $count++;
                    if ( $this->useJSON ) {
                        $entry = array();
                        //$entry['fullURL'] = $this->getSiteBase() . "PractitionerRole/" . $row->uuid;
                        $entry['resource'] = array( 'resourceType' => 'PractitionerRole' );
                        $entry['resource']['meta'] = array( 'profile' => array($listConfigs['profile']) );
                    } else {
                        //$entry = $this->doc->createElement("entry");
                        $fullURL = $this->doc->createElement("fullURL");
                        $fullURL->setAttribute( 'value', $this->getSiteBase() . "PractitionerRole/" . $row->uuid );
                        $entry->appendChild( $fullURL );
                        $resource = $this->doc->createElement("resource");
                        $role = $this->doc->createElement( "PractitionerRole" );
                    }
                    foreach($row as $key=>$value)
                    {
                        if($key==$listConfigs['removeCurrencyFromAmount']['iHRISFieldAlias']){
                            $value=explode('=',$value)[1];
                        }
                        if($value && $value!="0000-00-00 00:00:00" && $value!="0000-00-00")
                        {
                            $data[$key]=$value;
                        }
    
                        
                    }
                     //Add now Linked forms query
                     if($indexReq==0)
                     {
                        foreach($LinkedFormRequest as $keyJoinForm=>$joinFormRequest){
                            $subQuery="";
                            $paramSub=array($row->id);
                            //if($keyJoinForm =="person_id"){
                            if($keyJoinForm){
                                $subQuery=$joinFormRequest['mainFieldQuery']. $joinFormRequest['sourceFieldQuery'];
                                /* print_r($subQuery);
                                print_r("-------------------------------------"); */ 
                                $subStmt = $db->prepare( $subQuery );
                                $subStmt->execute($paramSub);
                                $id_count=0;
                                $linkedData[$keyJoinForm]=array();
                                while ( $subRow = $subStmt->fetch() ) {
                                    $linkedData[$keyJoinForm][$id_count]=array();
                                    foreach($subRow as $key=>$value)
                                    {
                                        if($value && $value!="0000-00-00 00:00:00" && $value!="0000-00-00")
                                        {
                                            $linkedData[$keyJoinForm][$id_count][$key]=$value;
                                        } 
                                    }
                                    $id_count++;
                                } 
                            }
                        }
                     }
                     
                    if ( $this->useJSON ) {
                        $this->createMapping_PractitionerRole( $data,$linkedData, $entry['resource'],$listMappingFields,
                        $listExtensionMappingFields,$listConfigs );
                        $top['entry'][] = $entry;
                        //print_r($entry);
                    } else {
                        $this->createMapping_PractitionerRole( $data,$linkedData, $entry['resource'],$listMappingFields,
                        $listExtensionMappingFields,$listConfigs );
    
                        $resource->appendChild($role);
                        $entry->appendChild( $resource );
                        $top->appendChild( $entry );
                    }
                    //print_r($data);
                    /*echo "\n---------------------------------------\n"; */
                    //Limit the result to only one record
                    //break;
    
                }
                $stmt->closeCursor();
                unset( $stmt );
                
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
                http_response_code(500);
                return false;
            }
        }
        if ( $this->useJSON ) {
            $top['total'] = $count;
        } else {
            $total->setAttribute('value', $count);
        }
        return true;
        
    }
    protected function getUpdatesMapping_ValueSet( &$top ) {
    
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
        
        $mainRequestResource=array();
        $LinkedFormRequest=array();
        $listForms=array();
        $listMappingFields=array();
        $listExtensionMappingFields=array();
        $listConfigs=array();
        $listResourceMeta=array();
        $this->buidMainRequest_Resources("ValueSet",$mainRequestResource,$LinkedFormRequest,$listForms,$listMappingFields,
        $listExtensionMappingFields,$listConfigs,$listResourceMeta);
        foreach( $listForms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }
        if ( $this->useJSON ) {
            $top['entry'] = array();
        }
        $count = 0;
        /*print_r($listConfigs);
        return array('result'=>"ok"); 
        print_r($mainRequestResource);*/
        foreach($mainRequestResource as $indexReq=>$mainRequest)
        {
            $count++;
            $params = array();
            $resourceMeta=$listResourceMeta[$indexReq];

            //print_r($resourceMeta);
            
            if($listConfigs["limitLocationForm"]['status'] ==true)
            {
                $mainRequest.=" WHERE ".$listConfigs["limitLocationForm"]['form'].".".$listConfigs["limitLocationForm"]['fieldName']."='".$listConfigs["limitLocationForm"]['value']."'";
            }

            $qry = $mainRequest;
            /*
            print_r($qry);*/
            //return array('result'=>"ok");
            try {
                $db = I2CE::PDO();
                $stmt = $db->prepare( $qry );
                $stmt->execute( $params );

                /* if ( $this->useJSON ) {
                    $top['entry'] = array();
                } */
                if ( $this->useJSON ) {
                    $entry = array();
                    //$entry['fullURL'] = $this->getSiteBase() . "PractitionerRole/" . $row->uuid;
                    $entry['resource'] = array( 'resourceType' => 'ValueSet' );
                    $entry['resource']['contact'] = array();
                    $entry['resource']['contact'][]=array('telecom'=>array(array('system'=>'url','value'=>'http://ihris.org')));
                    $entry['resource']['compose'] = array('include'=>array(array('concept'=>array())));

                    foreach($resourceMeta as $keyElement=>$metaElement)
                    {
                        if(isset($listConfigs["defaultIdentifierCodeableConcept"]) && $metaElement=="identifier-type-rdc")
                        {   
                            $defaultConcept=array(
                                'code'=>$listConfigs['defaultIdentifierCodeableConcept']['code'],
                                'display'=>$listConfigs['defaultIdentifierCodeableConcept']['display'],
                                'definition'=>$listConfigs['defaultIdentifierCodeableConcept']['display']
                            );
                            $entry['resource']['compose']['include'][0]['concept'][]=$defaultConcept;

                        }
                        if($keyElement=="profile")
                        {
                            $entry['resource']['meta']=array('profile'=>array($metaElement));
                        }
                        else
                        {
                            $entry['resource'][$keyElement]=$metaElement;
                        }

                    }
                    
                } else {
                    //$entry = $this->doc->createElement("entry");
                    $fullURL = $this->doc->createElement("fullURL");
                    //$fullURL->setAttribute( 'value', $this->getSiteBase() . "ValueSet/" . $row->uuid );
                    $entry->appendChild( $fullURL );
                    $resource = $this->doc->createElement("resource");
                    $role = $this->doc->createElement( "PractitionerRole" );
                }
                while ( $row = $stmt->fetch() ) {
                    $data=array();
                    $linkedData=array();
                    
                    
                    foreach($row as $key=>$value)
                    {
                        if($value && $value!="0000-00-00 00:00:00" && $value!="0000-00-00")
                        {
                            $data[$key]=$value;
                        }
    
                        
                    }
                     //Add now Linked forms query
                     if($indexReq==0)
                     {
                        foreach($LinkedFormRequest as $keyJoinForm=>$joinFormRequest){
                            $subQuery="";
                            $paramSub=array($row->id);
                            //if($keyJoinForm =="person_id"){
                            if($keyJoinForm){
                                $subQuery=$joinFormRequest['mainFieldQuery']. $joinFormRequest['sourceFieldQuery'];
                                /* print_r($subQuery);
                                print_r("-------------------------------------"); */ 
                                $subStmt = $db->prepare( $subQuery );
                                $subStmt->execute($paramSub);
                                $id_count=0;
                                $linkedData[$keyJoinForm]=array();
                                while ( $subRow = $subStmt->fetch() ) {
                                    $linkedData[$keyJoinForm][$id_count]=array();
                                    foreach($subRow as $key=>$value)
                                    {
                                        if($value && $value!="0000-00-00 00:00:00" && $value!="0000-00-00")
                                        {
                                            $linkedData[$keyJoinForm][$id_count][$key]=$value;
                                        } 
                                    }
                                    $id_count++;
                                } 
                            }
                        }
                     }
                     
                    if ( $this->useJSON ) {
                        $this->createMapping_ValueSet( $data,$linkedData, $entry['resource'],$listMappingFields,
                        $listExtensionMappingFields,$listConfigs );
                        //$top['entry'][] = $entry;
                        //print_r($entry);
                    } else {
                        $this->createMapping_ValueSet( $data,$linkedData, $entry['resource'],$listMappingFields,
                        $listExtensionMappingFields,$listConfigs );
    
                        $resource->appendChild($role);
                        $entry->appendChild( $resource );
                        //$top->appendChild( $entry );
                    }
                    //print_r($data);
                    /*echo "\n---------------------------------------\n"; */
                    //Limit the result to only one record
                    //break;
    
                }
                $top['entry'][] = $entry;
                $stmt->closeCursor();
                unset( $stmt );
                
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
                http_response_code(500);
                return false;
            }
        }
        if ( $this->useJSON ) {
            $top['total'] = $count;
        } else {
            $total->setAttribute('value', $count);
        }
        return true;
        
    }
    protected function getUpdatesMapping_Organization( &$top ) {
    
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
        
        $mainRequestOrganization=array();
        $LinkedFormRequest=array();
        $listForms=array();
        $listMappingFields=array();
        $listExtensionMappingFields=array();
        $listConfigs=array();
        $this->buidMainRequest_Organization($mainRequestOrganization,$LinkedFormRequest,$listForms,$listMappingFields,
        $listExtensionMappingFields,$listConfigs);
        //print_r($mainRequestOrganization);
        foreach( $listForms as $form ) {
            $cachedForm = new I2CE_CachedForm($form);
            if ( !$cachedForm->generateCachedTable() ) {
                http_response_code(500);
                return false;
            }
        }
        //print_r($mainRequestPractitionerRole);
        if ( $this->useJSON ) {
            $top['entry'] = array();
        }
        $count = 0;
        foreach($mainRequestOrganization as $indexReq=>$mainRequest)
        {
            $params = array();
            $qry = $mainRequest;
            //print_r($qry);
            try {
                $db = I2CE::PDO();
                $stmt = $db->prepare( $qry );
                $stmt->execute( $params );

                /* if ( $this->useJSON ) {
                    $top['entry'] = array();
                } */
                while ( $row = $stmt->fetch() ) {
                    $data=array();
                    $linkedData=array();
                    $count++;
                    if ( $this->useJSON ) {
                        $entry = array();
                        //$entry['fullURL'] = $this->getSiteBase() . "PractitionerRole/" . $row->uuid;
                        $entry['resource'] = array( 'resourceType' => 'Organization' );
                    } else {
                        //$entry = $this->doc->createElement("entry");
                        $fullURL = $this->doc->createElement("fullURL");
                        //$fullURL->setAttribute( 'value', $this->getSiteBase() . "Organization/" . $row->uuid );
                        $entry->appendChild( $fullURL );
                        $resource = $this->doc->createElement("resource");
                        $role = $this->doc->createElement( "Organization" );
                    }
                    foreach($row as $key=>$value)
                    {
                        if($value && $value!="0000-00-00 00:00:00" && $value!="0000-00-00")
                        {
                            $data[$key]=$value;
                        }
    
                        
                    }
                     //Add now Linked forms query
                     if($indexReq==0)
                     {
                        foreach($LinkedFormRequest as $keyJoinForm=>$joinFormRequest){
                            $subQuery="";
                            $paramSub=array($row->id);
                            //if($keyJoinForm =="person_id"){
                            if($keyJoinForm){
                                $subQuery=$joinFormRequest['mainFieldQuery']. $joinFormRequest['sourceFieldQuery'];
                                /* print_r($subQuery);
                                print_r("-------------------------------------"); */ 
                                $subStmt = $db->prepare( $subQuery );
                                $subStmt->execute($paramSub);
                                $id_count=0;
                                $linkedData[$keyJoinForm]=array();
                                while ( $subRow = $subStmt->fetch() ) {
                                    $linkedData[$keyJoinForm][$id_count]=array();
                                    foreach($subRow as $key=>$value)
                                    {
                                        if($value && $value!="0000-00-00 00:00:00" && $value!="0000-00-00")
                                        {
                                            $linkedData[$keyJoinForm][$id_count][$key]=$value;
                                        } 
                                    }
                                    $id_count++;
                                } 
                            }
                        }
                     }
                     
                    if ( $this->useJSON ) {
                        $this->createMapping_Organization( $data,$linkedData, $entry['resource'],$listMappingFields,
                        $listExtensionMappingFields,$listConfigs );
                        $top['entry'][] = $entry;
                        //print_r($entry);
                    } else {
                        $this->createMapping_Organization( $data,$linkedData, $entry['resource'],$listMappingFields,
                        $listExtensionMappingFields,$listConfigs );
    
                        $resource->appendChild($role);
                        $entry->appendChild( $resource );
                        $top->appendChild( $entry );
                    }
                    //print_r($data);
                    /*echo "\n---------------------------------------\n"; */
                    //Limit the result to only one record
                    //break;
    
                }
                $stmt->closeCursor();
                unset( $stmt );
                
            } catch( PDOException $e ) {
                I2CE::pdoError( $e, "Failed to get cached data for mCSD Update Supplier." );
                http_response_code(500);
                return false;
            }
        }
        if ( $this->useJSON ) {
            $top['total'] = $count;
        } else {
            $total->setAttribute('value', $count);
        }
        return true;
        
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

            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
            }

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
            
            /* if( array_key_exists('extension', $data ) ) {
                //
                if(!array_key_exists('extension', $top ))
                {
                    $top['extension']=array();
                }
                if(!empty($data['extension']))
                {
                    $top['extension']=array('url'=>"http://ihris.org/fhir/StructureDefinition/iHRISPractitionerRolesDetails",
                    'extension'=>array());
                    foreach ( $data['extension'] as $key => $roleInfo ) {
                        if(strpos($key,"date")!==false)
                        {
                            $top['extension']['extension'][]=array('url'=>$key,'valueDateTime'=>$roleInfo);
                        }
                        elseif(strpos($key,"value")!==false)
                        {
                            $top['extension']['extension'][]=array('url'=>$key,'valueString'=>$roleInfo);
                        }
                        elseif(strpos($key,"code")!==false)
                        {
                            if(!is_array($roleInfo))
                            {
                                $top['extension']['extension'][]=array('url'=>$key,"valueCoding"=>array("code"=>$roleInfo));
                            }
                            else{
                                $arrayCode=array();
                                foreach($roleInfo as $info){
                                    $arrayCode[]=array('code'=>$info);
                                }
                                $top['extension']['extension'][]=array('url'=>$key,"valueCoding"=>[$arrayCode]);
                            }
                           
                        }
                        else
                        {
                            $top['extension']['extension'][]=array('url'=>$key,'valueString'=>$roleInfo);
                        }
                        
                    }
                    //array_push($top["extension"],$affectationExtension);
                }
            }
            if( array_key_exists('salary', $data ) ) {
                $dataExtension=null;
                if(!array_key_exists('extension', $top ))
                {
                    $top['extension']=array();
                }
                else{
                    $dataExtension=$top['extension'];
                    if($data['salary']['startDate'])
                    {
                        $dataExtension['extension'][]=array('url'=>'salaryPeriod','valuePerod'=>array('start'=>$data['salary']['startDate'],
                        'end'=>$data['salary']['endDate']));
                    }
                    if($data['salary']['amount'])
                    {
                        $dataExtension['extension'][]=array('url'=>'salaryAmount','valueMoney'=>array('value'=>$data['salary']['amount'],
                            'currency'=>$data['salary']['currencyCode']));
                    } 
                }
                if($dataExtension)
                {
                    $top['extension']=$dataExtension;
                }

            } */
            if(array_key_exists( 'extension', $data ))
            {
                $top["extension"]=array();
                foreach ( $data['extension'] as $keyExtension => $extensionInfoList ) {
                    if(!empty($extensionInfoList))
                    {
                        foreach($extensionInfoList as $counter=>$extensionInfo){
                            $practitionerExtension=array('url'=>"http://ihris.org/fhir/StructureDefinition/".$keyExtension,
                                'extension'=>array());
                            foreach ( $extensionInfo as $keyFieldName => $fieldValue ) {
                                if(strpos($keyFieldName,"date_")!==false)
                                {
                                    $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDate'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"datetime_")!==false)
                                {
                                    $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueDateTime'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"value_")!==false)
                                {
                                    $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],'valueString'=>$fieldValue);
                                }
                                elseif(strpos($keyFieldName,"code_")!==false)
                                {
                                    if(!is_array($fieldValue))
                                    {
                                        $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>array("code"=>$fieldValue));
                                    }
                                    else{
                                        $arrayCode=array();
                                        foreach($fieldValue as $value){
                                            $arrayCode[]=array('code'=>$value);
                                        }
                                        $practitionerExtension['extension'][]=array('url'=>explode('_',$keyFieldName)[1],"valueCoding"=>[$arrayCode]);
                                    }
                                
                                }
                            }
                            array_push($top["extension"],$practitionerExtension);
                        }
                    }
                }
            }
            if ( array_key_exists( 'location', $data ) ) {
                $top['location'] = array( array( 'reference' => $data['location'] ) );
            }

            if ( array_key_exists( 'practitioner', $data ) ) {
                $top['practitioner'] = array( 'reference' => $data['practitioner'] );
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
    protected function createMapping_PractitionerRole( $data,$linkedData,&$top,$listMappingFields,
        $listExtensionMappingFields,$configs ) {
        $top['identifier'] = array();
        $top['code'] = array();
        $top['extension']=array();
        $telecom=array();
        $oIdentifier=array();
        $job=array();
        $specialty=array();
        /*echo "\n--------------------data -------------------------\n";
        
        print_r($data);
        echo "\n--------------------metadata -------------------------\n"; */
        //print_r($listMappingFields);
        $oIdentifier['system']=$configs['identifierSystemUrl'];
        foreach($listMappingFields as $keyIndex=>$mappingFields)
        {
            foreach($data as $key=>$value)
            {
                $fieldExtension=array();
                $fieldHasExtension=false;
                if($key==$mappingFields['iHRISFieldAlias'])
                {
                    $this->extractFieldExtension($listExtensionMappingFields,$mappingFields,$configs,$data,$fieldExtension,
                    $fieldHasExtension); 
                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="PractitionerRole")
                {
                    if(in_array($key,explode(',',$configs['transformFieldToResourceID']['iHRISFieldAlias']))){
                        $value=strtolower(preg_replace('/[^A-Za-z0-9]/', '', $value));
                    }
                    
                    if(array_key_exists('extension',$mappingFields)){
                        //print_r($mappingFields['extension']);
                        switch($mappingFields['extension']['fieldType'])
                        {
                            case"integer":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueInteger'=>$value);
                            break;
                            case"string":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueString'=>$value);
                            break;
                            case"date":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueDate'=> (new DateTime($value))->format("Y-m-d"));
                            break;
                            case"dateTime":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueDateTime'=>(new DateTime($value))->format("c"));
                            break;
                            case "valueCoding":
                                $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueCoding'=>array("code"=>$value));
                            break;
                            default:
                            $oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],'valueString'=>$value);
                        }
                        //$oExtension=array('url'=>$configs['extensionUrl'].$mappingFields['extension']['extensionName'],$dicValue);
                        $top['extension'][]=$oExtension;
                    }
                    else
                    {
                        if($mappingFields['fhirField']=="period.start")
                        {
                            $top['period']['start']= (new DateTime($value))->format("c");
                        }
                        elseif($mappingFields['fhirField']=="period.end")
                        {
                            $top['period']['end']= (new DateTime($value))->format("c");
                        }
                        elseif($mappingFields['fhirField']=="practitioner")
                        {
                            $top[$mappingFields['fhirField']] = array( 'reference' => "Practitioner/".$value );
                        }
                        elseif($mappingFields['fhirField']=="location")
                        {
                            $top[$mappingFields['fhirField']] = array( 'reference' => "Location/".$value );
                        }
                        elseif($mappingFields['fhirField']=="organization")
                        {
                            $top[$mappingFields['fhirField']] = array( 'reference' => "Organization/".$value );
                        }
                        elseif($mappingFields['fhirField']=="code.coding")
                        {
                            if(!array_key_exists('coding',$job)){
                                $job['coding']=array();
                            }
                            if($configs['removeiHRISPrefixForCodeableConcept'])
                            {

                                $temp=explode("|",$value)[1];
                                $job['coding'][0]['code']=$temp;
                            }
                            else{
                                $job['coding'][0]['code']=$value;
                            }
                            //$job['coding'][0]['code']=$value;
                        }
                        elseif($mappingFields['fhirField']=="code.text")
                        {
                            $job['text']=$value;
                        }
                        elseif($mappingFields['fhirField']=="code.display")
                        {
                            if(!array_key_exists('coding',$job)){
                                $job['coding']=array();
                            }
                            
                            $job['coding'][0]['display']=$value;
                        }
                        elseif($mappingFields['fhirField']=="specialty.coding")
                        {
                            $specialty['coding']=array();
                            $specialty['coding'][]=array('system'=>$configs['identifierSystemUrl'],'code'=>$value);
                        }
                        elseif($mappingFields['fhirField']=="specialty.text")
                        {
                            $specialty['text']=$value;
                        }
                        elseif($mappingFields['fhirField']=="id"){
                            $top['fullURL']=$this->getSiteBase() . "PractitionerRole/" .$value;
                            $top[$mappingFields['fhirField']]=$value;
                            
                        }
                        else
                        {
                            $top[$mappingFields['fhirField']]=$value;
                        }
                        
                    }
                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="identifier")
                {
                    if($mappingFields['fhirField']=="system")
                    {
                        $oIdentifier['system']=$configs['identifierSystemUrl'].$value;
                    }
                    elseif($mappingFields['fhirField']=="value"){
                        $oIdentifier['value']=$value;
                    }
                    elseif($mappingFields['fhirField']=="period.start"){
                        $oIdentifier['period']['start']= (new DateTime($value))->format('c');
                    }
                    elseif($mappingFields['fhirField']=="period.end"){
                        $oIdentifier['period']['end']=(new DateTime($value))->format('c');;
                    }
                    elseif($mappingFields['fhirField']=="assigner"){
                        $oIdentifier['assigner']=$value;
                    }
                    if($fieldHasExtension){
                        if(!array_key_exists('extension',$oIdentifier))
                        {
                            $oIdentifier['extension']=array();
                        }
                        $oIdentifier['extension'][]=$fieldExtension;
                    }
                }
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="telecom")
                {
                    $telecomKey=explode('_',$mappingFields['iHRISFieldAlias']);
                    if(sizeof($telecomKey)<=1)
                    {
                        if(!array_key_exists('default',$telecom)){
                            $telecom['default']=array();
                        }
                        $telecom['default']['use']='home';
                        if(in_array($telecomKey[0],array('phone','fax','email','pager','url','sms')) ){
                            $telecom['default']['system']=$telecomKey[0];
                        }
                        else
                        {
                            $telecom['default']['system']="other";
                        }
                        
                        $telecom['default']['value']=$value;
                        if($fieldHasExtension){
                            if(!array_key_exists('extension',$telecom['default']))
                            {
                                $telecom['default']['extension']=array();
                            }
                            $telecom['default']['extension'][]=$fieldExtension;
                        }
                        
                    }
                    else
                    {
                        if(!array_key_exists($telecomKey[0],$telecom)){
                            $telecom[$telecomKey[0]]=array();
                        }
                        if(in_array($telecomKey[0],array('home','work','mobile')) )
                        {
                            $telecom[$telecomKey[0]]['use']=$telecomKey[0];
                        }
                        else{
                            $telecom[$telecomKey[0]]['use']='temp';
                        }
                        if(in_array($telecomKey[1],array('phone','fax','email','pager','url','sms')) ){
                            $telecom[$telecomKey[0]]['system']=$telecomKey[1];
                        }
                        else
                        {
                            $telecom[$telecomKey[0]]['system']="other";
                        }
                        
                        $telecom[$telecomKey[0]]['value']=$value;
                        
                        if($fieldHasExtension){
                            if(!array_key_exists('extension',$telecom[$telecomKey[0]]))
                            {
                                $telecom[$telecomKey[0]]['extension']=array();
                            }
                            $telecom[$telecomKey[0]]['extension'][]=$fieldExtension;
                        }
                        
                    }
                    
                    
                }

            }
            
        }
        //if($oIdentifier['value']!=null)
        if(array_key_exists('value',$oIdentifier))
        {
            array_push($top['identifier'],$oIdentifier);
        }
        if(!empty($job))
        {
            $top['code'][]=$job;
        }
        if(!empty($specialty))
        {
            $top['specialty'][]=$specialty;
        }
        foreach($telecom as $key=>$contactPoint)
        {
            $top['telecom'][]=$contactPoint;
        }
        $identifierLinked=array();
        $contactPointLinked=array();
        //$indexLinked=0;
       $this->extractPractitionerRoleLinkedData($listMappingFields,$listExtensionMappingFields,$configs,$linkedData,
           $identifierLinked,$contactPointLinked);
        if(!empty($identifierLinked))
        {
            foreach($identifierLinked as $key=>$identifier)
            {
                //array_push($top['identifier'],$identifier);
                $top['identifier'][]=$identifier;
            }
        } 
        if(!empty($contactPointLinked))
         {
            foreach($contactPointLinked as $key=>$oTelecom)
            {
                array_push($top['telecom'],$oTelecom);
            }
         }
        $practitionerRoleGroupExtension=array();
        $this->extractPractitionerRoleGroupExtensions($listExtensionMappingFields,$listMappingFields,$configs,$data,$linkedData,
            $practitionerRoleGroupExtension);
        if(!empty($practitionerRoleGroupExtension)){
            foreach($practitionerRoleGroupExtension as $key=>$oExtension)
            {
                if($oExtension){
                    $top['extension'][]=$oExtension;
                }
                
            }
        }
       
    }
    protected function createMapping_ValueSet( $data,$linkedData,&$top,$listMappingFields,
        $listExtensionMappingFields,$configs ) {
        $concept=array();
        foreach($data as $key=>$value)
        {
            
            foreach($listMappingFields as $keyIndex=>$mappingFields)
            {
                if($key==$mappingFields['iHRISFieldAlias'] && $mappingFields['fhirBaseElement']=="concept")
                {
                    
                    if($mappingFields['fhirField']=="code")
                    {
                        if($configs['removeiHRISPrefixForCodeableConcept'])
                        {

                            $temp=explode("|",$value)[1];
                            $concept[$mappingFields['fhirField']]=$temp;
                        }
                        else{
                            $concept[$mappingFields['fhirField']]=$value;
                        }
                    }
                    elseif($mappingFields['fhirField']=="display"){
                        $concept[$mappingFields['fhirField']]=$value;
                    }
                    elseif($mappingFields['fhirField']=="definition"){
                        $concept[$mappingFields['fhirField']]=$value;
                    }
                }

            }
            
            
        }
        $top['compose']['include'][0]['concept'][]=$concept;
        //if($oIdentifier['value']!=null)
        
       
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
