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
 * View the history of entries for a particular field.
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the history of a particular field.
 * @package iHRIS
 * @subpackage Common
 * @access public
 */
class iHRIS_PageFieldHistory extends I2CE_Page {
        
    /**
     * Perform the main actions of the page.
     * @global array
     */
    protected function action() {
        $i2ce_config =I2CE::getConfig()->I2CE;
        parent::action();
                
        $id = $this->get('id');
        if ($this->get_exists('parent') && $this->get('parent')) {
            $parent = $this->get('parent');
        } else {
            $parent = '0';
        }
        $factory = I2CE_FormFactory::instance();
                
        if ( $this->get('type') == "person" ) {

            $person_id = ( $parent != '0' && $parent != '0|0' ? $parent : $id );
            $formObj = $factory->createContainer( $person_id );
            if ($formObj instanceof iHRIS_Person) {
                $this->template->setForm($formObj );
                $this->template->setDisplayDataImmediate( "return_link",  array('href'=>'view',
                            'id'=> $person_id ) );
                $this->template->appendFileById( "menu_view_link.html", "li", "navBarUL", true );
            }
        } else {
            $this->template->setDisplayData( "return_link", "" );
        }
        if ( $this->get_exists( 'template' ) ) {
            $table_template = "field_history_" . $this->get('template') . ".html";
            $row_template = "field_history_row_" . $this->get('template') . ".html";
        } else {
            $table_template = "field_history_table.html";
            $row_template = "field_history_row.html";
        }
        $this->template->addFile( $table_template );
                
        $fields = explode( ",", $this->get('field') );
                
        $obj = $factory->createContainer( $id );
        $obj->populateHistory( $fields );

        $all_dates = array();
        foreach( $fields as $field ) {
            while ( $obj->getField($field)->hasNextHistory() ) {
                $entry = $obj->getField($field)->nextHistory();
                $all_dates[ $entry->date->dbFormat() ][$field] = $entry;
            }
        }
        ksort( $all_dates );
        
        $previous = array();
        foreach( $all_dates as $date => $entries ) {
            $this->template->appendFileByName( $row_template, "tr", "history_table" );
            $first = true;
            foreach( $fields as $field ) {
                if ( array_key_exists( $field, $entries ) ) {
                    if ( $first ) {
                        $this->template->setDisplayData( "date_changed", $entries[$field]->date->displayDate() );
                        $first = false;
                    }
                    $this->template->setDisplayData( $field, $obj->getField($field)->getDisplayValue( $entries[$field] ) );
                    $previous[$field] = $entries[$field];
                } elseif ( array_key_exists( $field, $previous ) ) {
                    $this->template->setDisplayData( $field, $obj->getField($field)->getDisplayValue( $previous[$field] ) );
                } else {
                    $this->template->setDisplayData( $field, "" );
                }
            }
        }

    }
        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
