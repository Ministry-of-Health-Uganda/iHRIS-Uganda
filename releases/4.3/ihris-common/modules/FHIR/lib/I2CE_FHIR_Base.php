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
   * Class I2CE_FHIR_Base
   * 
   * @access public
   */

use FHIR_DSTU_TWO\PHPFHIRResponseParser as PHPFHIRResponseParser;
use FHIR_DSTU_TWO\FHIRElement\FHIRReference as FHIRReference;
use FHIR_DSTU_TWO\FHIRResourceContainer as FHIRResourceContainer; 
use FHIR_DSTU_TWO\FHIRElement\FHIRString as FHIRString;
use FHIR_DSTU_TWO\FHIRElement\FHIRId as FHIRId;
use FHIR_DSTU_TWO\FHIRResource as FHIRResource; 

class I2CE_FHIR_Base {


    /* the main resource being processed
     * @var \FHIR_DSTU_TWO\FHIRResource
     */
    public $resource;


    /* the FHIR response parser
     * @var \FHIR_DSTU_TWO\PHPFHIRResponseParser $parser
     */
    public $parser;

    public function __construct() {
        $this->parser = new PHPFHIRResponseParser(false);
    }


    /**
     * Get the contained resource indicated by the reference.
     * @param  \FHIR_DSTU_TWO\FHIRReference $reference
     * @param string $resource_type the type of the contained resource e.g. 'ValueSet'.  
     * @return \FHIR_DSTU_TWO\FHIRResource
     */
    public function get_contained_resource($reference,$resource_type) {
        $id = substr($reference,1);
        if (! is_array($contained = $this->resource->contained)) {
            throw new Exception("Contained resource $reference not found (1)");
        }
        foreach ($contained as $rc) {
            if ($rc instanceof FHIRResourceContainer
                && property_exists($rc,$resource_type)
                && ($resource = $rc->$resource_type) instanceof FHIRResource
                && ($resource->get_fhirElementName() == $resource_type)
                && ($resource->id instanceof FHIRId) 
                && ($resource->id->value == $id)
                ){
                return $resource;
            }
        }
        throw new Exception("Contained resource $reference not found (2)");
    }


    /**
     * Get a referenced resourcce
     * @param \FHIR_DSTU2\FHIRReference
     * @param string $resource_type  the type of the contained resource e.g. 'ValueSet'.  
     * @return \FHIR_DSTU_TWO\FHIRResource the referenced resource
     */
    public function get_referenced_resource($reference,$resource_type) {
        if ( ! ($reference instanceof FHIRReference) 
             || !( $reference->reference instanceof FHIRString)
             || !( is_string($url=  $reference->reference->value))
             || strlen($url) == 0
            ) {
            throw new Exception("Invalid reference");
        }
        if ($url[0] =='#') {
            //contained reference
            return $this->get_contained_resource($url,$resource_type);
        } else  {
            //external resource
            return  $this->_load_resource($url,$resource_type);
        }
    }



    /**
     * Loads a questionnaire from a any protocol supported here http://php.net/manual/en/wrappers.php          
     * @param string $url the source of the resource.
     * @param string $resource_type optional.  the type of the contained resource e.g. 'ValueSet'.  defaults to null
     * @return \FHIR_DSTU_TWO\FHIRResource the parsed resource
     */
    private function _load_resource($url,$resource_type = null) { 
        set_error_handler(function($e_no,$e_str) {I2CE::raiseMessage(  "Failed to get resource ($e_no): $e_str\n");});
        if ( ($content = file_get_contents($url)) === false) {
            restore_error_handler();
            throw new Exception("No resource found at $url");
        }
        restore_error_handler();
        return $this->_load_resource_content($content,$resource_type);
    }

    /**
     * Loads a questionnaire from a any protocol supported here http://php.net/manual/en/wrappers.php
     *
     * @param string $url the source of the resource.
     * @param string $resource_type optional.  the type of the contained resource e.g. 'ValueSet'.  defaults to null
     * @return \FHIR_DSTU_TWO\FHIRResource the parsed resource
     */
    public function load_resource($url,$resource_type = null) {
        return ($this->resource = $this->_load_resource($url,$resource_type));
    }

    
    /**
     * Loads a resource from a content string
     *
     * @param string $conent the contnet of the resource.
     * @param string $resource_type optional.  the type of the contained resource e.g. 'ValueSet'.  defaults to null
     * @return \FHIR_DSTU_TWO\FHIRResource the parsed resource
     */
    public function load_resource_content($content, $resource_type =null) {
        return ($this->resource = $this->_load_resource_content($content,$resouce_type));
    }

    /**
     * Loads a resource from a content string
     *
     * @param string $conent the contnet of the resource.
     * @param string $resource_type optional.  the type of the contained resource e.g. 'ValueSet'.  defaults to null
     * @return \FHIR_DSTU_TWO\FHIRResource the parsed resource
     */
    private function _load_resource_content($content, $resource_type =null) {
        if (! ( $resource = $this->parser->parse($content))  instanceof  FHIRResource
            || ( $resource_type && ! ($resource->get_fhirElementName() == $resource_type))
            ) {
            throw new Exception("Did not get a DSTU2 resource ($resource_type) from: $content");
        }    
        return  $resource;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
