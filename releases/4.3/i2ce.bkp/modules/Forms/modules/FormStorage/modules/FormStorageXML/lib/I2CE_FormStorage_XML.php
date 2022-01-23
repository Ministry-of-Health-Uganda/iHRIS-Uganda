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
* Class I2CE_FormStorage_XML
* Storage mechanism for reading XML code lists.
* 
* @access public
*/


class I2CE_FormStorage_XML extends   I2CE_FormStorage_XML_BASE{




    /**
     *$var protected array $searchcat array of the searchcats used index by the form name
     */
    protected $searchcat = array();

    /**
     *$var protected array $namespaces array of the name spaces used index by the form name
     */
    protected $namespaces = array();


    /**
     * Get the namespaces we should register with xpath
     * @param string $form
     * @returns array keys are namespace prefixes values are the URIS
     */
    protected function getNamespaces($form) {
        if (!array_key_exists($form,$this->namespaces) 
            || !is_array($this->namespaces[$form])) {
            return array();
        }
        return $this->namespaces[$form];
    }


    /**
     * Release any resourced held by this form storage mechanism for the indicated form
     * @param string $form
     */
    public function release($form) {
        if (!is_string($form)) {
            return;
        }
        if (array_key_exists($form,$this->namespaces)) {
            unset($this->namespaces[$form]);
        }        
        if (array_key_exists($form,$this->dataquery)) {
            unset($this->dataquery[$form]);
        }        
        if (array_key_exists($form,$this->dataquery)) {
            unset($this->dataquery[$form]);
        }        
        if (array_key_exists($form,$this->basequery)) {
            unset($this->basequery[$form]);
        }        
        if (array_key_exists($form,$this->searchcat)) {
            unset($this->searchcat[$form]);
        }        
        parent::release($form);        
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
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
        }
        $this->searchcat[$form] = false;
        if (!$options->setIfIsSet($this->searchcat[$form], "search")) {
            I2CE::raiseError( "search is not set" );
            return false;
        }
        $this->basequery[$form] = false;
        if (!$options->setIfIsSet($this->basequery[$form], "basequery")) {
            I2CE::raiseError( "base query is not set" );
            return false;
        }
        $this->dataquery[$form] = false;
        if (!$options->setIfIsSet($this->dataquery[$form], "dataquery")) {
            I2CE::raiseError( "data query is not set" );
            return false;
        }
        $this->namespaces[$form] = array();
        $options->setIfIsSet($this->namespaces[$form], "namespaces",true);
        return  parent::getDOMData($form);
    }


    /**
     * Get the xpath query for the base node containing the data
     * @param string $form
     * @returns string
     */
    protected function getBaseQuery($form) {
        return $this->basequery[$form];
    }

    /**
     * Get the xpath query for the data nodes relative to the  the containing data
     * @param string $form
     * @returns string
     */
    protected function getDataNodesQuery($form) {        
        return $this->dataquery[$form];
    }

    /**
     * Get the search category
     * @param string $form
     * @returns string
     */
    protected function getSearchCategory($form) {
        return $this->searchcat[$form];
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
