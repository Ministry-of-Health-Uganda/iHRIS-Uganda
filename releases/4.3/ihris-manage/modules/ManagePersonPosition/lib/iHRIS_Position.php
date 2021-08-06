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
 * Object for dealing with positions.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_Position extends I2CE_List {
    
    /**
     * Remove all fields except for the status when only dealing with status updates.
     */
    public function statusOnly() {
        $this->clearFields( array( 'status') );
    }
    
    /**
     * Update the status for this position.
     * @param integer $status
     */
    public function setStatus( $status ) {
        $this->fields['status']->setFromDB( $status );
    }
        
    /**
     * Mark this position as closed and remove it from any applications.
     * 
     * The person being assigned the position will have all application positions removed.
     * @param I2CE_User &$user The user performing this action.
     * @param integer $person_id The person being assigned this position.
     */
    public function closePosition( &$user, $person_id ) {
        $factory = I2CE_FormFactory::instance();
        $this->setStatus( "position_status|closed" );
        $this->save( $user );
        $mod_factory = I2CE_ModuleFactory::instance();
        if ( $mod_factory->isEnabled( 'ihris-manage-Application' ) ) {
            $apps = iHRIS_Applicant::findApplications( $this->id );
            if ( count( $apps ) > 0 ) {
                foreach ( $apps as $app_id => $data ) {
                    $app = $factory->createContainer( "application|" . $app_id );
                    $app->populate();
                    $app->removePosition( $this->getNameId() );
                    $app->save( $user ); 
                }
            }
        }
    }
        
    /**
     * Return the list of record ids that have this position as supervisor.
     * @return array
     */
    public function getSupervised() {
        $where_data = array(            
            'operator'=>'FIELD_LIMIT',
            'field'=>'supervisor',
            'style'=>'equals',
            'data'=>array(
                'value'=>$this->getNameId()
                )
            );
        return  I2CE_FormStorage::search('position',false,$where_data);               
    }
        
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
