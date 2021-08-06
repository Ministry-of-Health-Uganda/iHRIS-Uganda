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
            }

            $data['telecom'] = array( 
                    'work' => array( 'phone' => array(), 'email' => array() ), 
                    'mobile' => array( 'phone' => array() ), 
                    'home' => array( 'phone' => array(), 'email' => array() ), 
                    );
            $data['address'] = array();

            $person->populateChildren('person_contact_work');
            $work = current($person->getChildren('demographic'));
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
            $personal = current($person->getChildren('demographic'));
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


        $required_forms = array( "person", "demographic", "person_contact_personal", "person_contact_work" );
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

        $qry = "SELECT person.csd_uuid AS uuid, DATE_FORMAT(GREATEST(person.last_modified, IFNULL(demographic.last_modified,'0000-00-00 00:00:00'), IFNULL(personal.last_modified,'0000-00-00 00:00:00'), IFNULL(work.last_modified,'0000-00-00 00:00:00')), '%Y-%m-%d %T') AS lastupdated, person.id, person.surname AS family, person.firstname AS given, person.othername AS given2, demographic.gender, DATE_FORMAT(demographic.birth_date,'%Y-%m-%d') AS birthdate, personal.telephone AS home_phone, personal.mobile_phone AS home_mobile, personal.email AS hoome_email, personal.address AS home_address, work.telephone AS work_phone, work.mobile_phone AS work_mobile, work.email AS work_email, work.address AS work_address FROM hippo_person AS person LEFT JOIN hippo_demographic AS demographic ON demographic.parent = person.id LEFT JOIN hippo_person_contact_personal AS personal ON personal.parent = person.id LEFT JOIN hippo_person_contact_work AS work ON work.parent = person.ID $where ORDER BY lastupdated ASC";

        try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            $count = 0;
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            while ( $row = $stmt->fetch() ) {
                $count++;
                $data = array();
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
                $data['birthDate'] = $row->birthdate;
                $data['telecom'] = array( 
                        'work' => array( 'phone' => array(), 'email' => array() ),
                        'mobile' => array( 'phone' => array() ),
                        'home' => array( 'phone' => array(), 'email' => array() ),
                        );
                $data['address'] = array();
                if ( $row->home_phone ) {
                    $data['telecom']['home']['phone'][] = $row->home_phone;
                }
                if ( $row->home_mobile ) {
                    $data['telecom']['mobile']['phone'][] = $row->home_mobile;
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
                if ( $row->work_address ) {
                    $data['address']['work'] = $row->work_address;
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

        if ( $this-> useJSON ) {
            $top['id'] = $data['uuid'];
            if ( array_key_exists('lastUpdated', $data ) ) {
                $top['meta'] = array( 'lastUpdated' => $data['lastUpdated'] );
            }

            if ( array_key_exists('id_system', $data) && array_key_exists('id_code', $data) ) {
                $top['identifier'] = array();
                $top['identifier'][] = array( 'system' => $data['id_system'], 'value' => $data['id_code'] );
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
                        foreach ( $contacts as $contact ) {
                            $top['telecom'][] = array( 'resourceType' => 'ContactPoint', 'system' => $system, 'use' => $use, 'value' => $contact );
                        }
                    }
                }
            }

            if ( array_key_exists( 'address', $data ) ) {
                $top['address'] = array();
                foreach( $data['address'] as $use => $address ) {
                    $top['address'][] = array( 'resourceType' => 'Address', 'use' => $use, 'text' => $address );
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
                $count++;
                $data = array();
                $entry = $this->doc->createElement("entry");
                if ( $this->useJSON ) {
                    $entry = array();
                    $entry['fullURL'] = $this->getSiteBase() . "FHIR/Location/" . $row->uuid;
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
            if ( $pers_pos->end_date->isBlank() ) {
                $data['active'] = 'true';
            } else {
                $data['active'] = 'false';
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


        $required_forms = array( "person_position", "person", "position", "job", "facility" );
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
            $where = "WHERE pp.last_modified >= ?";
            $params = array( $this->since );
        }

        $qry = "SELECT pp.csd_uuid AS uuid, DATE_FORMAT(pp.last_modified, '%Y-%m-%d %T') AS lastupdated, pp.id, pp.end_date, job.id AS code, job.title AS code_text, person.csd_uuid AS practitioner, facility.csd_uuid AS location FROM hippo_person_position AS pp LEFT JOIN hippo_position position ON position.id = pp.position LEFT JOIN hippo_job AS job ON job.id = position.job LEFT JOIN hippo_facility AS facility ON facility.id = position.facility LEFT JOIN hippo_person AS person ON person.id = pp.parent $where ORDER BY lastupdated ASC";

        try {
            $db = I2CE::PDO();
            $stmt = $db->prepare( $qry );
            $stmt->execute( $params );
            $count = 0;
            if ( $this->useJSON ) {
                $top['entry'] = array();
            }
            while ( $row = $stmt->fetch() ) {
                $count++;
                $data = array();
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
                if ( !$row->end_date || $row->end_date == '0000-00-00 00:00:00' ) {
                    $data['active'] = 'false';
                } else {
                    $data['active'] = 'true';
                }
                $data['code_system'] = $this->getSiteBase();
                $data['code'] = $row->code;
                $data['code_text'] = $row->code_text;

                //$data['location'] = $this->getSiteBase() . "FHIR/Location/" . $row->location;
                $data['location'] = "Location/" . $row->location;
                //$data['practitioner'] = $this->getSiteBase() . "FHIR/Location/" . $row->practitioner;
                $data['practitioner'] = "Location/" . $row->practitioner;

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



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
