<?php
/*
 * © Copyright 2014 IntraHealth International, Inc.
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
 * View a person's record.
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2014 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying user alerts
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 */
class iHRIS_PageAckUserAlerts extends I2CE_Page { 

    /**
     * Handle the actions for the page.
     * @return boolean
     */
    protected function action() {
        if ( !$this->get_exists('id') ) {
            $this->template->addFile( "user_alerts_acknowledge_invalid.html" );
            return true;
        } else {
            $user_alert = I2CE_FormFactory::instance()->createContainer( $this->get('id') );
            if ( !$user_alert instanceof iHRIS_UserAlert ) {
                $this->template->addFile( "user_alerts_acknowledge_invalid.html" );
                return  true;
            }
            $user_alert->populate();
            $user = $this->getUser();
            if ( !$user_alert->getParentId() ) {
                $this->template->addFile( "user_alerts_acknowledge_invalid.html" );
                return  true;
            }
            if ( $user_alert->getParentId() != $user->username &&
                    !$this->hasPermission("task(user_alerts_edit_all)") ) {
                $this->template->addFile( "user_alerts_acknowledge_perms.html" );
                return true;
            }
            if ( $user_alert->time_ack->equals( I2CE_Date::blank() ) ) {
                $user_alert->time_ack = I2CE_Date::now();
                $user_alert->save( $user );
            }
            if ( array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
                $this->setRedirect( $_SERVER['HTTP_REFERER'] );
            } else {
                $this->setRedirect("view_alerts");
            }
        }
        return true;
    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
