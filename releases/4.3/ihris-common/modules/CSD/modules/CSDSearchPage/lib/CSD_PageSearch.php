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
* @package ihris
* @subpackage common
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class CSD_PageSearch
* 
* @access public
*/


class CSD_PageSearch extends I2CE_PageFormAuto {

    protected function save() {
	//does nothing.
    }

    public function isPost() {
        return parent::isPost() && $this->hasData();
    }

    public function isConfig() {
        return parent::isConfirm() && $this->hasData();
    }

    protected function hasData() {
        $path = array('form',$this->getPrimaryFormName(),'0','0','fields');
        $vals = $this->request();
        $walked = true;
        foreach ($path as $p) {
            if (!is_array($vals) || ! array_key_exists($p,$vals)) {
                $walkred  = false;
                break;
            }
            $vals = $vals[$p];
        }
        $hasValues = false;
        if ($walked && is_array($vals)) {
            foreach ($vals as $v) {
                $hasValues |= ($v !== '');
            }
        }
        return $hasValues;
    }


    protected $bad_result =false;

    protected function loadObjects() {
	parent::loadObjects();
        if ( ($this->isPost() || $this->hasData())
	    &&  ($searchObj = $this->getPrimary()) instanceof CSD_Search
	    && (I2CE_FormStorage::getStorageMechanism($searchObj->getName())) instanceof I2CE_FormStorage_CSDSearch
	    ) {
	    //this will populate "result" field
	    $searchObj->setID("1"); //so it will populate
            if ( ($maxField = $searchObj->getField('max')) instanceof I2CE_FormField_INT) {
                if ( $maxField->getValue() > 200) {
                    $maxField->setValue(200);
                }
            }
            if ( ($entityIDField = $searchObj->getField('entityID')) instanceof I2CE_FormField_STRING_LINE) {
                list($form,$id) = array_pad(explode('|', $entityIDField->getValue(),2),2,'');
                if ($id) {
                    $entityIDField->setValue($id);
                }
                
            }
	    $searchObj->populate(true);
            if (($matches = $searchObj->getField('matches')) instanceof I2CE_FormField_ASSOC_MAP_RESULTS
                && count($matches->getValue()) > 200) {
                $this->userMessage("To Many Results To Display.  Please refine your search");
                I2CE::raiseError("Too many results");
                $this->bad_result = true;
                $matches->setValue(array());
                if ( ($resultField = $searchObj->getField('result')) instanceof I2CE_FormField) {
                    $resultField->setValue('');
                }
            }
	}
	return true;
	
    }
    protected function getBaseTemplate() {
        return 'csd_search_form.html';
    }


    protected function loadHTMLTemplates() {
        $append_node = 'siteContent';
        if (array_key_exists('auto_template',$this->args)
            && is_array($this->args['auto_template']) ) {
            if ( array_key_exists('append_node',$this->args['auto_template']) 
                 && is_scalar($this->args['auto_template']['append_node']) 
                 && $this->args['auto_template']['append_node']) {
                $append_node = $this->args['auto_template']['append_node'];
            }
            $options = $this->args['auto_template'];
        } else {
            $options = array();
        }
        if (!array_key_exists('field_data',$options)
            || !is_array($opions['field_data'])) {
            $options['field_data']  = array();
        }

        if (! ($node = $this->template->appendFileById($this->getBaseTemplate(), 'div', $append_node )) instanceof DOMNode
            || ! ($searchNode = $this->template->getElementByID('search',$node)) instanceof DOMNode
            || ! ($resultsNode = $this->template->getElementByID('results',$node)) instanceof DOMNode
            ) {
            I2CE::raiseError("Could not load template:" . $this->getBaseTemplate());
            return false;
        }

        
        $s_options = $options;        
        $s_options['is_edit'] = true;
        $s_options['template']='csd_search_form_search.html';
        $s_options['field_data']['matches'] = array('enabled'=>false);
        $s_options['field_data']['result'] =  array('enabled'=>false);
        $searchGizmo = new I2CE_Gizmo_Form($this,$this->primaryObject,$s_options);
        $searchGizmo->generate($searchNode);

        if (($this->isPost() || $this->hasData()) && !$this->bad_result) {
            $r_soptions = $options;
            $r_options['is_edit'] = false;
            $r_options['template']='csd_search_form_results.html';
            foreach ($this->primaryObject->getFieldNames() as $field) {
                if ($field != 'matches' && $field !='result') {
                    $r_options['field_data'][$field] = array('enabled'=>false);
                }
            	 $r_options['field_data']['result'] =  array('enabled'=>false);
            }
            $r_options['display_order'] = 'matches,result';
            $resultsGizmo = new I2CE_Gizmo_Form($this,$this->primaryObject,$r_options);
            $resultsGizmo->generate($resultsNode);
        } else {
            $this->template->removeNode($resultsNode);
        }
        return true;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
