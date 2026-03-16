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
*  I2CE_SwissConfig_FormRelationship_Join
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_FormRelationship_Join extends  I2CE_Swiss_FormRelationship {

    public function processValues($vals) {
        $do_style = false;
        $do_field = false;
        if (array_key_exists('limit_one', $vals) && $vals['limit_one'] == 0 ) {
            $this->setField('limit_one',0);
        } else {
            //default to limit one
            $this->setField('limit_one',1);
        }
        if (array_key_exists('displaystyle', $vals)) {
            $this->storage->join_data->displaystyle = $vals['displaystyle'];
        }
        if (array_key_exists('drop_empty', $vals)) {
            if ($vals['drop_empty'] == '2') {
                $this->setField('drop_empty', 2);
            } else if ($vals['drop_empty'] == '1') {
                $this->setField('drop_empty', 1);
            } else{
                $this->setField('drop_empty', 0);
            }
        }
        if (array_key_exists('join_style',$vals)) {
            $do_style = true;
            if (!$this->hasParent()) {
                I2CE::raiseError("No parent");
                return false;
            }
            if (!in_array($vals['join_style'], array('parent','child','parent_field','child_field','reference'))) {
                I2CE::raiseError("Invalid join_style:{$vals['join_style']}");
                return false;
            }
            if (in_array($vals['join_style'], array('reference','parent_field','child_field'))){
                $do_field = true;
                if (!array_key_exists('join_field',$vals) || !is_scalar($vals['join_field']) || !$vals['join_field']) {
                    I2CE::raiseError("Invalid joinfield");
                    return false;
                }
                if ($vals['join_style'] == 'parent_field') {
                    $parent = $this->getParent()->getAncestorByClass('I2CE_Swiss_FormRelationship');
                    if (!$parent instanceof I2CE_Swiss_FormRelationship) {
                        I2CE::raiseError("Invalid parent");
                        return false;
                    }
                    $formName = $parent->getForm();
                } else if ($vals['join_style'] == 'reference') {
                    $parent = $this->getParent()->getAncestorByClass('I2CE_Swiss_FormRelationship');
                    if (!$parent instanceof I2CE_Swiss_FormRelationship) {
                        I2CE::raiseError("Invalid parent");
                        return false;
                    }
                    $formName = $parent->getForm();
                } else {
                    if (!array_key_exists('form',$vals)) {
                        I2CE::raiseError("No form specified");
                        return false;
                    }
                    $formName = $vals['form'];
                }
                $factory = I2CE_FormFactory::instance();
                $formObj= $factory->createContainer($formName);
                if (!$formObj instanceof I2CE_Form) {
                    I2CE::raiseError("Invalid form $formName");
                    return false;
                }                    
                $fields = $factory->getFieldNames($formName,array('in_db'=>true));
                if (!in_array($vals['join_field'],$fields)) {
                    I2CE::raiseError("Invalid field" . $vals['join_field'] . "\n" . print_r($fields,true));
                    return false;
                }
            }
        } 
        if (!parent::processValues($vals)) {
            return false;
        }
        if ($do_field){ 
            $this->storage->join_data->field = $vals['join_field'];
        }
        if ($do_style) {
            $this->storage->join_style = $vals['join_style'];
        }
        return true;
    }


    public function getDescription() {
        $joinDesc = parent::getDescription();
        $joinStyle = '';
        $this->storage->setIfIsSet($joinStyle,'join_style');
        $child = null;
        if (!$this->hasParent()) {
            return $joinDesc;
        }
        $parent = $this->getParent()->getAncestorByClass('I2CE_Swiss_FormRelationship');
        if (!$parent instanceof I2CE_Swiss_FormRelationship) {
            return $joinDesc;
        }
        $parentForm = $parent->getStorage()->getName();
        switch ($joinStyle) {
        case 'parent':
            $joinDesc .= 'This form is a parent of ' . $parentForm . '.';        
            break;
        case 'child':            
            $joinDesc .= 'This form is a child of ' . $parentForm . '.';                
            break;
        case 'child_field':            
            $field = '<NOT SPECIFIED>';
            $this->storage->setIfIsSet($field,"join_data/field");
            $joinDesc .= 'This form a links to ' . $parentForm . ' via one the field ' . $field. '.';               
            break;
        case 'parent_field':
            $field = '<NOT SPECIFIED>';
            $this->storage->setIfIsSet($field,"join_data/field");
            $joinDesc .= 'This form is  linked from ' . $parentForm . ' via the field '  . $field. '.';                
            break;
        case 'reference':
            $field = '<NOT SPECIFIED>';
            $this->storage->setIfIsSet($field,"join_data/field");
            $joinDesc .= 'This form is  linked from ' . $parentForm . ' via the field '  . $field. '.';                
            break;
        default:
            break;
        }
        return $joinDesc;
    }



    /**
     * Displays the meta data about this form
     * @param mixed$configPath
     * @param mixed $configPath
     * @param DOMNode $appendNode The page template node from which we wish to add the join menu to
     * @returns boolean true  on success
     */
    protected function displayMetaData( $action,$node ) {
        return parent::displayMetaData('view',$node);        
    }


    protected function showDropEmpty($action,$contentNode) {
        $file = 'formRelationship_join_drop_empty_' . $action . '.html';
        $dropNode = $this->template->appendFileByNode($file,'div',$contentNode);        
        if (!$dropNode instanceof DOMNode) {
            I2CE::raiseError("Could not load template for drop data");
            return false;
        }
        $drop = $this->getField('drop_empty');
        if ($drop == '') {
            $drop = '0';
        }
        if ($action == 'edit') {
            $radio = $this->template->getElementById('drop_empty_' . $drop,$dropNode);
            if ($radio instanceof DOMElement) {
                $radio->setAttribute('checked','checked');
            }
            $limit = $this->getField('limit_one');
            if ($limit != '1') {
                $limit = '0';
            }     
            $radio = $this->template->getElementById('limit_one_' . $limit,$dropNode);
            if ($radio instanceof DOMElement) {
                $radio->setAttribute('checked','checked');
            }
            $this->renameInputs(array('limit_one','drop_empty'),$dropNode);
        } else { //view
            $this->template->setDisplayDataImmediate('do_drop_empty',$drop,$dropNode);
        }
    }




    protected function showDisplayStyle($action,$contentNode) {
        $file = 'formRelationship_join_displaystyle_' . $action . '.html';
        $dsNode = $this->template->appendFileByNode($file,'div',$contentNode);        
        if (!$dsNode instanceof DOMNode) {
            I2CE::raiseError("Could not load template for display style data");
            return false;
        }
        $displaystyle =null;
        $this->storage->setIfIsSet($displaystyle,"join_data/displaystyle");
        if (!$displaystyle) {
            $displaystyle ='default';
        }
        $this->template->setDisplayDataImmediate('displaystyle',$displaystyle,$dsNode);
        if ($action == 'edit') {            
            $this->renameInputs(array('displaystyle'),$dsNode);
        }
    }

    protected function manageFunctions($action, $node, $transient_options) {
        $this->template->setDisplayDataImmediate('reporting_functions_link', '', $node);
    }


    protected function displayAncestors($contentNode,$transient_options,$action) {
        $ancForms = $this->getAncestorFormNames();
        if (count($ancForms) == 0) {
            I2CE::raiseError("DAnc(count=0):{$this->path}");
            return parent::displayAncestors($contentNode,$transient_options,$action);
        }
        $id = 'relationship_ancestors';
        $node = $this->template->getElementById($id);
        $swissChild = $this->getChild('ancestral_conditions',true);
        if (!$swissChild instanceof I2CE_Swiss) {
            $this->template->removeNode($node);
            I2CE::raiseError("bad child");
            return true;
        }
        $swissChild->addAjaxLink($id .'_link','relationship_conditions_container:' . $this->path . '/ancestral_conditions', $id  . '_ajax' ,$contentNode,$action, $transient_options);        
        return true;
    }



    public function getChildType($child) {
        if ($child == 'ancestral_conditions') {
            return 'FormRelationship_AncestralConditions';
        } else {
            return parent::getChildType($child);
        }
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
