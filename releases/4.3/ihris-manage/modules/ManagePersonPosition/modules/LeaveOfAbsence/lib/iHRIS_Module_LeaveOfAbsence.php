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
* @package iHRIS
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.2.3
* @since v4.2.3
* @filesource 
*/ 
/** 
* Class iHRIS_Module_LeaveOfAbsence
* 
* @access public
*/


class iHRIS_Module_LeaveOfAbsence extends I2CE_Module {

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_leave_of_absence' => 'validate_form_leave_of_absence',
                );
    }

    /**
     * Checks to make sure the start and end dates are valid for leave of absence.
     * @param I2CE_FormField_DATE_YMD $field
     */
    public function validate_form_leave_of_absence( $form  ) {

        if ( isset( $form->start_date ) && !$form->start_date->isBlank() ) {

            $where = array(
                    'operator' => 'AND',
                    'operand' => array(
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'start_date',
                            'style' => 'lessthan_equals',
                            'data' => array( 'value' => $form->start_date->dbFormat() ),
                            ),
                        array(
                            'operator' => 'OR',
                            'operand' => array(
                                array(
                                    'operator' => 'FIELD_LIMIT',
                                    'field' => 'end_date',
                                    'style' => 'null',
                                    ),
                                array(
                                    'operator' => 'FIELD_LIMIT',
                                    'field' => 'end_date',
                                    'style' => 'greaterthan_equals',
                                    'data' => array( 'value' => $form->start_date->dbFormat() ),
                                    ),
                                ),
                            ),
                        ),
                        );
            $conflicts = I2CE_FormStorage::search( 'leave_of_absence', $form->getParent(), $where );
            if ( count( $conflicts ) > 0 ) {
                $form->setInvalidMessage( 'start_date', 'date_overlap' );
            }

            if ( isset( $form->end_date ) && !$form->end_date->isBlank() ) {
                if ( $form->end_date->before( $form->start_date ) ) {

                    $form->setInvalidMessage( 'end_date', 'before_start' );

                } else {

                    $where =  array(
                            'operator' => 'OR',
                            'operand'=> array(
                                array(
                                    'operator' => 'AND',
                                    'operand' => array(
                                        array(
                                            'operator' => 'FIELD_LIMIT',
                                            'field' => 'start_date',
                                            'style' => 'lessthan_equals',
                                            'data' => array( 'value' => $form->end_date->dbFormat() ),
                                            ),
                                        array(
                                            'operator' => 'OR',
                                            'operand' => array(
                                                array(
                                                    'operator' => 'FIELD_LIMIT',
                                                    'field' => 'end_date',
                                                    'style' => 'null',
                                                    ),
                                                array(
                                                    'operator' => 'FIELD_LIMIT',
                                                    'field' => 'end_date',
                                                    'style' => 'greaterthan_equals',
                                                    'data' => array( 'value' => $form->end_date->dbFormat() ),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                    array(
                                        'operator' => 'AND',
                                        'operand' => array(
                                            array(
                                                'operator' => 'FIELD_LIMIT',
                                                'field' => 'start_date',
                                                'style' => 'greaterthan_equals',
                                                'data' => array( 'value' => $form->start_date->dbFormat() ),
                                                ),
                                            array(
                                                'operator' => 'FIELD_LIMIT',
                                                'field' => 'end_date',
                                                'style' => 'lessthan_equals',
                                                'data' => array( 'value' => $form->end_date->dbFormat() ),
                                                ),
                                            ),
                                        ),
                                    ),
                                );
                    $conflicts = I2CE_FormStorage::search( 'leave_of_absence', $form->getParent(), $where );
                    if ( count( $conflicts ) > 0 ) {
                        $form->setInvalidMessage( 'end_date', 'date_overlap' );
                    }

                }
            } else {

                $where = array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'start_date',
                        'style' => 'greaterthan_equals',
                        'data' => array( 'value' => $form->start_date->dbFormat() ),
                        );
                $conflicts = I2CE_FormStorage::search( 'leave_of_absence', $form->getParent(), $where );
                if ( count( $conflicts ) > 0 ) {
                    $form->setInvalidMessage( 'start_date', 'date_overlap' );
                }


            }
        }

    }


    /**
     * Return the list of fuzzy methods supported by this module.
     * @return array
     */
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_leave_of_absence' => 'action_leave_of_absence',
            );
    }

    
    /**
     * Add the leave of absence forms to the page.
     * @param iHRIS_PageView $page
     * @return boolean
     */
    public function action_leave_of_absence($page) {
        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
        $page->addLastChildForm('leave_of_absence', 'start_date'); 
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
