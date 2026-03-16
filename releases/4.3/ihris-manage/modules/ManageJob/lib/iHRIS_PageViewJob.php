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
 * View a job's record.
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since Demo-v2.a
 * @version Demo-v2.a
 */

/**
 * The page class for displaying a job's record.
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageViewJob extends I2CE_Page {

    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        parent::action();
        $this->template->addHeaderLink("view.js");
        $this->template->appendFileById( "menu_view.html", "li", "navBarUL", true );
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuLists", "a[@href='lists']" );
        $factory = I2CE_FormFactory::instance();
        $job = $factory->createContainer( $this->get('id') );
        $job->populate();
        $this->template->setForm( $job );

        $positions = $job->getPositions();
        //print_r( $supervised );
                
        if ( count($positions) > 0 ) {
            $this->template->appendFileById( "view_job_positions.html", "div", "job" );
            foreach( $positions as $record ) {
                $this->template->appendFileById( "view_position_row.html", "tr", "positions" );
                $this->template->setDisplayData( "position", I2CE_List::lookup( $record, "position" ) );
                $this->template->setDisplayData( "add_pos_id", array("id" => "position|" . $record) );
            }
            $this->template->setAttribute( "class", "even", "positions", "tr[position() mod 2 = 0]" );
        }

    }
        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
