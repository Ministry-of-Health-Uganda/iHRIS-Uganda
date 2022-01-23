<?php
/**
* © Copyright 2016 IntraHealth International, Inc.
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
* @subpackage fhir
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2
* @since v4.2
* @filesource 
*/ 
/** 
* Class I2CE_FHIR_Bundle
* 
* @access public
*/

use \FHIR_DSTU_TWO\FHIRResource\FHIRBundle as FHIRBundle; 
use \FHIR_DSTU_TWO\FHIRResource\FHIRBundle\FHIRBundleEntry as FHIRBundleEntry;
use \FHIR_DSTU_TWO\FHIRElement\FHIRBundleType as FHIRBundleType;
use \FHIR_DSTU_TWO\FHIRResource\FHIRBundle\FHIRBundleLink as FHIRBundleLink;
use FHIR_DSTU_TWO\FHIRElement\FHIRString as FHIRString;
use FHIR_DSTU_TWO\FHIRElement\FHIRUri as FHIRUri;
use FHIR_DSTU_TWO\FHIRResource as FHIRResource; 
use FHIR_DSTU_TWO\FHIRResourceContainer as FHIRResourceContainer;

class I2CE_FHIR_SearchBundle extends I2CE_FHIR_Base implements Iterator{
    
    /**
     * @var \FHIR_DSTU_TWO\FHIRResource\FHIRBundle\FHIRBundleEntry[] $entries
     */
    protected $entries;
    /**
     * @var integer $i the counter
     */
    protected $i = 0;
    /**
     * @var array $resource_types,  filter the response to only include the entries with resource types in the array.  
     */
    protected $resource_types = array();
    /**
     *Initiate a search
     * @param string $url a search string
     * @param array $resource_types, filter the response to only include the entries with resource types in the array.  
     *        Example array('QuestionnaireResponse')
     * @return \FHIR_DSTU_TWO\FHIRResource\FHIRBundle
     */
    public function start_search($url,$resource_types) {
        if (! ($b = $this->load_resource($url,'Bundle')) instanceof FHIRBundle) {
            throw new Exception("Could not perform search");
        }        
        $this->resource =$b;
        if (!is_array($resource_types)) {
            $resource_types =array();
        }
        $this->resource_types  = $resource_types;
        $this->append_entries();
        return $this->resource;
    }
    /**
     * process the current bundle and append bundle entries to the result list
     */
    private function append_entries() {
        if (! $this->resource instanceof FHIRBundle
            || ! is_array($this->resource->entry)
            || ! is_array($this->resource_types)
            || count($this->resource_types) == 0
            ) {            
            return false;
        }
        foreach ($this->resource->entry as $entry) {
            if (!$entry instanceof FHIRBundleEntry
                || ! ($rc = $entry->resource) instanceof FHIRResourceContainer                
                ){
                continue;
            }
            foreach ($this->resource_types as $rt) {
                if (!is_string($rt)
                    ||! property_exists($rc,$rt)
                    ||! $rc->$rt instanceof FHIRResource
                    ) {
                    continue;
                }
                $this->entries[] = $rc->$rt;
                break;
            }
        }
        return true;
    }

    /**
     * get the url for the next page or results
     * @returns string
     */ 
    public function get_next_url() {
        if (!  $this->resource instanceof FHIRBundle
            || ! is_array($links = $this->resource->links)
            ){
            return false;
        }
        foreach ($links as $link) {
            if (!$link instanceof FHIRBundleLink
                || ! ($uri =  $link->url ) instanceof FHIRUri
                || ! ($relation =  $link->relation ) instanceof FHIRString
                || ! ($realation == 'next')
                ) {
                continue;
                return $uri->value;
            }
        }
        return false;
    }

    /**
     * Get the current entry.  part of Iterator interface
     * @return \FHIR_DSTU_TWO\FHIRResource\FHIRBundle\FHIRBundleEntry 
     */
    public function current() {
        if (array_key_exists($this->i, $this->entries)) {
            return $this->entries[$this->i];
        } else {
            throw new Exception("Index out of bounds");
        }
    }
    /**
     * Get the current key. part of Iterator interface
     * @ return integer
     */
    public function key() {
        return $this->i;
    }
    /**
     * advance the counter pointer.  slurp up new bundle entries if possible.
     * part of Iterator interface
     */
    public function next() {
        $this->i++;
        if ($this->i > count($this->entries)
            && ($next_url = $this->get_next_url())
            ) {
            if (! ($b = $this->load_resource($url,'Bundle')) instanceof FHIRBundle) {
                throw new Exception("Could not perform search");
            }        
            $this->resource =$b;
            $this->append_entries();
        }
    }
    /**
     * check to see if we are in a valid state. part of Iterator interface
     * @return boolean
     */
    public function valid() {
        return (
            $this->i < count($this->entries)
            && $this->resource instanceof FHIRBundle
            && ($this->resource->type instanceof FHIRBundleType)
            && ($this->resource->type->value == 'searchset')
            );
    }
    /**
     * reset pointer counter.   
     */
    public function rewind() {
        $i = 0;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
