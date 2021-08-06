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
 * @access public
 * @author Nobert Mijumbi <nmijumbi@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the manage people page.
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageSupervisee extends I2CE_Page {
        
    /**
     * Perform the main actions of the page.
     */
     
    protected function action() {
        
        if ( !parent::action() ) {
            return false;
        }
        
        $factory = I2CE_FormFactory::instance();
        $this->template->setAttribute( "class", "active", "menuManage", "a[@href='supervisor']" );
        $this->template->appendFileById( "supervisee_base.html", "ul", "menuManage" );
        
        $user_person_id = iHRIS_UserMap::getPersonId();
        if ( !$user_person_id) {
            if ( !$this->get_exists('person')) {
                $this->template->addFile('supervisee_list_error.html');
                return true;
            }else {
                $user_person_id = $this->get('person');
            }
        }
        
        $user = $factory->createContainer( $user_person_id );
    	$user->populateLast( array( "person_position" => "start_date" ) );
    	if ( !array_key_exists( 'person_position', $user->children ) || !is_array( $user->children['person_position'] ) || count($user->children['person_position']) == 0 ) {
    	    $this->template->addFile('supervisee_list_error.html');
        	return true;
    	}
    	foreach( $user->children['person_position'] as $user_person_position ) {
        	if ( $user_person_position->end_date->isValid() && $user_person_position->end_date->before( I2CE_Date::now() ) ) {
            	$this->template->addFile('supervisee_list_error.html');
            	return true;
        	}
        //get position of the supervisor
        $user_position = $user_person_position->getField("position")->getDBValue();
    	
        }
        
        $supervisee_positions = I2CE_Form::listFields( "position", array( "title" ),
                            array( 'operator' => 'FIELD_LIMIT',
                                'field' => 'supervisor',
                                'style' => 'equals',
                                'data' => array( 'value' => $user_position ) 
                                )
                        );
                       
                    $count = 0;
                    foreach( $supervisee_positions as $id => $data ) {
                    
                        $position = "position|" . $id ;
                        $person = $this->getPerson($position);
                        $person = $factory->createContainer( $person );
                        
                        $node = $this->template->appendFileById( "supervisee_list_entry.html", "li", "supervisee_list" );
                        $this->setForm($person,$node);
                    }
                    
          
    }
    
    protected function getPerson( $position ) {
        $where_data = array(
                      'operand' => 'AND',
                            array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'position',
                            'style' => 'equals',
                            'data' => array(
                                'value' => $position,
                                )
                            )
                            array(
                            'operator' => 'FIELD_LIMIT',
                            'field' => 'end_date',
                            'style' => 'null',
                                )
                            
                );
        $person = I2CE_FormStorage::listFields( 'person_position', array('parent'), false, $where_data );
        return $person;
    }
       
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
