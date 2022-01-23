<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
*/
/**
*  I2CE_Swiss_CustomReports_Report_Base
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


abstract class I2CE_Swiss_CustomReports_Report_Base extends I2CE_Swiss_CustomReports_Base {
    protected $swissReport = null;
    public function getBaseReport() {
        if ($this->swissReport === null) {
            $this->swissReport = $this->getAncestorByClass('I2CE_Swiss_CustomReports_Report');
            if (!$this->swissReport instanceof I2CE_Swiss_CustomReports_Report) {
                $this->swissReport = false;
            }
        }
        return $this->swissReport;
    }

    protected $swissReports = null;
    public function getSwissReports() {
        if ($this->swissReports === null) {
            $this->swissReports = $this->getAncestorByClass('I2CE_Swiss_CustomReports_Reports');
            if (!$this->swissReports instanceof I2CE_Swiss_CustomReports_Reports) {
                $this->swissReports = false;
            }
        }
        return $this->swissReports;

    }


    public function getSwissReport($report) {
        if ( ! ($swissReports = $this->getSwissReports()) instanceof I2CE_Swiss_CustomReports_Reports) {
            return false;
        }
        if ( ! ($child = $swissReports->getChild($report)) instanceof I2CE_Swiss_CustomReports_Report) {
            return false;
        }
        return $child;
    }

    protected  $relationshipFactory = null;
    protected  function setupRelationshipFactory() {
        if ($this->relationshipFactory instanceof I2CE_Swiss_FormRelationships) {            
            return $this->relationshipFactory;
        } else {
            $relConfig = $this->factory->getStorage('/')->traverse('../relationships',false,false);
            if (!$relConfig instanceof I2CE_MagicDataNode) {
                I2CE::raiseError("Bad magic data path to relationships");
                return false;
            }
            $init_options = array(
                'root_url_postfix'=>'relationships',
                'root_path'=>$relConfig->getPath(false),
                'root_type'=>'FormRelationships');
            try {
                $swiss_factory = new I2CE_SwissMagicFactory($this->factory->getPage(),$init_options);
            } catch (Exception $e) {
                I2CE::raiseError("Could not create swissmagic for relationship:" . $e->getMessage());
                return false;
            }
            try {
                $swiss_factory->setRootSwiss();
            } catch (Exception $e) {
                I2CE::raiseError("Could not create root swissmagic for relationships:" . $e->getMessage());
                return false;
            }            
            $this->relationshipFactory = $swiss_factory;
            return $this->relationshipFactory;
        }
    }


    /**
     * Get the reported fields for a report
     * @param string $report
     * @param boolean $get_disabled  Defaults to false
     * @returns array with keys the field name of the form "$form+$field" and values the header for the field
     */
    public  function getReportFields($get_disabled = false) {        
        $fields = array();
        $swissReport = $this->getBaseReport();
        if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
            return $fields;
        }
        $fields = array();
        $swissForms = $swissReport->getSwissForms();
        foreach ($swissForms as $swissForm) {
            $swissFields = $swissForm->getSwissFields();
            foreach ($swissFields as $swissField) {
                if ($get_disabled == false && !$swissField->isEnabled()) {
                    continue;
                }
                $fields[$swissForm->getName() . '+' . $swissField->getName()] = $swissField->getHeader();
            }
        }
        return $fields;
    }



    public function getRelationship() {
        $baseReport = $this->getBaseReport();
        if (!$baseReport instanceof I2CE_Swiss_CustomReports_Report) {
            return false;
        }
        return $baseReport->getRelationship();
    }


    public function getSwissRelationships() {
        if (!$this->setupRelationshipFactory()) {
            I2CE::raiseError("Could not setup relationship factory");
            return false;
        }       
        $swissRelationships = $this->relationshipFactory->getSwiss('/'); 
        if (!$swissRelationships instanceof I2CE_Swiss_FormRelationships) {
            return false;
        }
        return  $swissRelationships;
    }

    public function getSwissRelationship() {
        $relationship = $this->getRelationship();
        if ($relationship === false) {
            return false;
        }
        $swissRelationships = $this->getSwissRelationships();
        if (!$swissRelationships instanceof I2CE_Swiss_FormRelationships) {
            return false;
        }
        $swissRelationship = $swissRelationships->getChild($relationship);
        if (!$swissRelationship instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        return $swissRelationship;
    }


    public function getSwissRelationshipForm($form) {
        $swissRel = $this->getSwissRelationship();
        if (!$swissRel instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        if ($form == 'primary_form') {
            return $swissRel;
        } else {
            $forms = $swissRel->getExistingSwissForms();
            if (array_key_exists($form,$forms) && $forms[$form] instanceof I2CE_Swiss_FormRelationship) {
                return $forms[$form];
            } else {
                return false;
            }
        }
    }


    public function getSwissFormsInRelationship() {
        $swissRelationship = $this->getSwissRelationship();
        if (!$swissRelationship instanceof I2CE_Swiss_FormRelationship) {
            I2CE::raiseError("Could not get swiss relationship for " . $baseReport->getRelationship());
            return array();
        }
        return $swissRelationship->getExistingSwissForms();
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
