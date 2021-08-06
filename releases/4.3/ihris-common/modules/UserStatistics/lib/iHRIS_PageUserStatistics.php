<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
* 
* This File is part of iHRIS Common 
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
*
* @package iHRIS
* @subpackage Common
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1
* @since v4.1
* @filesource
*/
/**
* Class iHRIS_PageUserStatistics
*
* @access public
*/


class iHRIS_PageUserStatistics extends I2CE_Page_CustomReports {

    /**
     * Perform any actions for the page.
     * @return boolean
     */
    public function action() {
        if ( count( $this->request_remainder ) > 0 ) {
            $cacheControl = array_shift( $this->request_remainder );
            $usModule = I2CE_ModuleFactory::instance()->getClass("UserStatistics");
            switch( $cacheControl ) {
                case "updateCacheForce" : 
                    if ( $usModule->cacheUserStatistics( false, true ) ) {
                        I2CE::raiseMessage("Updated user statistics.");
                    } else {
                        I2CE::raiseError("Failed to force cache user statistics.");
                    }
                    break;
                case "updateCache" : 
                    if ( $usModule->cacheUserStatistics() ) {
                        I2CE::raiseMessage("Updated user statistics.");
                    } else {
                        I2CE::raiseError("Failed to cache user statistics.");
                    }

            }
        }
        if ( !parent::action() ) {
            return false;
        }
        if ( ( array_key_exists( 'admin_only', $this->args ) 
                    ? $this->args['admin_only'] : true ) 
                && !$this->hasPermission('role(admin)') ) {
            return false;
        }
        $this->template->setAttribute( "class", "active", "menuConfigure", "a[@href='configure']" );
        $this->template->appendFileById( "menu_configure.html", "ul", "menuConfigure" );
        $this->template->setAttribute( "class", "active", "menuUserStatistics", "a[@href='UserStatistics']" );
        $displayObj = new iHRIS_CustomReport_Display_UserStatistics( $this, "UserStatistics" );

        $contentNode = $this->template->getElementById('siteContent');
        if ( !$contentNode instanceof DOMNode ) {
            I2CE::raiseError("Couldn't find siteContent node.");
            return false;
        }
        $this->template->addHeaderLink("customReports_display_Default.css");
        $this->template->setDisplayData( "limit_description", false );

        if ( $displayObj->display($contentNode ) ) {
            //$cacheControl = $this->template->appendFileById( "user_statistics_cache_control.html", "div", "report_results", 0, null, true );
            $limitNode = $this->template->getElementByName( "report_view_limit_description", 0 );
            $cacheNode = $this->template->createElement( "div", array( "id" => "user_statistics_cache_control" ) );
            $limitNode->parentNode->insertBefore( $cacheNode, $limitNode->nextSibling );
            $cacheControl = $this->template->appendFileById( "user_statistics_cache_control.html", "div", "user_statistics_cache_control" );
            $config = I2CE::getConfig()->modules->UserStatistics->cache;
            $config->volatile(true);
            $start = 0;
            $end = 0;
            $failed = 0;
            $config->setIfIsSet( $start, "start" );
            $config->setIfIsSet( $end, "end" );
            $config->setIfIsSet( $failed, "failed" );

            $st_date = I2CE_Date::now( I2CE_DATE::DATE_TIME, $start );
            //I2CE::raiseMessage("last started on " . $st_date->displayDate() );

            if ( $start == 0 ) {
                $this->launchBackgroundPage( "/UserStatistics/cache" );
            } else {
                if ( $end != 0 ) {
                    $end_date = I2CE_Date::now( I2CE_Date::DATE_TIME, $end );
                    $this->setDisplayDataImmediate( "last_cache_end", $end_date->displayDate(), $cacheControl );
                } else {
                    $this->setDisplayDataImmediate( "last_cache_end", "Never Cached", $cacheControl );
                }
                if ( $end >= $start ) {
                    $this->template->addFile( "user_statistics_cache_status_done.html", "p" );
                } elseif ( $failed >= $start ) {
                    $this->template->addFile( "user_statistics_cache_status_failed.html", "p" );
                } else {
                    $this->template->addFile( "user_statistics_cache_status_running.html", "p" );
                }
            }
            return true;
        } else {
            return false;
        }

    }

    /**
     * Perform the command line action for this page.
     * @param array $args
     * @param array $request_remainder
     * @return boolean
     */
    public function actionCommandLine( $args, $request_remainder ) {
        $usModule = I2CE_ModuleFactory::instance()->getClass("UserStatistics");

        $action = "update";
        if ( count($request_remainder) > 0 ) {
            $action = array_shift( $request_remainder );
        }

        switch( $action ) {
            case "recreate" : 
                if ( $usModule->cacheUserStatistics( true ) ) {
                    I2CE::raiseMessage("Re-Cached user statistics.");
                } else {
                    I2CE::raiseError("Failed to re-cache user statistics.");
                }
                break;
            case "recreateForce" : 
                if ( $usModule->cacheUserStatistics( true, true ) ) {
                    I2CE::raiseMessage("Re-Cached user statistics.");
                } else {
                    I2CE::raiseError("Failed to re-cache user statistics.");
                }
                break;
            case "force" :
                if ( $usModule->cacheUserStatistics( false, true ) ) {
                    I2CE::raiseMessage("Updated user statistics.");
                } else {
                    I2CE::raiseError("Failed to force cache user statistics.");
                }
                break;

            default :
                if ( $usModule->cacheUserStatistics() ) {
                    I2CE::raiseMessage("Updated user statistics.");
                } else {
                    I2CE::raiseError("Failed to cache user statistics.");
                }
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
