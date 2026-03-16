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
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FHIR_ValueSet
* 
* @access public
*/


use FHIR_DSTU_TWO\FHIRDomainResource\FHIRValueSet as FHIRValueSet;
use FHIR_DSTU_TWO\FHIRResource\FHIRValueSet\FHIRValueSetCodeSystem as FHIRValueSetCodeSystem;
use FHIR_DSTU_TWO\FHIRResource\FHIRValueSet\FHIRValueSetConcept as FHIRValueSetConcept; 
use FHIR_DSTU_TWO\FHIRElement\FHIRCode as FHIRCode;
use FHIR_DSTU_TWO\FHIRElement\FHIRString as FHIRString;
 
class I2CE_FHIR_ValueSet extends I2CE_FHIR_Base{


    /**
     * Gets an associative array with keys the code and values the display name
     * @returns array()
     */
    public function get_associative() {
        if (!$this->resource instanceof FHIRValueSet
            || !( $code_system = $this->resource->codeSystem) instanceof FHIRValueSetCodeSystem
            || !(is_array($concepts = $code_system->concept)) 
            ) {
            throw new Exception("Not a DSTU2 Valueset resource");
        }
        $values = array();
        foreach ($concepts as $concept) {
            if (!$concept instanceof FHIRValueSetConcept
                || ! ($code = $concept->code) instanceof FHIRCode
                || ! is_string($val = $code->value)
                || strlen($code) == 0
                ) {
                continue;
            }
            $disp = $val;
            if (($concept->display) instanceof FHIRString
                && ($concept->display->value)
                ) {
                $disp = $concept->display->value;
            }
            $values[$val] = $disp;
        }
        return $values;
    }

    /**
     * Gets an represnetation of the ValueSet suiteable for loading in magic data
     * @returns array()
     */
    public function get_simple_list() {
        $values = $this->get_associative();
        $md = array();
        foreach ($values as $k=>$v) {
            $k = str_replace(array('=','/'),array('&#61;','&#x2F;'),$k);
            if (!I2CE_MagicDataNode::checkKey($k)) {
                I2CE::raiseError("Invalid code for magic data $k");
                continue;
            }
            $md[$k] = array(
                'fields'=>array(
                    'name'=>$v
                    )                
                );            
        }
        return $md;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
