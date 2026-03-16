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
*  I2CE_SwissConfig_FormRelationship_Where_Operands
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_Where_Operands extends I2CE_Swiss {


    public function getFormName() {
        if ($this->parent instanceof I2CE_Swiss_Where) {
            return $this->parent->getFormName();
        }
        return null;
    }

    public function getForm() {
        if ($this->parent instanceof I2CE_Swiss_Where) {
            return $this->parent->getForm();
        }
        return null;
    }


    public function getChildType($child) {
        return 'Where';
    }


    public function processValues($vals) {
        if (!array_key_exists('action',$vals)) {            
            return true; //nothing to do
        }
        if ($vals['action'] != 'Add') {
            return true; //nothing to do
        }
        $keys = $this->getChildNames();
        if (count($keys) == 0) {
            $key = '0';
        } else {
            $parent = $this->getParent();
            if (!$parent instanceof I2CE_Swiss) {
                I2CE::raiseError("Invalid Parent " .get_class($parent));
                return false;
            }
            $operator = $parent->getField('operator');
            if ($operator === 'NOT') {
                I2CE::raiseError("Trying to add to a NOT when there is already one");
                return false;
            } 
            $key = max($keys) + 1;
        }        
        $this->storage->$key->operator; //touch the new key so it exists  and make the operator node there.
        return true;
    }


    public function displayValues( $contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('swiss_existing_operand.html','div',$contentNode);
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not load template for existing operands");
            return false;
        }
        $parent = $this->getParent();
        if (!$parent instanceof I2CE_Swiss) {
            I2CE::raiseError("Invalid Parent " .get_class($parent));
            return false;
        }
        $operator = $parent->getField('operator');
        $this->template->setDisplayDataImmediate('operator_name', $operator,$mainNode);
        

        //existing wheres
        $appendNode = $this->template->getElementById('relationship_existing_where',$mainNode);
        if (!$appendNode instanceof DOMNode) {
            I2CE::raiseError("Could not figure out where to put existing limits");
            return false;
        }
        $newAppendNode = $this->template->getElementById('relationship_existing_where',$mainNode);
        if (!$newAppendNode instanceof DOMNode) {
            I2CE::raiseError("Could not figure out where to put new limits");
            return false;
        }

        if (!$this->displayExistingOperands($appendNode, $transient_options, $action)) {
            I2CE::raiseError("Could not display existing operands");
            return false;
        }
        if (!$this->displayNewWhere($newAppendNode, $transient_options, $action)) {
            I2CE::raiseError("Could not display new where menu");
            return false;
        }
        return true;
    }



    protected function displayExistingOperands ($contentNode, $transient_options,$action) {
        $children = $this->getChildNames();
        if (count($children) == 0) {
            return true;
        }
        $appendNode = $this->template->appendFileByNode('swiss_existing_operand_list.html','div',$contentNode);
        $ulNode = $this->template->getElementById('existing_operand_list',$appendNode);
        if (!$ulNode instanceof DOMNode) {
            I2CE::raiseError("Could not find template for existing limits");
            return false;
        }

        foreach ($children as $child) {
            $swissChild = $this->getChild($child);
            if (!$swissChild instanceof I2CE_Swiss) {
                continue;
            }
            $linode = $this->template->appendFileByNode('swiss_existing_operand_list_member_' . $action . '.html','li',$ulNode);
            if (!$linode instanceof DOMElement) {
                I2CE::raiseError("Could not find template to display an existing limit");
                return false;
            }
            $operator = $swissChild->getField('operator');
            switch ($operator) {
            case 'XOR':
            case 'AND':
            case 'NOT':
            case 'OR':
                $name = $operator;
                break;
            case 'FIELD_LIMIT':
                $name = $swissChild->getField('field') ;
                if ($swissChild->getField('style')) {
                    $name .= ' -- ' . $swissChild->getField('style');
                }
                break;
            case '':
                $name = "Unspecified Limit";
                break;
            default:
                I2CE::raiseError("Unrecognized $operator");
                continue 2;
            }
            $delete_link = $swissChild->getURLRoot('delete')  .  $swissChild->path .$swissChild->getURLQueryString();
            $this->template->setDisplayDataImmediate('delete_existing_limit_link', $delete_link, $linode);
            $this->template->setDisplayDataImmediate('existing_limit_name',$name,$linode);
            $swissChild->addAjaxLink('existing_limit_link','relationship_where_container',  'existing_limit_ajax' ,$linode,$action, $transient_options);
        }
        return true;
    }




    protected function displayNewWhere($contentNode, $transient_options,$action) {
        if ($action == 'view') {
            return true;
        }
        $parent = $this->getParent();
        if (!$parent instanceof I2CE_Swiss) {
            I2CE::raiseError("Invalid Parent " .get_class($parent));
            return false;
        }
        $operator = $parent->getField('operator');
        $children = $this->getChildNames();
        if ( !in_array($operator,array('NOT','XOR','AND','OR'))) { //really should not be here.  this is extra failsage
            //should not have any thing to display.               
            if (count($children) > 0) {
                $this->userMessage("Operator $operator has " . $results->length . " operands which is unexpected",'notice',false);
            }
            return true;
        }
        if ( $operator == 'NOT') {
            //see if we have an operand already
            if (count($children) > 0) {
                if (count($children) > 1) {
                    $this->userMessage("Warning operator NOT has more than one operand at " . $this->configPath , 'notice',false);
                }
                return true;
            }
        }
        // insert stuff to add a new operand.
        $newNode = $this->template->appendFileByNode("swiss_new_operand.html",'div',$contentNode);
        if (!$newNode instanceof DOMNode ) {
            I2CE::raiseError("Was not able to  add new operand template");
            return false;
        }
        $this->renameInputs(array('action'),$newNode);
        return true;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
