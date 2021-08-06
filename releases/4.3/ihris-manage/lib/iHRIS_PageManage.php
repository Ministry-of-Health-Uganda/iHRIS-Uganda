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
 * @author Luke Duncan <lduncan@intrahealth.org>
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
class iHRIS_PageManage extends I2CE_Page {
        
    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        $factory = I2CE_FormFactory::instance();
        $this->template->setAttribute( "class", "active", "menuManage", "a[@href='manage']" );
        $this->template->appendFileById( "menu_manage.html", "ul", "menuManage" );
        switch( $this->get('action') ) {
        case "review" :
            if ( I2CE_ModuleFactory::instance()->isEnabled( "ihris-manage-Application" ) ) {
                $this->template->setAttribute( "class", "active", "menuManage", "//li/a[@href='manage?action=review']" );
                $this->template->addFile( "applicant_review.html" );
                if ( $this->get_exists('position') ) {
                    $position_data = explode( '|', $this->get('position'), 2 );
                    $position_id = $position_data[1];
                    $this->template->setDisplayData( "return_link", array("action"=>"review" ));
                    $this->template->addFile( "applicant_review_results.html" );
                    $this->template->setDisplayData( "position_name", I2CE_List::lookup( $position_id, "position" ) );
                    $results = iHRIS_Applicant::findApplicants( $position_id );
                    if ( count($results) > 0 ) {
                        foreach( $results as $app_id => $app_data ) {
                            $this->template->appendFileById( "applicant_review_row.html", "li", "app_list" );
                            $this->template->setDisplayData( "app_id", array("id" => "person|" . $app_id ) );
                            $this->template->setDisplayData( "app_name", $app_data['surname'] . ', ' . $app_data['firstname'] );
                            //$last_mod = I2CE_Date::fromDB( $app_data['last_modified'] );
                            //$this->template->setDisplayData( "app_modified", $last_mod->displayDate() );
                            $this->template->setDisplayData( "make_offer", array("parent" => "person|" . $app_id , "position" => $this->get('position') ));
                        }
                    } else {
                        $this->template->appendFileById( "applicant_review_no_results.html", "li", "app_list" );
                    }
                } else {
                    $this->template->addFile( "applicant_review_list.html" );
                    $positions = I2CE_Form::listFields( "position", array( "code", "title" ),
                                array( 'operator' => 'FIELD_LIMIT',
                                    'field' => 'status',
                                    'style' => 'equals',
                                    'data' => array( 'value' => 'position_status|open' ) 
                                    )
                            );
                    $count = 0;
                    foreach( $positions as $id => $data ) {
                        $node = $this->template->appendFileById( "applicant_review_list_entry.html", "tr", "open_position_list" );
                        if ( ++$count % 2 == 0 ) {
                            $node->setAttribute( "class", "even" );
                        }
                        $this->template->setDisplayDataImmediate( "view_applicant", array( "action" => "review",
                                    "position" => "position|" . $id, ), $node );
                        $this->template->setDisplayDataImmediate( "position_name", $data['code'] . " - " . $data['title'], $node );
                    }
                }
            } else {
                parent::action();
            }
            break;
        default :
            parent::action();
            break;
        }
    }
        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
