<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 * @subpackage Manage
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v1.0.0
 * @version v2.0.0
 */
/**
 * Object for dealing with applicants.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_Applicant extends I2CE_Form {


    /**
     * Remove the given position from the list of positions for this application.
     * @param integer $position
     */
    public function removePosition( $position ) {
        if ( $position == "all" ) {
            $this->position = array();
        } else {
            $tmp_position = $this->position;
            $pos_arr = explode( '|', $position, 2 );
            if ( count($pos_arr) != 2 ) {
                $pos_arr = array( 'position', $position );
            }
            $key = array_search( $pos_arr, $tmp_position );
            if ( $key !== false ) {
                unset( $tmp_position[$key] );
            }
            $this->position = $tmp_position;
        }
    }



    /**
     * Search the database for applications that have applied to the given position.
     * @param integer $position
     * @return array
     * @see search()
     */
    public static function findApplications( $position ) {
        $apps = I2CE_FormStorage::listFields( "application", array( ), true, 
                array( 'operator' => 'OR',
                    'operand' => array(
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'position',
                            'style' => 'equals',
                            'data' => array( 'value' => 'position|' . $position )
                            ),
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'position',
                            'style' => 'like',
                            'data' => array( 'value' => '%,position|' . $position )
                            ),
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'position',
                            'style' => 'like',
                            'data' => array( 'value' => 'position|' . $position . ',%' )
                            ),
                         array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'position',
                            'style' => 'like',
                            'data' => array( 'value' => '%,position|' . $position . ',%' )
                            ),
                        )
                    )
                );
        return $apps;
    }
    /**
     * Search the database for applicants that have applied to the given position.
     * @param integer $position
     * @return array
     * @see search()
     */
    public static function findApplicants( $position ) {
        $apps = self::findApplications( $position );
        $pids = array();
        foreach( $apps as $appid => $data ) {
            $pids[] = str_replace( 'person|', '', $data['parent'] );
        }
        if ( count($pids) > 0 ) {
            $people = I2CE_Form::listFields( "person", array( "surname", "firstname" ), 
                    array( 'operator' => 'FIELD_LIMIT',
                        'field' => 'id',
                        'style' => 'in',
                        'data' => array( 'value' => $pids )
                        )
                    );
            return $people;
        } else {
            return array();
        }
    }

                
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
