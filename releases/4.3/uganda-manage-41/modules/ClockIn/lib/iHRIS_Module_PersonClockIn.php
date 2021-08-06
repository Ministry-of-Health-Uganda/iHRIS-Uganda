<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
*
* @package ihris-common
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2
* @since v3.2
* @filesource
*/
/**
* Class iHRIS_Module_PersonClockIn
*
* @access public
*/


class iHRIS_Module_PersonClockIn extends I2CE_Module {

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_clockin' => 'validate_form_clockin',
                );
    }

    /**
     * Checks to make sure the end date is after the start date for the person position.
     * @param I2CE_Form $form
     */
    public function validate_form_clockin( $form ) {
        if ( $form->time_in->isValid() && $form->time_out->isValid() ) {
            if ( $form->time_in->compare( $form->time_out ) < 1 ) {
                $form->getField('time_out')->setInvalid( "The Time-Out must be after the Time-In." );
            }
        }
        
     } 
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_clockin' => 'action_clockin'
            );
    }


    public function action_clockin($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        return $obj->addChildForms('clockin', 'siteContent');
    }

    public function showCurrentClockIn($node,$template) {
        return $this->showClockIn($node,$template,true);
    }

    public function showAllActions($node,$template) {
        return $this->showClockIn($node,$template,false);
    }


    protected function showClockIn($node,$template,$only_active) {
        $personForm = $template->getData('FORM','person',$node);
        if (!$personForm instanceof iHRIS_Person || $personForm->getId() =='0') {
            $node->parentNode->removeChild($node);
            return;
        }
        $node->removeAttribute('type');
        $node->removeAttribute('name');
        $clockins = iHRIS_Module_PersonClockIn::getPersonClockIns($personForm,$only_active);
        foreach ($clockins as $clockin) {
            $clockin->populate();
            $actionNode = $template->appendFileByNode('view_clockin.html','div',$node);
            if (!$actionNode instanceof DOMNode) {
                continue;
            }
            $template->setForm($clockin,$actionNode);
        }

    }


    public static function getPersonClockIns($person, $only_active = true) {
        $clockins = array();
        $factory = I2CE_FormFactory::instance();
        if (is_scalar($person)) {
            $person = $factory->createContainer($person);
        }
        if (!$person instanceof iHRIS_Person) {
            return $clockins;
        }
        if ($only_active) {
             $where = array(
                    'operator' => 'OR',
                    'operand' => array (
                        0 => array (
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'time_out',
                            'style'=> 'greaterthan_equals',
                            'data'=> I2CE_Date::now(I2CE_DATE::DATE_TIME)->getValues()
                            ),
                        1 => array (
                            'operator'=>'FIELD_LIMIT',
                            'field'=>'time_out',
                            'style'=> 'null',
                            )
                        )
                    );
        } else {
            $where = array();
        }
        $clockinIds = $person->getChildIds('clockin','time_in', $where); //get all clockins in order of increasing start date
        foreach ($clockinIds as $id) {
            $clockin = $factory->createContainer('clockin|'.$id);
            $clockin->populate();
            if (!$clockin instanceof iHRIS_ClockIn || $clockin->getId() == '0') {
                continue;
            }
            $clockins[] = $clockin;
        }
        return $clockins;
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
