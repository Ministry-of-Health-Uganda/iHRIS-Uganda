<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package i2ce
* @subpackage user
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.0
* @since v4.1.0
* @filesource 
*/ 
/** 
* Class I2CE_Module_User
* 
* @access public
*/


class I2CE_Module_User extends I2CE_Module{

    /**
     * Return the hooks defined by this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "post_page_action" => "javascriptTimeout",
                );
    }

    /**
     * Add javascript logout to the page if a timeout is set for the site.
     * @param I2CE_Page $page
     */
    public function javascriptTimeout( $page ) {

        if ( !$page->getUser()->logged_in() ) {
            return;
        }
        $no_js = 0;
        $config = I2CE::getConfig();
        $config->setIfIsSet( $no_js, "/config/site/user_timeout_no_javascript" );
        $config->setIfIsSet( $no_js, "/user_prefs/timeout/no_javascript" );
        if ( !$no_js ) {
            $timeout = 0;
            $config->setIfIsSet( $timeout, "/config/site/user_timeout" );
            $config->setIfIsSet( $timeout, "/user_prefs/timeout/user_timeout" );
    
            if ( $timeout && is_numeric( $timeout ) && $timeout > 0 ) {
                $timeout = $timeout * 1000;
                $message = "You have been logged out due to inactivity.";
                I2CE::getConfig()->setIfIsSet( $message, "/config/site/user_timeout_message" );
                I2CE::getConfig()->setIfIsSet( $message, "/user_prefs/timeout/user_timeout_message" );
                $logout = $page->getAccessedBaseURL() . "logout?autologout=1&message=" . urlencode($message);
                $js = <<<EOJS
var auto_logout_timeout_id = 0;
window.addEvent('domready', function() { 
        autoLogoutResetTimeout(); 
        document.addEvent('keypress', function(event) { autoLogoutResetTimeout(); } );
        document.addEvent('mousemove', function(event) { autoLogoutResetTimeout(); } );
        } );
function autoLogoutResetTimeout() {
    if ( auto_logout_timeout_id > 0 ) {
        clearTimeout( auto_logout_timeout_id );
    }
    auto_logout_timeout_id = setTimeout( function() { window.location = "$logout"; }, $timeout );
}
EOJS;
                $template = $page->getTemplate();
                $template->addHeaderLink( "mootools-core.js" );
                $template->addHeaderText( $js, "script", true );
            }
        }
    }

    /**
     * Checks to see if we are doing an auto-login
     */
    public static function doAutoLogin() {
        $userAccess= I2CE::getUserAccess();
        if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
            return false;
        }            
        return $userAccess->doAutoLogin();        
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
