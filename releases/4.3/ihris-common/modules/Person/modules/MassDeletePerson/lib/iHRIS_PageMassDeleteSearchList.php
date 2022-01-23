<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
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
 * Edit participants for a training
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.4
 * @version v4.1.4
 */

/**
 * The page class for editing particpants for a training
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageMassDeleteSearchList extends I2CE_PageReportAction { 

    /**
     * Return the action text to display in each cell based on the fields passed.
     * @param array $fields The field values for this row.
     * @return string
     */
    public function getActionText( $fields ) {
        return "Mark Person";
    }

    /**
     * Return the arguments to pass to the action method.
     * These arguments should be ready to pass directly to the javascript
     * method so must be quoted and escaped if needed.
     * @return array
     */
    public function getActionArguments() {
        return array( 'this' );
    }

    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        if ( !parent::action() ) {
            return false;
        }
        if (!$this->hasPermission("role(admin)")) {
            $this->userMessage("You do not have permission to view this page.");
            return false;
        }

        $this->template->addHeaderLink("view.js");
        //$this->template->appendFileById( "menu_view.html", "li", "navBarUL", true );

        return $this->actionReport();

    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
