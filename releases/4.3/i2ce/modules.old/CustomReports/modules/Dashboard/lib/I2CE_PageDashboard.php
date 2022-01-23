<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
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
* @package I2CE
* @author Sovello Hildebrand <sovellohpmgani@gmail.com>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Dashboard
* 
* @access public
*/


class I2CE_PageDashboard extends I2CE_Page {
    
    /**
     * main actions for the page
     * 
     */
    public function action() {
      parent::action();
#$this->template->addHeaderLink('sort_dashboard.js');
#$this->template->addHeaderLink('display_dashboard.js');
      $this->template->addHeaderLink('swfobject.js');
      $this->template->addHeaderLink( 'https://www.google.com/jsapi', array( 'type' => "text/javascript", 'ext' => 'js' ), false );
      $this->template->addHeaderText("google.load('visualization', '1.0', {'packages':['corechart']});", 'script', 'vis');
      $this->template->addHeaderLink( 'QueryWrapper.js' );
      $this->template->addHeaderLink('dashboard.css');
      $message = "At least one report view must be defined for the dashboard.";
      $this->displayReportLinks();
    }
    
    
    /**
     * Create the links on the dashboard page that will used to load the reports through ajax.
     * @param string $options An array to set options especially to display the default report_view configurations
     * @return boolean
     */
     
    public function displayReportLinks( ) {
        $user = new I2CE_User();
        $loggedin_role = $user->getRole();
        //for each of the views we display it directly to the dashboard page
        //get reports for the current user
        //I2CE::raiseMessage('opts ' . print_r($this->args,true));
        
        if ( !array_key_exists( 'dashes', $this->args ) ) {
            $this->template->addFile( "dashboard_missing.html" );
            return;
        }
        if ( array_key_exists( 'default_settings', $this->args ) ) {
            $defaults = $this->args['default_settings'];
        }
        $opt_settings = array( 'height' => 250, 'width' => 350, 'title' => '', 'label_size' => 10 );
        foreach( $opt_settings as $key => $val ) {
            if ( !array_key_exists( $key, $defaults ) ) {
                $defaults[$key] = $val;
            }
        }

        $dash = null;
        if ( count( $this->request_remainder ) > 0 ) {
            $dash = array_shift( $this->request_remainder );
        } else {
            $dash = $loggedin_role;
        }
        if ( !array_key_exists( $dash, $this->args['dashes'] ) ) {
            $dash = 'default';
            if ( !array_key_exists( $dash, $this->args['dashes'] ) ) {
                I2CE::raiseError("No default dashboard configured.");
                $this->template->addFile('dashboard_missing.html');
            }
        }

        $dash_details = $this->args['dashes'][$dash];


        $permissions = array();
        if ( array_key_exists( 'tasks', $dash_details ) ) {
            $permissions[] = 'task('.implode(',',$dash_details['tasks']) .')';
        }
        if ( array_key_exists( 'roles', $dash_details ) ) {
            $permissions[] = 'role('.implode(',',$dash_details['roles']) .')';
        }
        if ( count($permissions) > 0 && !$this->hasPermission( implode('|', $permissions ) ) ) {
            $this->template->addFile( "dashboard_denied.html" );
            return;
        }

        if ( array_key_exists( 'settings', $dash_details ) ) {
            foreach( $opt_settings as $key => $val ) {
                if ( array_key_exists( $key, $dash_details['settings'] ) ) {
                    $defaults[$key] = $dash_details['settings'][$key];
                }
            }
        }
        
        $this->template->setDisplayDataImmediate( 'dashboard_title', $defaults['title'] );


        if ( !array_key_exists('order', $dash_details) || !is_array($dash_details['order']) 
            || count($dash_details['order']) == 0 ) {
            $this->template->addFile( "dashboard_misconfigured.html" );
            return;
        }
        $reportViews = $dash_details['order'];
        ksort($reportViews);

        $views = array();
        if ( array_key_exists( 'report_views', $dash_details ) ) {
            $views = $dash_details['report_views'];
        }

        $reportListNode = $this->template->getElementById("dashboard_report_list");

        foreach( $reportViews as $report_view ){
            $view_settings = $defaults;
            if ( array_key_exists( $report_view, $views ) ) {
                if ( array_key_exists( 'enabled', $views[$report_view] ) && !$views[$report_view]['enabled'] ) {
                    // Skip if not enabled.
                    continue;
                }
                foreach( $opt_settings as $key => $val ) {
                    if ( array_key_exists( $key, $views[$report_view] ) && $views[$report_view][$key] ) {
                        $view_settings[$key] = $views[$report_view][$key];
                    }
                }
            }
            $reportViewConfigs = I2CE::getConfig()->getAsArray("/modules/CustomReports/reportViews/$report_view");
            $div = $this->template->createElement('div', 
                    array( 'id' => "report_view_$report_view", 'class' => "dashboard_report" ) );
            $reportListNode->appendChild( $div );
            $page = new I2CE_Page_ShowReport( array(), array($report_view), array( 'no_controls' => 1, 'flash_height' => $view_settings['height'], 'flash_width' => $view_settings['width'], 'results_id' => $report_view, 'height' => $view_settings['height'], 'width' => $view_settings['width'], 'label_size' => $view_settings['label_size'] ) );
            $page->template = $this->template;
            $displayObj = $page->getDisplay( $report_view );
            //$displays = $page->getDesiredDisplays( $report_view );
            //$displayObj = $page->instantiateDisplay( $displays[0], $report_view );
            $displayObj->display($div);
            $header = $this->template->getElementByName( "report_view_display_name", 0, $div );
            if ( $header instanceof DOMElement ) {
                $link = $this->template->createElement( 'a', array( "href" => "CustomReports/show/$report_view" ), 
                        $header->nodeValue );
                $header->replaceChild( $link, $header->firstChild );
            }
            //$url = "CustomReports/show/$report_view?no_controls=1&results_id=$report_view&flash_height=" . $view_settings['height'] . "&flash_width=" . $view_settings['width'] . "&height=" . $view_settings['height'] . "&width=" . $view_settings['width'];
            //$this->addAjaxLoad( "report_view_$report_view",$url,'report','CustomReports_PieChart,visualization_wrapper' );
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
