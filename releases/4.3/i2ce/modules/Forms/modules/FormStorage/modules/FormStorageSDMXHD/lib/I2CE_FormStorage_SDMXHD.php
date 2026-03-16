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


class I2CE_FormStorage_SDMXHD extends   I2CE_FormStorage_XML_BASE{



    /**
     *$var protected array $codelist array of the codelists used index by the form name
     */
    protected $codelist = array();


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
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
        }
        $this->codelist[$form] = false;
        if (!$options->setIfIsSet($this->codelist[$form], "CodeListID")) {
            I2CE::raiseError( "SDMX-HD CodeListID is not set" );
            return false;
        }
        return  parent::getDOMData($form);
    }

    /**
     * Get the xpath query for the base node containing the data
     * @param string $form
     * @returns string
     */
    protected function getBaseQuery($form) {        
        return  "//structure:CodeList[@id='" . $this->codelist[$form] . "']" ;
    }


    /**
     * Get the xpath query for the data nodes relative to the  the containing data
     * @param string $form
     * @returns string
     */
    protected function getDataNodesQuery($form) {        
        return './structure:Code';
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
     * Get the id associated to the given node
     * @param string $form
     * @param DOMNode $node
     * @param int count
     * @returns string.  '0' or null  on failure a string on success.
     */
    protected function getNodeIdDescription($form) {
        return array('attribute'=>'value','prepended'=>0,'eval'=>false);
    }
    
    

    /**
     * Get the field data description for the given form.
     * @param string $form
     * @param string $field.  Defaults to null in which case we get all the fields for the given form
     * @returns array indexed by field name or an array of the field data.
     */
    protected function getFieldDataDesc($form, $field = null) {
        if ($field === null) {
            return array(
                'name'=>array(
                    'query'=>'./structure:Description',
                    'eval'=>false
                    )
                );
        } else if ($field === 'name') {
            return  array(
                'query'=>'./structure:Description',
                'eval'=>false
                );
        } else {
            return array('eval'=>false);
        }
    }

    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
