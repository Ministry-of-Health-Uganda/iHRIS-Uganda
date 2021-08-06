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
* Class iHRIS_Module_UserTriggers
* 
* @access public
*/


class iHRIS_Module_UserTriggers extends I2CE_Module {

    /**
     * @var I2CE_MagicDataNode The user trigger configuration top level.
     */
    var $config;

    /**
     * @var I2CE_ModuleFactory The module factory
     */
    var $module_factory;

    /**
     * Construct this object
     */
    public function __construct() {
        $this->config = I2CE::getConfig()->modules->UserTriggers;
        $this->module_factory = I2CE_ModuleFactory::instance();
    }

    /**
     * Return the list of fuzzy methods handled by this module.
     * @return array
     */
    public static function getMethods() {
        return array(
                'iHRIS_PageViewUser->action_user_trigger' => 'action_user_trigger',
                );
    }

    /**
     * Handle the display for the user_trigger form on the view user page.
     * @return boolean
     */
    public function action_user_trigger( $page ) {
        if ( !$page instanceof iHRIS_PageViewUser ) {
            return;
        }
        $template = $page->getTemplate();
        $template->appendFileById( "user_view_link_user_trigger.html", "li", "user_edit_links" );

        $view_user = $page->getViewUser();
        $view_user->populateChildren("user_trigger");
        if ( array_key_exists( 'user_trigger', $view_user->children ) 
                && is_array( $view_user->children['user_trigger'] ) ) {
            $node = $template->appendFileById( "user_view_user_trigger_top.html", "div", "user_child_forms" );
            foreach( $view_user->children['user_trigger'] as $child ) {
                $node = $template->appendFileById( "user_view_user_trigger.html", "div", "user_trigger" );
                if ( !$node instanceof DOMNode ) {
                    I2CE::raiseError( "Could not find template user_view_user_trigger.html" );
                    return false;
                }
                $template->setForm( $child, $node );
            }
        }
        return true;
    }

    /**
     * Return a list of the triggers available.
     * For use with an ENUM field type
     * @return array
     */
    public function listTriggers() {
        $triggers = array();
        foreach( $this->config->triggers as $key => $data ) {
            if ( $data instanceof I2CE_MagicDataNode ) {
                $triggers[$key] = $data->display;
            }
        }
        return $triggers;
    }

    /**
     * Trigger an event and call any associated handlers for all users linked to the trigger.
     * @param string $trigger The trigger being called
     * @param string $msg_key A key to look up in /modules/UserTriggers/messages to get the message to display.
     * @param string $message The message to include with the $msg_key if it exists.
     * @param string $link The link to send to the handlers (or use the default if set to true)
     * @param string $link_add Any data to include to the end of the link
     * @param array $args Any custom args to override for each handler that may be used, otherwise the default from MD will
     *                    be used.
     * @return boolean
     */
    public function trigger( $trigger, $msg_key='', $message='', $link=false, $link_add='', $args=array() ) {

        if ( !$this->config->__isset( "triggers/$trigger" ) ) {
            I2CE::raiseError( "No trigger found for $trigger!" );
            return false;
        }

        $trigger_opts = $this->config->triggers->$trigger->getAsArray();
        $handlers = $this->config->handlers->getAsArray();

        $call_handlers = array();
        if ( array_key_exists( 'handler', $trigger_opts ) && is_array( $trigger_opts['handler'] ) ) {
            foreach( $trigger_opts['handler'] as $handler => $bool ) {
                if ( array_key_exists( $handler, $handlers ) && $bool ) {
                    $call_handlers[] = $handler;
                    if ( !array_key_exists( 'args', $trigger_opts ) ) {
                        $trigger_opts['args'] = array();
                    }
                    if ( !array_key_exists( $handler, $trigger_opts['args'] ) ) {
                        $trigger_opts['args'][$handler] = array();
                    }
                    if ( array_key_exists( $handler, $args ) ) {
                        foreach( $args[$handler] as $key => $val ) {
                            // Override the default args with whatever is sent to the method call.
                            $trigger_opts['args'][$handler][$key] = $val;
                        }
                    }
                }
            }
        }
        if ( count( $call_handlers ) == 0 ) {
            I2CE::raiseError( "No handlers enabled for $trigger so nothing to do!" );
            return false;
        }

        $msg_defaults = array ( 'prefix' => '', 'suffix' => '', 'link_text' => '', 'link' => '' );
        if ( array_key_exists( 'message', $trigger_opts ) ) {
            foreach( $msg_defaults as $key => &$val ) {
                if ( array_key_exists( $key, $trigger_opts['message'] ) ) {
                    $val = $trigger_opts['message'][$key];
                }
            }
        }

        $msg_lookup = '';
        if ( $msg_key ) {
            $this->config->setIfIsSet( $msg_lookup, "messages/$msg_key" );
        }
        if ( $link ) {
            if ( is_string( $link ) && strlen($link) > 2 ) { 
                $msg_defaults['link'] = $link;
            }
        }
        if ( $link_add ) {
            $msg_defaults['link'] .= $link_add;
        }
        if ( strlen( $msg_defaults['link'] ) > 2 && strtolower( substr( $msg_defaults['link'], 0, 4 ) ) != 'http' ) {
            $site_url = I2CE::getAccessedBaseURL();
            if ( $msg_defaults['link'][0] == '/') {
                $site_url = substr( $site_url, 0, strpos( $site_url, '/', 9 ) );
            }
            $msg_defaults['link'] = $site_url . $msg_defaults['link'];

        }

        $full_message = $msg_defaults['prefix'] . " " . $msg_lookup . " " . $message . " " . $msg_defaults['suffix'];

        $trigger_where = array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'equals',
                'field' => 'trigger',
                'data' => array( 'value' => $trigger )
                );
        $triggers = I2CE_FormStorage::listFields( 'user_trigger', array( 'parent' ), false, $trigger_where );
        $ret_val = true;

        foreach( $triggers as $data ) {
            $user_form = I2CE_FormFactory::instance()->createContainer( $data['parent'] );
        
            foreach( $call_handlers as $handler ) {
                foreach( $handlers[$handler] as $module => $method ) {
                    $modObj = $this->module_factory->getClass($module);
                    if ( $modObj instanceof I2CE_Module ) {
                        if ( $modObj->_hasMethod( $method ) ) {
                            if ( !$modObj->$method( $user_form->username, $trigger, $full_message, 
                                        $msg_defaults['link'], $msg_defaults['link_text'], $trigger_opts['args'][$handler] ) ) {
                                I2CE::raiseError( "Error trying to call $method on $module");
                                $ret_val &= false;
                            }
                        } else {
                            I2CE::raiseError("Can't find method $method on $module");
                            $ret_val &= false;
                        }
                    } else {
                        I2CE::raiseError( "$module isn't an instance of I2CE_Module" );
                        $ret_val &= false;
                    }
                }
            }
        }

        return $ret_val;

    }

    /**
     * Handler for mail triggers
     * @param string $username The username to be notified
     * @param string $trigger The trigger being called
     * @param string $message The message to send
     * @param string $link The optional link to include
     * @param string $link_text The link text for the link
     * @param array $args Any option arguments for this trigger handler
     * @return boolean
     */
    public function triggerEmail( $username, $trigger, $message, $link=false, $link_text='', $args=array() ) {
        $user = I2CE_FormFactory::instance()->createContainer( "user|" . $username );
        $user->populate();

        $email = $user->email;
        if ( !I2CE_Validate::checkEmail($email) ) {
            I2CE::raiseError( "Invalid email to $email for $username while sending trigger: $trigger" );
            return false;
        }
        
        $display = $trigger;
        $this->config->setIfIsSet( $display, "triggers/$trigger/display" );

        $subject = '';
        if ( array_key_exists( 'subject', $args ) ) {
            $subject = $args['subject'];
        } else {
            $this->config->setIfIsSet( $subject, "messages/default_email_subject" );
            if ( $subject = '' ) {
                $subject = 'Automated email for ' . $display;
            } else {
                $subject .= " $display";
            }
        }

        $full_message = $message;
        if ( $link ) {
            $full_message .= "\n\n";
            if ( $link_text ) {
                $full_message .= "$link_text: ";
            }
            $full_message .= "$link\n";
        }

        $full_message = wordwrap( $full_message );

        if ( !I2CE_Mailer::mail( $email, array( 'Subject' => $subject ), $full_message ) ) {
            I2CE::raiseError("Unable to send message to $email for trigger.");
            return false;
        }
        return true;
    }

    /**
     * Hander for hook triggers
     * @param string $username The username to be notified
     * @param string $trigger The trigger being called
     * @param string $message The message to send
     * @param string $link The optional link to include
     * @param string $link_text The link text for the link
     * @param array $args Any option arguments for this trigger handler
     * @return boolean
     */
    public function triggerHook( $username, $trigger, $message, $link=false, $link_text='', $args=array() ) {
        if ( !array_key_exists( 'hooks', $args ) ) {
            I2CE::raiseError( "No hooks defined for $trigger.  It must be defined in /modules/UserTriggers/triggers/$trigger/args/hok/hooks" );
            return false;
        }
        $hooks = $args['hooks'];
        if ( !is_array( $hooks ) ) {
            $hooks = array( $hooks );
        }

        foreach ( $hooks as $hook ) {
            I2CE_ModuleFactory::callHooks( $hook, $username, $trigger, $message, $link, $link_text, $args );
        }
    }
 
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
