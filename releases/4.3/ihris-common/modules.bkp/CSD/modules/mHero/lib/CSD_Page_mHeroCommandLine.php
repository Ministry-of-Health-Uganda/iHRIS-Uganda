<?php
  /**
   * © Copyright 2014 IntraHealth International, Inc.
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
   * @package ihris-common
   * @subpackage csd
   * @author Carl Leitner <litlfred@ibiblio.org>
   * @version v4.2.0
   * @since v4.2.0
   * @filesource 
   */ 
  /** 
   * Class CSD_Page_StartWorkflow
   * 
   * @access public
   */




class CSD_Page_mHeroCommandLine extends CSD_Page_RapidPro_Base {

    public function actionCommandLine($args,$request_remainder) {
        if (! $this->_display()) {
            exit(1);
        } else {
            exit(0);
        }
    }



    protected function extract() {
        $code = 'rapidpro_contact_id';
        $assigning_authority =  $this->server_host . '/' . $this->slug;

        $extract = array();
        $extract = $this->rapidpro->getAllContactData();
        $doc = new DOMDocument();
        $src = '<csd:CSD xmlns:csd="urn:ihe:iti:csd:2013"><csd:organizationDirectory/><csd:serviceDirectory/><csd:facilityDirectory/><csd:providerDirectory/></csd:CSD>';
        $doc->loadXML($src);        
        $organization_dir_node = $doc->documentElement->childNodes->item(0);
        $facility_dir_node = $doc->documentElement->childNodes->item(2);
        $provider_dir_node = $doc->documentElement->childNodes->item(3);


        $has_person = I2CE_ModuleFactory::instance()->isEnabled('Person-CSD');
        $has_provider = I2CE_ModuleFactory::instance()->isEnabled('csd-provider-data-model');
        
        foreach($extract as $contact) {
            if (!is_array($contact)
                ||! array_key_exists('uuid',$contact)
                ||! array_key_exists('name',$contact)
                || ! ($uuid = $contact['uuid'])
                || ! ($name = $contact['name'])
                || ! array_key_exists('fields',$contact)
                || ! is_array($fields = $contact['fields'])
                || ! array_key_exists( 'globalid',$fields)
                || ! ($csd_uuid = $fields['globalid'])
                ){
                continue;
            }
            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'csd_uuid',
                'style'=>'in',
                'data'=>array(
                    'value'=>$csd_uuid
                    )
                );
            $person_ids = $I2CE_FormStorage::search('person',false,$where);
            if (($has_person && count($person_ids) > 0) 
                ||  ($has_provider && I2CE_FormStorage::lookupField( 'csd_provider', $csd_uuid,array('entityID')))
                ) {
                //it's a provider
                $provider_dir_node->appendChild($node = $doc->createElementNS('urn:ihe:iti:csd:2013','csd:provider'));
                $node->appendChild($demo_node = $doc->createElementNS('urn:ihe:iti:csd:2013','csd:demographic'));
                $demo_node->appendChild($name_node = $doc->createElementNS('urn:ihe:iti:csd:2013','csd:name'));
                $name_node->appendChild($cn_node = $doc->createElementNS('urn:ihe:iti:csd:2013','csd:commonName'));
                $cn_node->appendChild($doc->createTextNode($name));

            } else if ($has_provider &&  I2CE_FormStorage::lookupField( 'csd_facility', $csd_uuid,array('entityID'))) {
                $facility_dir_node->appendChild($node = $doc->createElementNS('urn:ihe:iti:csd:2013','csd:facility'));
                $node->appendChild($name_node =  $doc->createElementNS('urn:ihe:iti:csd:2013','csd:primaryName'));
                $name_node->appendChild($doc->createTextNode($name));
            } else if ( $has_provider && I2CE_FormStorage::lookupField( 'csd_organization', $csd_uuid,array('entityID'))) {
                $organization_dir_node->appendChild($node = $doc->createElementNS('urn:ihe:iti:csd:2013','csd:organization'));
                $node->appendChild($name_node =  $doc->createElementNS('urn:ihe:iti:csd:2013','csd:primaryName'));
                $name_node->appendChild($doc->createTextNode($name));
            } else {
                continue;
            }
            $node->setAttribute('entityID',$csd_uuid);
            $node->appendChild($other_id_node = $doc->createElementNS('urn:ihe:iti:csd:2013','csd:otherID'));
            $other_id_node->setAttribute('code',$code);
            $other_id_node->setAttribute('assigningAuthorityName',$assigning_authority);
            $other_id_node->appendChild($doc->createTextNode($uuid));
            $node->appendChild($rec_node = $doc->createElementNS('urn:ihe:iti:csd:2013','csd:record'));
            //what should 'created' be?
            $rec_node->setAttribute('updated',$contact['modified_on']);
            $rec_node->setAttribute('created',$contact['modified_on']);
            $rec_node->setAttribute('status','106-001');
            $rec_node->setAttribute('sourceDirectory',	$assigning_authority);

        }
        echo $doc->saveXML();
        return true;
    }

    protected function extract_raw() {
        $extract = array();
        $extract = $this->rapidpro->getAllContactData();
        print_r( $extract);
        return true;
        
    }


    protected function _display($supress_output = false) {
	if (  !$this->action 
              || array_key_exists('HTTP_HOST',$_SERVER) ) {
	    return parent::_display($supress_output);            
        }
        //command line only
        switch ($this->action) {
        case 'extract':
            exit( $this->extract() ? 0 : 1);
        case 'extract_raw':
            exit( $this->extract_raw() ? 0 : 1);
        }
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
