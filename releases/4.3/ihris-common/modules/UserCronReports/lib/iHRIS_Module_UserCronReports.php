<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @package iHRIS
* @subpackage Common
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1.6
* @since v4.1.6
* @filesource 
*/ 
/** 
* Class iHRIS_Module_UserCronReports
* 
* @access public
*/


class iHRIS_Module_UserCronReports extends I2CE_Module {

    /**
     * Return the list of fuzzy methods handled by this module.
     * @return array
     */
    public static function getMethods() {
        return array(
                'iHRIS_PageViewUser->action_cron_report' => 'action_cron_report',
                );
    }

    /**
     * Retrn the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'cronjob_five' => 'cronjob_five',
                'cronjob_ten' => 'cronjob_ten',
                'cronjob_fifteen' => 'cronjob_fifteen',
                'cronjob_thirty' => 'cronjob_thirty',
                'cronjob_hourly' => 'cronjob_hourly',
                'cronjob_daily' => 'cronjob_daily',
                'cronjob_weekly' => 'cronjob_weekly',
                'cronjob_monthly' => 'cronjob_monthly',
                );
    }

    /**
     * Run the five minute cron
     */
    public function cronjob_five() {
        $this->cronjob( 'five' );
    }

    /**
     * Run the ten minute cron
     */
    public function cronjob_ten() {
        $this->cronjob( 'ten' );
    }

    /**
     * Run the fifteen minute cron
     */
    public function cronjob_fifteen() {
        $this->cronjob( 'fifteen' );
    }

    /**
     * Run the thirty minute cron
     */
    public function cronjob_thirty() {
        $this->cronjob( 'thirty' );
    }

    /**
     * Run the hourly cron
     */
    public function cronjob_hourly() {
        $this->cronjob( 'hourly' );
    }

    /**
     * Run the daily cron
     */
    public function cronjob_daily() {
        $this->cronjob( 'daily' );
    }

    /**
     * Run the weekly cron
     */
    public function cronjob_weekly() {
        $this->cronjob( 'weekly' );
    }

    /**
     * Run the monthly cron
     */
    public function cronjob_monthly() {
        $this->cronjob( 'monthly' );
    }

    /**
     * Run the appropriate cron jobs based on the cron type.
     * @param string $type
     */
    public function cronjob( $type ) {

        // We need to run any normal hooks and not CLI hooks for this process if run from CLI
        $host_changed = false;
        if ( !array_key_exists( 'HTTP_HOST', $_SERVER ) ) {
            $host_changed = true;
            $_SERVER['HTTP_HOST'] = '';
        }
        // Find all the Module Access modules that are available so the user can be set.
        $report_limits = I2CE_ModuleFactory::callHooks( "get_report_module_limit_options" );
        $limit_modules = array();
        foreach( $report_limits as $limit ) {
            if ( array_key_exists( 'module', $limit ) ) {
                $mod = I2CE_ModuleFactory::instance()->getClass( $limit['module'] );
                $limit_modules[] = array( 'module' => $mod, 'user' => $mod->getUser() );
            }
        }

        $cron_type_where = array( 'operator' => "FIELD_LIMIT",
                'style' => 'equals',
                'field' => 'cron_type',
                'data' => array( 'value' => $type ),
                );
        $reports = I2CE_FormStorage::listFields( 'cron_report', array('parent', 'report_view'), false, $cron_type_where );
        foreach( $reports as $report ) {
            $view = $report['report_view'];
            $user = I2CE_FormFactory::instance()->createContainer( $report['parent'] );
            // Set the user on all access modules to limit results.
            foreach( $limit_modules as $module ) {
                $module['module']->setUser( $user->user );
            }
            $_SESSION['user_name'] = $user->username;

            $page = new I2CE_Page_ShowReport( array(), array( $view ) );
            $template = $page->getTemplate();
            $display = $page->getDesiredDisplays( $view );
            $use_display = 'Default';
            if ( $display[0] != 'PieChart' ) {
                $use_display = $display[0];
            }
            $displayObj = $page->instantiateDisplay( $use_display, $view );
            $config = I2CE::getConfig()->modules->CustomReports->reportViews->$view;

            $template->loadRootText( "<span id='siteContent' />" );
            $contentNode = $template->getElementById( 'siteContent' );

            $attachments = array();

            $report_name = $config->display_name;
            $report_desc = $config->description;
            $report_limit = '';
            $generated = strftime( '%c' );

            switch( $use_display ) {
                case "CrossTab" :
                    if ( $displayObj->isExport() ) {
                        $export = $displayObj->generateExport( $contentNode, false );
                        $html = false;
                        $attachments[] = array( 'type' => "text/csv; charset=UTF-8",
                                'data' => $export, 'name' => $this->getFileName($report_name).".csv" );
                        break;
                    }
                    // If not export then fall through to the Default
                case "Default" :
                    $displayObj->unsetPaging();
                    $displayObj->display( $contentNode );

                    $report_limit = $displayObj->getReportLimitsDescription();
        
                    $css = I2CE::getFileSearch()->search( 'CSS', 'customReports_display_Default.css' );
                    $report_css = file_get_contents( $css );
        
                    $report_table = $template->getElementById( 'report_table' );
                    $report_content = $template->doc->saveHTML( $report_table );

                    $html = <<<EOF
<?xml version="1.0" encoding="utf-8"?>'
<!DOCTYPE html>
<html>
<head>
    <title>Automated Report: $report_name</title>
    <style type="text/css">
$report_css
    </style>
</head>
<body>
    <h1>$report_name</h1>
    <h2>$report_desc</h2>
    <h3>$report_limit</h3>
    <p>Generated on: $generated</p>
    $report_content
</body>
</html>
EOF;
                    break;
                case "PDF" :
                    $pdf = $displayObj->getPDF( $contentNode );
                    $pdf_data = $pdf->Output( $report_name, 'S' );
                    $html = false;
                    $attachments[] = array( 'type' => 'application/pdf', 'data' => $pdf_data, 
                            'name' => $this->getFileName($report_name) . ".pdf" );
                    break;
                case "Export" :
                    $export = $displayObj->generateExport();
                    $html = false;
                    $attachments[] = array( 'type' => $displayObj->getContentType( true ),
                            'data' => $export, 'name' => $displayObj->getFileName() );
                    break;
                default :
                    I2CE::raiseError( "Unknown display type used for report display for user cron reports." );
                    break;
            }
            
            $email = $user->email;

            // This is duplicated from the Default setting so it can be in the main mail message as well.
            // It isn't called earlier to avoid duplicate processing with the Default display.
            if ( !$html && $report_limit == '' ) {
                $report_limit = $displayObj->getReportLimitsDescription();
            }
            $mail_msg = wordwrap("This is the automated report for $report_name:  $report_desc.\nLimits are: $report_limit\nGenerated on: $generated.");
            if ( I2CE_Mailer::mail( $email, array('Subject' => 'Automated Report: '.$report_name ), $mail_msg, $html, $attachments ) ) {
                echo "Report mail $report_name (" . $report['report_view'] . ") sent to $email.\n";
            } else {
                echo "Report mail $report_name (" . $report['report_view'] . ") failed to $email.\n";
            }
            
            //$page->actionCommandLine( array(), array() );
        }
        if ( $host_changed ) {
            unset( $_SERVER['HTTP_HOST'] );
        }
        unset( $_SESSION['user_name'] );
        foreach( $limit_modules as $module ) {
            $module['module']->setUser( $module['user'] );
        }
    }

    /**
     * Handle the display for the cron_report form on the view user page.
     * @return boolean
     */
    public function action_cron_report( $page ) {
        if ( !$page instanceof iHRIS_PageViewUser ) {
            return;
        }
        $template = $page->getTemplate();
        $template->appendFileById( "user_view_link_cron_report.html", "li", "user_edit_links" );

        $view_user = $page->getViewUser();
        $view_user->populateChildren("cron_report");
        if ( array_key_exists( 'cron_report', $view_user->children ) 
                && is_array( $view_user->children['cron_report'] ) ) {
            $node = $template->appendFileById( "user_view_cron_report_top.html", "div", "user_child_forms" );
            foreach( $view_user->children['cron_report'] as $child ) {
                $node = $template->appendFileById( "user_view_cron_report.html", "div", "cron_report" );
                if ( !$node instanceof DOMNode ) {
                    I2CE::raiseError( "Could not find template user_view_cron_report.html" );
                    return false;
                }
                $template->setForm( $child, $node );
            }
        }
        return true;
    }

    /**
     * Convert the report name to a valid filename.
     * @param string $report_name
     * @return string
     */
    protected function getFileName( $report_name ) {
        return addslashes(str_replace(array(' ',"\n","\t") , array('_',' ','_' ),
                    $report_name)) . '_' . date("d_m_Y");

    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
