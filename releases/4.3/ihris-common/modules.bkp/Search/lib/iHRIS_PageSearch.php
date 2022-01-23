<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_Page_ReportRelationship
* @package iHRIS
* @subpackage Common
* @author Carl Leitner <litlfred@ibiblio.org>
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.0.3
* @access public
*/


class iHRIS_PageSearch extends I2CE_Page_ShowReport {



    /**
     *Determine the desired displays for the indicated report view
     * @param string $view
     *@returns array of string. 
     */
    public function getDesiredDisplays($view) {
        return array('Search');
    }


    /**
     *Determine all the allowed for the indicated report view
     * @param string $view
     *@returns array of string. 
     */
    public function getAllowedDisplays($view) {
        return array('Search');
    }


    /**
     * Set the active menu
     */
    protected function setActiveMenu() {
        $this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='CustomReports/view/reportViews']" );
        $this->template->setAttribute( "class", "active", "menuSearch", "a[@href='search']" );

        $report_config = I2CE::getConfig()->traverse( "/modules/CustomReports", true );

        foreach( $report_config->search_reports as $report => $report_info ) {
            $node = $this->template->appendFileById( "menu_search_report.html", "li", "menu_search_reports" );
            $this->template->setDisplayDataImmediate( "menu_search_link", array( "href" => "search/" . $report, "no_results" => 1 ), $node );
            $this->template->setDisplayDataImmediate( "menu_search_name", $report_info->name, $node );
            $this->template->setAttribute( "name", "menu_" . $report, null, "//a[@name='menu_search_link']", $node );
        }
        $this->template->setDisplayData( "limit_description", false );

        if (count($this->request_remainder) > 0) {
            reset($this->request_remainder);
            $view = current($this->request_remainder);
            $this->template->setAttribute( "class", "active", "menuSearch", "ul/li/a[@name='menu_" . $view . "']" );
        }

    }



    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_search.html", "ul", "menuSearch" );
        if ( I2CE_ModuleFactory::instance()->isEnabled( 'ihris-common-RecentForm' ) ) {
            $this->template->appendFileById( "menu_search_recent.html", "li", "menu_search_reports" );
        }

    }


    /**
     * Initializes any data for the page
     * @returns boolean.  True on sucess. False on failture
     */
    protected function initPage() {
        if (!$this->hasPermission('task(custom_reports_can_access)')) {
            return false;
        }
        if ($this->page !== 'search') {            
            return false;
        }
        return true;
    }


    
    /**
     * Perform any actions for the page
     * 
     * @returns boolean.  true on sucess
     */
    public function action() {
        $view = false;
        if (count($this->request_remainder) > 0) {
            $view = current($this->request_remainder);
        }
        $run_reports_in_bg = (!array_key_exists('run_reports_in_background',$this->args)) || ($this->args['run_reports_in_background']);
        if ($run_reports_in_bg) {
            $use_reports = array();
            if( $view) {
                $use_reports = array($view);
            } else {
                $report_config = I2CE::getConfig()->traverse( "/modules/CustomReports", true );
                $bkg_time = 0;
                $report_config->setIfIsSet( $bkg_time, "times/background" );
                foreach( $report_config->search_reports as $report => $report_info ) {
                    // if background generation of reports is turned off, then don't run this unless
                    // overridden by the search report setting
                    // When the background process runs each report will check it's own staleness
                    // before running.
                    $force = $report_info->traverse( "force" );
                    if ( ( !is_numeric( $bkg_time ) || $bkg_time <= 0 ) && !$force ) {
                        continue;
                    }
                    $use_reports[] = $report;
                }
            }
            $stale_reports = array();
            foreach ($use_reports as $report) {
                if (!I2CE_CustomReport::isStale($report)) {
                    continue;
                }
                $stale_reports[] = $report;
            }
            if (count($stale_reports) > 0) {
                $this->launchBackgroundPage( "/CustomReports/generate/" . implode( "/", $stale_reports ) );
            }
        }
        $this->template->setDisplayData( "limit_description", false );
        
        if ($view) {
            return $this->actionSearch($view);
        } else  {
            return $this->actionMenu();
        }
        //parent handles the show action
        return parent::action();
    }

    protected function actionMenu() {
        $this->template->addFile('search.html');
        if ( I2CE_ModuleFactory::instance()->isEnabled( 'ihris-common-RecentForm' ) ) {
            $this->template->appendFileById( "search_recent.html", "div", "search_reports" );
        }
        $report_config = I2CE::getConfig()->traverse( "/modules/CustomReports", true );
        foreach( $report_config->search_reports as $report => $report_info ) {
            $node = $this->template->appendFileById( "search_report.html", "div", "search_reports" );
            $this->template->setAttribute( "id", $report, "search", null, $node );
            $this->template->setAttribute( "id", $report . "_replace", "search_replace", null, $node );
            $this->template->setDisplayDataImmediate( "search_link", array( "href" => "search/" . $report, "no_results" => 1 ), $node );
            $this->template->setDisplayDataImmediate( "search_name", $report_info->name, $node );
            $this->template->setDisplayDataImmediate( "search_desc", $report_info->description, $node );
        }
        // Don't do Ajax on search pages for now, it's not working with tree select limits.
        return true;
        if (!I2CE_ModuleFactory::instance()->isEnabled('stub') || !$this->template->hasAjax()) {            
            return true;
        }
        //if we are doing an ajax.  make sure any possible required things are loaded
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('stubs.js');
        $this->template->addHeaderLink('stub.css');
        $this->template->addHeaderLink('Request.Content.js');
        $this->template->addHeaderLink('I2CE_ClassValues.js');
        $this->template->addHeaderLink('I2CE_Validator.js');
        $this->template->addHeaderLink('I2CE_Window.js');
        $this->template->addHeaderLink('I2CE_ToggableWindow.js');
        $this->template->addHeaderLink('I2CE_MultiForm.js');
        $this->template->addHeaderLink('FormWorm.css');
        $this->template->addHeaderLink("customReports_display.css");
        $this->template->addHeaderLink("customReports_display_Search.css");
        $this->template->addHeaderLink('I2CE_TreeSelect.js');
        $this->template->addHeaderLink('Tree.css');
        $this->template->addHeaderLink('Observer.js');
        $this->template->addHeaderLink('Autocompleter.js');
        $this->template->addHeaderLink('Autocompleter.css');
        $this->template->addHeaderLink('I2CE_TreeSelectAutoCompleter.js');
        $dp_args = array( "format" => "F j, Y", 
                "inputOutputFormat" => "Y-m-d",
                "allowEmpty" => true, "startView" => "decades" );
        $this->template->addDatePicker( "datepicker_ymd", $dp_args );
        $links = $this->template->query('//a[contains(@class,\'\')]');
        for ($i=0; $i < $links->length; $i++) {
            $link = $links->item($i);
            if (!$link instanceof DOMElement) {
                echo "Bad $i\n";
                continue;
            }
            if (!$link->hasAttribute('id') || !$link->hasAttribute('href')) {
                continue;
            }
            $id = $link->getAttribute('id');
            if (!is_string($id) || strlen($id) == 0) {
                continue;
            }
            $href = $link->getAttribute('href');
            if (!is_string($href) || strlen($href) == 0) {
                continue;
            }
            //echo "Adding update for $id\n";
            $this->addAjaxUpdate($id . '_replace',$id,'click',$href,'limit_form');
            $func = 
                ' var limit_form = $("limit_form"); ' .
                ' if (limit_form) {'.
                '  limit_form.set("id","limit_form_' . $id . '");' .
                '  new  I2CE_FormWorm("limit_form_' . $id . '",{"optionsMenuPositionVert":"mouse_above"}); '.
                ' }' .
                ' DP_datepicker_ymd.attach();';
            //echo "Adding ajax complete\n";
            $this->addAjaxCompleteFunction($id,$func);
        }
        return true;
    }



    protected function actionSearch($view) {
        $displayObj = $this->getDisplay($view);
        if (!$displayObj) {
            $this->setRedirect('search');
            return false;
        }
        $contentNode = $this->template->getElementById('siteContent');
        if (!$contentNode instanceof DOMNode) {
            $this->setRedirect('search');
            return false;
        }
        //we are good to go at this point.
        $this->template->addHeaderLink("CustomReports.css");
        $this->template->addHeaderLink("CustomReports_iehacks.css", array('ie6' => true ));
        $process_results = !$this->request_exists('no_results');
        return $displayObj->display($contentNode,  $process_results, 'Search');
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
