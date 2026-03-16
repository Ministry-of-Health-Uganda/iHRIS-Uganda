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
* @package ihris-manage
* @subpackage confirmation
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class iHRIS_Module_Confirmation
* 
* @access public
*/


class iHRIS_Module_Confirmation extends I2CE_Module {

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_confirmation' => 'action_confirmation'
            );
    }


    public function action_confirmation($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        return $obj->addChildForms('confirmation');
    }


    /**
     * Gets the list (in the format of {I2CE_FormStorage::listFields()} of allowed confirmations for the indicated person
     * @param iHRIS_Person $person
     * @param boolean $only_new.  Defaults to false.  it true, we only check for confirmations that this person does not already have
     * @returns array
     */
    public function getAllowedConfirmations($person,$only_new=false) {
        if (!$person instanceof iHRIS_Person) {
            return array();
        }
        $allowed = array();
        $conf_type_fields = array('name','job','probation_period');
        $confirmations = I2CE_FormStorage::listFields('confirmation_type',$conf_type_fields);        
        if (count($confirmations) == 0) {
            return array();
        }
        foreach ($confirmations as $id=>$vals) {
            $job = false;
            if (array_key_exists('job',$vals) && $vals['job']) {
                $job = $vals['job'];
            }
            //there is no job associted to this confirmation, all positions are valid.
            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'start_date',
                'style'=>'not_null'
                );
            if ($job) {
                $pers_positions = I2CE_FormStorage::listFields('person_position',array('start_date','position'),$person->getNameId(),$where,'start_date');            
                if (!is_array($pers_positions) || count($pers_positions) == 0) {
                    continue;
                }
                $position = false;
                foreach ($pers_positions as $pp_id=>$pp_data) {
                    //we need to check if this posiiton has the correct job
                    $pos_id = substr($pp_data['position'],9); 
                    if  ( !$pos_id || ! ($pos_job = I2CE_FormStorage::lookupField('position', $pos_id,'job'))) {
                        continue;
                    }
                    if ($pos_job != $job) {
                        continue;
                    }
                    $position = $pp_data;
                    break;
                }
                if (!$position) { 
                    //no mathching posiiton
                    continue;
                }
                        
            }else {
                $positions = I2CE_FormStorage::listFields('person_position',array('start_date'),$person->getNameId(),$where,'start_date',1);
                if (!is_array($positions) || count($positions) == 0) {
                    //person has no valid positions
                    continue;
                }
                reset($positions);
                $position = current($positions);
            }
            if (array_key_exists('probation_period',$vals) && $vals['probation_period'] > 0) {
                if (!array_key_exists('start_date',$position) || !$position['start_date']) {
                    //there was never a position for which can consider this person valid
                    continue;
                }            
                //now we need to check the probationary period
                $pos_start_date = I2CE_Date::fromDB($position['start_date']);
                if (!$pos_start_date->isValid()) {
                    continue;
                }
                //probation period is measure in months.  this is an approximation.  we really need to use the php 5.3 date time class.
                $prob_valid_month = mktime(0, 0, 0, date("m")- ((int) $vals['probation_period']), date("d"),   date("Y"));
                if ($prob_valid_month === false) {
                    I2CE::raiseError("Difficulty in calculating date offest");
                    continue;
                }
                $prob_valid = I2CE_Date::now(I2CE_Date::DATE, $prob_valid_month);
                if (!$prob_valid->isValid() || $prob_valid->before($pos_start_date) ) {  //comprare returns -1 if  $prob_valid is before $pos_start_data
                    continue;
                }

                //we have a position which is  valid for this confirmation type
            }
            if ($only_new) {
                $confirm_where = 
                    array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>'confirmation_type',
                        'style'=>'equals',
                        'data'=>array(
                            'value'=>'confirmation_type|' . $id
                            )
                        );
                if (count(I2CE_FormStorage::search('confirmation',$person->getNameId(),$confirm_where)) > 0) {
                    continue;
                }
            }
            //we made it to here, then the confirmation type is allowed
            $allowed[$id] = $vals;
        }
        return $allowed;
    }


    /**
     * checks to see if there are any valid confirmations for the indicated person
     * @param iHRIS_Person $person
     * @param boolean $only_new.  Defaults to false.  it true, we only check for confirmations that this person does not already have
     * @returns boolean
     */
    public function hasAllowedConfirmations( $person, $only_new = false ) {
        $allowed = $this->getAllowedConfirmations($person,$only_new);
        return (count($allowed) > 0);
    }


    /**
     * 
     */
    public function addAllowedConfirmations($node,$template, $person) {
        if (!$node instanceof DOMElement || ! ($parent = $node->parentNode) instanceof DOMElement || !$template instanceof I2CE_Template) {
            return;
        }
        $allowedConfirmations = $this->getAllowedConfirmations($person,true); //get the new confirmations
        if ( count($allowedConfirmations) == 0) {
            return;
        }                
        $imported = $template->loadFile( "add_confirmation.html", "span" );
        if (!$imported instanceof DOMNode) {
            I2CE::raiseError("Could not find add_conirmation.html");
            return ;
        }    
        if (! ($conf = I2CE_FormFactory::instance()->createForm('confirmation')) instanceof iHRIS_Confirmation) {
            return;
        }
        if ( ! ($type = $conf->getField('confirmation_type')) instanceof I2CE_FormField) {
            return;
        }
        $typeName = $type->getHTMLName();

        foreach ($allowedConfirmations as $id=>$vals) {
            if (! ($disp = I2CE_List::lookup($id,'confirmation_type')) ) {
                continue;
            }
            $imported_node = $imported->cloneNode(true);
            if ( ! ($linkNode = $template->getElementById('confirmation_link', $imported_node)) instanceof DOMElement) {
                continue;
            }
            $parent->insertBefore($imported_node,$node);
            $linkNode->setAttribute('href',"confirmation?" . $typeName . "=" . urlencode("confirmation_type|".$id) . '&parent=');            
            $template->setDisplayDataImmediate('confirmation_name',$disp,$imported_node);
        }
        $parent->removeChild($node);
    }
    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
