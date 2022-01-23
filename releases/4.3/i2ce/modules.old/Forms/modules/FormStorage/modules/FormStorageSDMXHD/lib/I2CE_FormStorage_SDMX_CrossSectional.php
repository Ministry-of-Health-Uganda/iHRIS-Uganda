<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @package i2ce
* @subpackage forms
* @author Luke Duncan <lduncan@intrahealth.org>
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_SDMXHD
* Storage mechanism for reading SDMX-HD code lists.
* 
* @access public
*/


class I2CE_FormStorage_SDMX_CrossSectional extends   I2CE_FormStorage_XML_BASE{

    /**
     * Get the id associated to the given node
     * @param string $form
     * @param DOMNode $node
     * @param int count
     * @returns string.  '0' or null  on failure a string on success.
     */
    protected function getLocationId($form, $node, $count) {
        return $count;
    }

    /**
     *$var protected array $namespace array of the namespaces used index by the form name
     */
    protected $namespace = array();
    /**
     * Get the xpath query for the base node containing the data
     * @param string $form
     * @returns string
     */
    protected function getBaseQuery($form) {        
        return "/CrossSectionalData/" . $this->namespace[$form] . ":DataSet";
    }


    /**
     * Get the xpath query for the data nodes relative to the  the containing data
     * @param string $form
     * @returns string
     */
    protected function getDataNodesQuery($form) {        
        return ".//" . $this->namespace['form'] . ":OBSVALUE";
    }

    /**
     * Get the search category
     * @param string $form
     * @returns string
     */
    protected function getSearchCategory($form) {
        return 'SDMXHD';
    }


    /**
     * Tries to get the DOM Data for the SDMX-HD file
     * @param string $form 
     * @return DOMDocument
     */
    protected function getDOMData( $form ) {
        if ( array_key_exists( $form, $this->dom_data ) 
                && is_array( $this->dom_data[$form] ) ) {
            return $this->dom_data[$form];
        }
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid SDMX_CrossSectional storage options for $form" );
            return false;
        }
        $this->namespace[$form] = 'ns';
        $options->setIfIsSet($this->namespace[$form], "namespace");
        return  parent::getDOMData($form);
    }

    
    /**
     * @var protected array $mapping_data.  An array, indexed by form names, of an array of mapping data.
     */ 
    protected $mapping_data = array();

    /**
     * Gets any code lists that are already  mapped in the system
     * @param string $form
     * @returns boolean
     */
    protected function getMappedCodeLists($form, $field) {
        if (array_key_exists($form,$this->mapping_data) && array_key_exists($field, $this->mapping_data[$form])) {
            return $this->mapping_data[$form][$field];
        }
        if (!array_key_exists($form,$this->mapping_data)) {
            $this->mapping_data[$form] = array();
        }
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid SDMX_CrossSectional storage options for $form" ); //shouldn't happen at this point
            return false;
        }
        $mapData = false;
        //do something to populate the mapping data
        if ($field == 'parent') {
            $mapPath = "parent/map_data";
        }else {
            $mapPath = "fields/$field/map_data";
        }
        if ($options->is_scalar($mapPath . '/list') && $options->is_scalar($mapPath . '/codelist')) {
            $list = $option->$mapPath->list;
            $codelist = $option->$mapPath->codelist;
            if ($list && $codelist) { 
                $mapData = array(); //we have a list and a codelist set so we should attempt to get the mapping data at this point
                if (I2CE_FormFactory::instance()->exists($list) ) {
                    $linkForm = 'list_linkto_list_' . I2CE_FormStorage::getStorage($options->$mapPath->list);
                    $options->setIfIsSet($linkForm,$mapPath .'/mapping_form');
                    $where = array(
                        'operator'=>'AND',
                        'operands'=> array(
                            0=>array(
                                'operator'=>'FIELD_LIMIT',
                                'field'=>'list', 
                                'style'=>'like',
                                'data'=>array(
                                    'value'=>$list . '|%'
                                    )
                                ),
                            1=>array(
                                'operator'=>'FIELD_LIMIT',
                                'field'=>'like', 
                                'style'=>'starts_with',
                                'data'=>array(
                                    'value'=>$codelist . '|%'
                                    )
                                )
                            )
                        );
                    $data = I2CE_FormStorage::listFields($linkForm,array('list','links_to'), false,$where);                
                }         
                $codelist_len = strlen($codelist) +1;
                foreach ($data as $id=>$vals) {
                    if (!is_array($vals) || !array_key_exists('list',$vals) || !array_key_exists('links_to',$vals) || strlen($vals['links_to'] <= $codelist_len)) {
                        continue;
                    }
                    //chop of the $codelist| from the links to value and store it.
                    $data[substr($vals['codelist'],$codelist_len)] = $vals['list'];
                }
            } 
        }
        $this->mapping_data[$form][$field] = $mapData;
        return $this->mapping_data[$form][$field];
    }

    /**
     * Get the value based on the  given field data
     * @param array $fieldData
     * @param array $dom_data
     * @param DOMNode $node
     * @returns mixed. null if no data was found.  otherwise it is a string
     */
    protected function getFieldValue($form,$field,$fieldData,$dom_data,$node) {
        if (!array_key_exists('attribute',$fieldData) || !$fieldData['attribute']) {
            return null;
        }
        $dbval = null;
        $parentNode = $node;
        while ($parentNode instanceof DOMElement && $dbval === null) {
            if ($parentNode->hasAttribute($fieldData['attribute'])) {
                $dbval = $paretNode->getAttribute($fieldData['attribute']);
            }
            $parentNode = $parentNode->parentNode;
        }        
        if (is_array($mapData = $this->getMappedCodeLists($formName, $field ))) {
            if ($dbval !== null) {
                if ( array_key_exists($dbval,$mapData)) {
                    $dbval = $mapData[$dbval];
                }  else {
                    $dbval = null;
                }
            }
        }
        return $dbval;
    }
    

    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
