<?php
/*
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
 */
/**
 * @package iHRIS
 * @subpackage Common
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v4.2.0
 * @version v4.2.0
 */
/**
 * iHRIS_UserAlert class for the user_alert form.
 *
 * @package iHRIS
 * @subpackage Common
 */
class iHRIS_UserAlert extends I2CE_Form {


    /**
     * Check for duplicates and save the user alert
     * @param I2CE_User $user
     * @param boolean $transact
     * @return boolean
     */
    public function save( $user, $transact=true ) {
        if ( $this->getId() === '0' ) {
            $find_duplicates = array( 
                    'operator' => 'AND',
                    'operand' => array(
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'message',
                            'style' => 'lowerequals',
                            'data' => array( 'value' => strtolower( $this->message ) )
                            ),
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'time_ack',
                            'style' => 'null'
                            ),
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'alert_type',
                            'style' => 'equals',
                            'data' => array( 'value' => $this->alert_type )
                            ),
                        )
                    );
            if ( $this->link == '' ) {
                $find_duplicates['operand'][] = array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'link',
                        'style' => 'null'
                        );
            } else {
                $find_duplicates['operand'][] = array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'link',
                        'style' => 'equals',
                        'data' => array( 'value' => $this->link )
                        );
            }
            if ( $this->link_text == '' ) {
                $find_duplicates['operand'][] = array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'link_text',
                        'style' => 'null'
                        );
            } else {
                $find_duplicates['operand'][] = array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'link_text',
                        'style' => 'lowerequals',
                        'data' => array( 'value' => strtolower( $this->link_text ) )
                        );
            }
            $found = I2CE_FormStorage::search( 'user_alert', $this->getParent(), $find_duplicates, array( "-time_sent" ), 1 );
            if ( $found ) {
                I2CE::raiseMessage("found duplicates so increasing repeats. $found");
                $duplicate = I2CE_FormFactory::instance()->createContainer( "user_alert|" . $found );
                $duplicate->populate();
                $duplicate->repeated++;
                return $duplicate->save( $user, $transact );
            }
        }
        return parent::save( $user, $transact );
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
