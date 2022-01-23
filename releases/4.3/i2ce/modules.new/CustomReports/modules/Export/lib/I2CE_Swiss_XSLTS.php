<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @subpackage customreports
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_Siwss_XSLTS
* 
* @access public
*/


class I2CE_Swiss_XSLTS extends I2CE_Swiss{
    
    /**
     * Get the swiss child type
     * @param string $child
     * @returns string
     */
    public function getChildType($child) {
        return 'XSLT';
    }



    
    public function displayValues($contentNode, $transient_options,$action) {
        if (!($mainNode = $this->template->appendFileByNode('swiss_xslts.html','div',$contentNode)) instanceof DOMNode) {
            return false;
        }
        $existingTransforms = $this->getChildNames();
        if ($action === 'edit') {
            if (count($existingTransforms) > 0) {
                $transformNode = $this->template->getElementById('xslt_name',$contentNode);
                if (!$transformNode instanceof DOMNode) {
                    I2CE::raiseError("Transform name node could not be found");
                    return false;
                }
                $this->template->setClassValue($transformNode,'validate_data',array('notinlist'=>$existingTransforms), '%');
            }
            $formfields = I2CE::getConfig()->getAsArray('/modules/forms/FORMFIELD');
            $this->template->setDisplayDataImmediate('formfield',$formfields,$mainNode);            
            if (!$this->addAjaxOptionMenu('new_xslt', 'xslt_container', $contentNode)) {
                return false;
            }
        }
        if (count($existingTransforms) == 0) {
            $this->template->setDisplayDataImmediate('has_existing_xslts','',$mainNode);
            return true;
        } 
        $this->template->setDisplayDataImmediate('has_existing_xslts',1,$mainNode);

        $addNode = $this->template->getElementById('existing_xslt_list',$mainNode);
        if (!$addNode instanceof DOMNode) {
            I2CE::raiseError("Don't know where to add existing transforms");
            return false;
        }
        foreach ($existingTransforms as $transform) {
            $swissChild = $this->getChild($transform);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }            
            $liNode = $this->template->appendFileById('swiss_xslts_each.html','li',$addNode);
            if (!$liNode instanceof DOMNode) {
                I2CE::raiseError("Cannot add existing transform template");
            }
            $this->template->setDisplayDataImmediate('name',$transform,$liNode);
            $delete_link = $swissChild->getURLRoot('delete')  .  $swissChild->path .$swissChild->getURLQueryString();
            $this->template->setDisplayDataImmediate('xslt_delete_link',$delete_link,$liNode);
            $swissChild->addAjaxLink('xslt_link','xslt_content',  'xslt_ajax' ,$liNode,$action, $transient_options);            
        }
        return true;
    }


    /**
     * Update config for given values -- creates a new reportung fynction
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */    
    public function processValues($vals) {
        if (!array_key_exists('name', $vals) || !$vals['name']) {
            $this->userMessage("No  name provided");
            //no name provided.
            return false;
        }
        if (!I2CE_MagicDataNode::checkKey($vals['name']) || strpos($vals['name'],'+') !== false ) {
            $this->userMessage("An invalid name has been provided.  It must consist only of letters, numbers, _, and -");
            return false;
        }
        $existingTransforms = $this->getChildNames();
        if (in_array($vals['name'], $existingTransforms)) {
            $this->userMessage("Name {$vals['name']} is already being used");
            I2CE::raiseError("Name {$vals['name']} is already being used");
            return false;

        }
        $swissTransform = $this->getChild($vals['name'],true);
        if (!$swissTransform instanceof I2CE_Swiss_XSLT) {
            I2CE::raiseError("XSLT  Badness");
            return false;
        }
        return $swissTransform->processValues($vals);
    }

    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
