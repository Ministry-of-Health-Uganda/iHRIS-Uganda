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
 * View a position's record.
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying a position's record.
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageViewPosition extends I2CE_Page {

    protected $position = null;
    /**
     * Initializes any data for the page
     * @returns boolean.  True on sucess. False on failture
     */
    protected function initPage() {
        $factory = I2CE_FormFactory::instance();
        $this->position = $factory->createContainer( $this->get('id') );
        if (!$this->position instanceof iHRIS_Position) {
            return false;
        }
        $this->position->populate();
        $this->template->setForm( $this->position );
        if (  ($facilityField = $this->position->getField('facility')) instanceof I2CE_FormField_MAP) {
            if ( ($facility = $facilityField->getMappedFormObject()) instanceof I2CE_Form) {
                $this->template->setForm($facility);
            }
        }
        if (!$this->hasPermission('task(can_view_database_list_position)')) {
            $this->userMessage("You cannot view position information", 'notice',false);
            return;
        }
        return parent::initPage();
    }


    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        $factory = I2CE_FormFactory::instance();
        if ( $this->get_exists('set_status') ) {
            $new_status = $this->get('set_status');
            $set_status = $factory->createContainer( $this->get('id') );
            $set_status->statusOnly();
            $set_status->setStatus( "position_status|" . $new_status );
            $set_status->save( $this->user );
            $set_status->cleanup();
            unset( $set_status );
        }
        parent::action();
        $this->template->addHeaderLink("view.js");
        $this->template->appendFileById( "menu_view.html", "li", "navBarUL", true );
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuLists", "a[@href='lists']" );
        if ( ($jobField = $this->position->getField( "job" )) instanceof I2CE_FormField) {
            $jobField->setHref( "view_job?id=" );
        }
        if ( ($supField =   $this->position->getField( "supervisor" )) instanceof I2CE_FormField) {
            $supField->setHref( "view_position?id=" );
        }


        if( $this->position->status[1] != "closed" ) {
            $this->template->appendFileById( "view_position_" . $this->position->status[1] . "_link.html", 
                    "li", "edit_links" );
        }
                
        $pers_pos_ids = iHRIS_PersonPosition::getIds( $this->get('id') );
                
        //print_r( $pers_pos_ids );
                
        if ( count($pers_pos_ids) > 0 && $pers_pos_ids['record'] != '0' ) {
            $this->template->appendFileById( "view_position_person.html", "div", "position" );
            $pers_pos = $factory->createContainer( "person_position".'|'. $pers_pos_ids['record'] );
            $pers_pos->populate();
            $this->template->setForm( $pers_pos );
            $person = $factory->createContainer( $pers_pos_ids['parent'] );
            $person->populate();
            $this->template->setForm( $person );
        }
                
        $supervised = $this->position->getSupervised();
        //print_r( $supervised );
                
        if ( count($supervised) > 0 ) {
            $this->template->appendFileById( "view_position_supervised.html", "div", "position" );
            foreach( $supervised as $record ) {
                $this->template->appendFileById( "view_position_row.html", "tr", "supervised" );
                $this->template->setDisplayData( "position", I2CE_List::lookup( $record, "position" ) );
                $this->template->setDisplayData( "add_pos_id", array("id" => $record ));
            }
            $this->template->setAttribute( "class", "even", "supervised", "tr[position() mod 2 = 0]" );
        }

    }
        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
