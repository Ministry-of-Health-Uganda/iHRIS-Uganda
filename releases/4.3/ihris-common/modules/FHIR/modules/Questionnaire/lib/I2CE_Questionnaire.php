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
* @subpackage FHIR
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.1
* @since v4.2.1
* @filesource 
*/ 
/** 
* Class I2CE_Questionnaire
* 
* @access public
*/

use FHIR_DSTU_TWO\FHIRDomainResource\FHIRQuestionnaire as FHIRQuestionnaire;
use FHIR_DSTU_TWO\PHPFHIRResponseParser as PHPFHIRResponseParser;

class I2CE_Questionnaire {
    
    public $object;
    public $url;
    public $parser;
    /**
     * @param $url the url where the FHIR resource can be loaded
     */
    public function __construct() {
        $this->parser = new PHPFHIRResponseParser();
        $this->object = new FHIRQuestionnaire();
    }


    public function loadResource($url) {
        $this->url = $url;
        $content = file_get_contents($this->url);
        I2CE::raiseError("Received from {$this->url}:\n$content");

        $this->object = $this->parser->parse($content);
        if (! $this->object instanceof  FHIRQuestionnaire) {
            throw new Exception("Did not get a DSTU2 Questionnaire resource");
        }    
    }

    /**
     * walk through the questionnaire and create form name with the given name
     * @param string $form the form name we want to call this questionnaire
     */
    public function createForm($form,$url) {
       
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
