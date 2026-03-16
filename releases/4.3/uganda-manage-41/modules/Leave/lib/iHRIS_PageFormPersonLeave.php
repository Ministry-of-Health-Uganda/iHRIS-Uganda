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
 * View a person's training record for a cadre.
 * @package iHRIS
 * @subpackage ihris-manage
 * @access public
 * @author Sovello Hildebrand Mgani <sovellohpmgani@gmail.com
 * @copyright Copyright &copy; 2007, 2008 - 2013 IntraHealth International, Inc. 
 * @version v4.1
 */

/**
 * The page class for displaying the a person's leave request record
 * @package iHRIS
 * @subpackage ihris-manage
 * @access public
 */
class iHRIS_PageFormPersonLeave extends I2CE_Page {
        
   /**
     * Create and load data for the objects used for this form.
     */
    protected function loadObjects() {
        parent::loadObjects();
        if ( !$this->isPost() ) {
            $parent = $this->getParent();
            $parent->populateChildren( array( "personleave" => "days_requested" ) );
            $last = current( $parent->children['license'] );
            if ( $last )
                $this->getPrimary()->license_number = $last->license_number;
        }
    }
        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
