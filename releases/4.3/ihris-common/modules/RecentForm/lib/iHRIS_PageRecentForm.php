<?php
/**
 * @copyright © 2008, 2009, 2010 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
* iHRIS_PageRecentForm
* @package iHRIS
* @subpackage Common
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.0.3
* @access public
*/


class iHRIS_PageRecentForm extends I2CE_Page {


    /**
     * Perform any actions for the page
     * 
     * @return boolean.  true on success
     */
    public function action() {
        parent::action();
        $this->template->setAttribute( "class", "active", "menuSearch", "a[@href='search']" );
        $this->template->appendFileById( "menu_recent.html", "ul", "menuSearch" );
        $this->template->setAttribute( "class", "active", "menuSearch", "ul/li/a[@name='menu_recent']" );

        if ( I2CE_ModuleFactory::instance()->isEnabled( 'ihris-common-Search' ) ) {
            $report_config = I2CE::getConfig()->traverse( "/modules/CustomReports", true );
            foreach( $report_config->search_reports as $report => $report_info ) {
                $node = $this->template->appendFileById( "menu_search_report.html", "li", "menu_search_reports" );
                $this->template->setDisplayDataImmediate( "menu_search_link", array( "href" => "search/" . $report, "no_results" => 1 ), $node );
                $this->template->setDisplayDataImmediate( "menu_search_name", $report_info->name, $node );
            }
        }

        if (count($this->request_remainder) > 0) {
            return $this->actionRecent();
        } else {
            return $this->actionMenu();
        }
    }

    /**
     * Display the main menu for this page.
     * @return boolean
     */
    protected function actionMenu() {
        $this->template->appendFileById( "recent_desc.html", "div", "recent_forms" );
        $recent_config = I2CE::getConfig()->traverse( "/modules/RecentForm" );
        $form_config = I2CE::getConfig()->traverse( "/modules/forms/forms" );
        foreach( $recent_config->forms as $form => $details ) {
            if ( !$form_config->is_parent( $form ) ) continue;
            $node = $this->template->appendFileById( "recent_form.html", "div", "recent_forms" );
            $this->template->setDisplayDataImmediate( "recent_link", array( "href" => "recent/" . $form ), $node );
            $form_name = $form;
            $form_config->setIfIsSet( $form_name , "$form/display" );
            $form_description = "";
            $form_config->setIfIsSet( $form_description, "$form/meta/description" );
            $this->template->setDisplayDataImmediate( "recent_name", $form_name , $node );
            $this->template->setDisplayDataImmediate( "recent_description", $form_description, $node );
        }
        return true;
    }
    
    /**
     * Display the recent changes list for the given form.
     * @return boolean
     */
    protected function actionRecent() {
        $form = array_shift( $this->request_remainder );
        $form_config = I2CE::getConfig()->traverse( "/modules/forms/forms" );
        if ( !$form_config->is_parent( $form )
          || !I2CE::getConfig()->is_parent( "/modules/RecentForm/forms/$form" ) ) {
            return $this->actionMenu();
        }
        $page_size = 25;
        $days = "today";
        $user = false;

        if ( count( $this->request_remainder ) > 0 ) {
            $days = array_shift( $this->request_remainder );
        }
        $user_list = false;
        if ( count( $this->request_remainder ) > 0 ) {
            $user_list = array_shift( $this->request_remainder );
            $user = explode( ',', $user_list );
            foreach( $user as $key => $uid ) {
                if ( $uid == "me" ) {
                    $uobj = new I2CE_User();
                    $user[$key] = $uobj->getId();
                }
            }
            $user = array_filter( $user, "is_numeric" );
            if ( count($user) == 0 ) {
                $user = false;
            } elseif ( count($user) == 1 ) {
                $user = array_pop($user);
            } 
        }
        switch( $days ) {
            case "yesterday" :
                $mod_time = mktime( 0, 0, 0, date("n"), date("j")-1 );
                break;
            case "week" :
                $mod_time = mktime( 0, 0, 0, date("n"), date("j")-7 );
                break;
            default :
                $mod_time = mktime( 0, 0, 0 );
                break;
        }
        $form_name = $form;
        $form_config->setIfIsSet( $form_name, "$form/display" );
        $user_link = "";
        if ( $user_list ) {
            $user_link = "/" . $user_list;
        }
        $this->template->setDisplayDataImmediate( "display_form_name", ": " . $form_name );
        $header = $this->template->appendFileById( "recent_display.html", "div", "recent_forms" );
        $this->template->setDisplayDataImmediate( "recent_name", $form_name, $header );
        $this->template->setDisplayDataImmediate( "recent_date", date( "d M Y", $mod_time) , $header );
        $this->template->setDisplayDataImmediate( "recent_today_link", array( "href" => "recent/$form/today".$user_link ), $header );
        $this->template->setDisplayDataImmediate( "recent_yesterday_link", array( "href" => "recent/$form/yesterday".$user_link ), $header );
        $this->template->setDisplayDataImmediate( "recent_week_link", array( "href" => "recent/$form/week".$user_link ), $header );
        $this->template->setDisplayDataImmediate( "recent_me_link", array( "href" => "recent/$form/$days/me" ), $header );
        $this->template->setDisplayDataImmediate( "recent_all_link", array( "href" => "recent/$form/$days" ), $header );

        $recent_form_config = I2CE::getConfig()->traverse( "/modules/RecentForm/forms/$form", true );
        $fields = $recent_form_config->fields->getAsArray();
        ksort($fields);
        if ( !is_array( $fields ) ) $fields = array();
        $display = implode( " ", array_fill( 0, count($fields), "%s" ) );
        $recent_form_config->setIfIsSet( $display, "display" );
        $link = "recent";
        $recent_form_config->setIfIsSet( $link, "link" );

        $parent = false;
        $recent_form_config->setIfIsSet( $parent, "parent" );
        if ( $parent ) $parent = true;

        $order = $fields;
        array_unshift( $order, "-last_modified" );
        if ( $this->request_exists( "page" ) ) {
            $limit_start = ( (int)$this->request("page") - 1 ) * $page_size;
        } else {
            $limit_start = 0;
        }
        $results = I2CE_FormStorage::listDisplayFields( $form, $fields, $parent, 
                array(), $order, array( $limit_start, $page_size ) , $mod_time, false, $user );
        $num_found = I2CE_FormStorage::getLastListCount( $form );
        $this->template->setDisplayDataImmediate( "recent_found", $num_found, $header );
        foreach( $results as $id => $data ) {
            $record = $this->template->appendFileById( "recent_display_form.html", "li", "recent_list" );
            if ( $parent ) {
                $this->template->setDisplayDataImmediate( "form_link", 
                        array( "href" => $link . $data['parent'] ), $record );
            } else {
                $this->template->setDisplayDataImmediate( "form_link", 
                        array( "href" => $link . $form ."|" . $id ), $record );
            }
            $extra_display = I2CE_ModuleFactory::callHooks("recent_form_${form}_display", $data);
            array_unshift( $extra_display, vsprintf($display, $data) );
            $this->template->setDisplayDataImmediate( "record_display", implode(' ', $extra_display), $record );
        }

        if ( $this->module == "I2CE" ) {
            $url = $this->page . "/" . $form . "/" . $days;
        } else {
            $url = $this->module . "/" . $this->page . "/" . $form . "/" . $days;
        }
        $total_pages = max( 1, ceil( $num_found / $page_size ) );
        if ( $total_pages > 1 ) {
            $page_num = (int) $this->request('page');
            $page_num = min( max ( 1, $page_num ), $total_pages );
            $this->makeJumper( "recent", $page_num, $total_pages, $url, array() );
        }

    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
