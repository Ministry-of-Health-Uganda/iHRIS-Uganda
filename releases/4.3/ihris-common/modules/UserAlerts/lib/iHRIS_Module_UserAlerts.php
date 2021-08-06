<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* Class iHRIS_Module_UserAlerts
* 
* @access public
*/


class iHRIS_Module_UserAlerts extends I2CE_Module {

    /**
     * Return the list of fuzzy methods handled by this module.
     * @return array
     */
    public static function getMethods() {
        return array(
                'iHRIS_PageViewUser->action_user_alert' => 'action_user_alert',
                );
    }

    /**
     * Retrn the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "post_page_action" => "add_alerts",
                );
    }

    /**
     * Handle the display for the user_alert form on the view user page.
     * @return boolean
     */
    public function action_user_alert( $page ) {
        if ( !$page instanceof iHRIS_PageViewUser ) {
            return;
        }
        $template = $page->getTemplate();
        $template->appendFileById( "user_view_link_user_alert.html", "li", "user_edit_links" );

        $view_user = $page->getViewUser();
        $view_user->populateChild("user_alert", null, null, 'default', 5 );
        if ( array_key_exists( 'user_alert', $view_user->children ) 
                && is_array( $view_user->children['user_alert'] ) ) {
            $node = $template->appendFileById( "user_view_user_alert_top.html", "div", "user_child_forms" );
            foreach( $view_user->children['user_alert'] as $child ) {
                $node = $template->appendFileById( "user_view_user_alert.html", "div", "user_alert" );
                if ( !$node instanceof DOMNode ) {
                    I2CE::raiseError( "Could not find template user_view_user_alert.html" );
                    return false;
                }
                $template->setForm( $child, $node );
            }
        }
        return true;
    }

    /**
     * Add alerts to the page
     * @param I2CE_Page $page
     */
    public function add_alerts( $page ) {
        if ( !$page instanceof I2CE_Page ) {
            return;
        }

        $config = I2CE::getConfig()->traverse( "/modules/UserAlerts/display" );
        $user = I2CE_FormFactory::instance()->createContainer( "user|" . $page->getUser()->username );

        $alerts = $user->getChildIds( "user_alert" );
        $alert_count = count($alerts);
        if ( $alert_count == 0 ) {
            return;
        }
        $pend_where = array( 
                'operator' => 'FIELD_LIMIT',
                'field' => 'time_ack',
                'style' => 'null'
                );
        $pending = $user->getChildIds( "user_alert", array(), $pend_where );
        $pend_count = count($pending);

        $append_id = 'sysUser';
        $config->setIfIsSet( $append_id, "append_id" );
        $append_tag = 'li';
        $config->setIfIsSet( $append_tag, "append_tag" );
        $append_before = true;
        $config->setIfIsSet( $append_before, "append_before" );
        $pending_style = 'alerts_pending';
        $config->setIfIsSet( $pending_style, "pending_style" );
        $default_style = 'alerts_seen';
        $config->setIfIsSet( $default_style, "default_style" );
        $template_file = 'user_alert_link.html';
        $config->setIfIsSet( $template_file, "append_file" );
        if ( $pend_count > 0 ) {
            $style = $pending_style;
        } else {
            $style = $default_style;
        }

        $template = $page->getTemplate();
        $alert = $template->appendFileById( $template_file, $append_tag, $append_id, $append_before );
        $alert->setAttribute( "class", $style );
        $template->setDisplayDataImmediate( "alert_pending_count", $pend_count, $alert );
        $template->setDisplayDataImmediate( "alert_total_count", $alert_count, $alert );

    }

    /**
     * Add an alert to the given user.
     * @param string $username
     * @param string $alert_type
     * @param string $message
     * @return boolean
     */
    public function sendUserAlert( $username, $alert_type, $message, $link=null, $link_text=null ) {
        if ( !I2CE_User::userExists( $username, false ) ) {
            I2CE::raiseError( "Invalid user: $username passed to sendUserAlert");
            return false;
        }
        $ff = I2CE_FormFactory::instance();
        $user_alert = $ff->createContainer( "user_alert" );
        $user_alert->getField('alert_type')->setFromDB( $alert_type );
        $user_alert->message = $message;
        if ( $link && $link_text ) {
            $user_alert->link = $link;
            $user_alert->link_text = $link_text;
        }
        $user_alert->setParent( "user|" . $username );

        $save_user = new I2CE_User( '0', false, true, false );
        $user_alert->validate();
        if ( $user_alert->hasInvalid() ) {
            I2CE::raiseError("Invalid data passed to sendUserAlert");
            return false;
        }
        if ( $user_alert->save( $save_user ) ) {
            return true;
        }
        I2CE::raiseError("Failed to save new user alert");
        return false;
    }

    /**
     * Handler for alert triggers
     * @param string $username The username to be notified
     * @param string $trigger The trigger being called
     * @param string $message The message to send
     * @param string $link The optional link to include
     * @param string $link_text The link text for the link
     * @param array $args Any option arguments for this trigger handler
     * @return boolean
     */
    public function triggerAlert( $username, $trigger, $message, $link=false, $link_text='', $args=array() ) {
        $alert_type = 'notice';
        if ( array_key_exists( 'alert_type', $args ) ) {
            $alert_type = $args['alert_type'];
        }
        return $this->sendUserAlert( $username, $alert_type, $message, $link, $link_text );
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
